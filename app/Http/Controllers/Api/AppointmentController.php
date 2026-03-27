<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            $appointments = Appointment::with(['patient', 'dentist', 'service'])->get();
        } elseif ($user->hasRole('dentist')) {
            $appointments = Appointment::with(['patient', 'service'])
                ->where('dentist_id', $user->id)
                ->get();
        } else {
            $appointments = Appointment::with(['dentist', 'service'])
                ->where('patient_id', $user->id)
                ->get();
        }

        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dentist_id'       => 'required|exists:users,id',
            'service_id'       => 'nullable|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'notes'            => 'nullable|string',
        ]);

        $data['patient_id'] = $request->user()->id;
        $data['status'] = 'pending';

        $appointment = Appointment::create($data);

        return response()->json($appointment->load(['patient', 'dentist', 'service']), 201);
    }

    public function show(Request $request, Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        return response()->json($appointment->load(['patient', 'dentist', 'service', 'treatmentNote']));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $data = $request->validate([
            'appointment_date' => 'sometimes|date',
            'start_time'       => 'sometimes|date_format:H:i',
            'end_time'         => 'sometimes|date_format:H:i',
            'status'           => 'sometimes|in:pending,confirmed,cancelled,completed,no_show',
            'notes'            => 'nullable|string',
        ]);

        $appointment->update($data);

        return response()->json($appointment->load(['patient', 'dentist', 'service']));
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $this->authorize('delete', $appointment);
        $appointment->delete();

        return response()->json(null, 204);
    }
}
