<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TreatmentNote;
use Illuminate\Http\Request;

class TreatmentNoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            $notes = TreatmentNote::with(['dentist', 'patient', 'appointment'])->get();
        } elseif ($user->hasRole('dentist')) {
            $notes = TreatmentNote::with(['patient', 'appointment'])
                ->where('dentist_id', $user->id)
                ->get();
        } else {
            $notes = TreatmentNote::with(['dentist', 'appointment'])
                ->where('patient_id', $user->id)
                ->get();
        }

        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $this->authorize('create', TreatmentNote::class);

        $data = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'patient_id'     => 'required|exists:users,id',
            'diagnosis'      => 'nullable|string',
            'treatment'      => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $data['dentist_id'] = $request->user()->id;

        $note = TreatmentNote::create($data);

        return response()->json($note->load(['dentist', 'patient', 'appointment']), 201);
    }

    public function show(Request $request, TreatmentNote $treatmentNote)
    {
        $this->authorize('view', $treatmentNote);

        return response()->json($treatmentNote->load(['dentist', 'patient', 'appointment']));
    }

    public function update(Request $request, TreatmentNote $treatmentNote)
    {
        $this->authorize('update', $treatmentNote);

        $data = $request->validate([
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $treatmentNote->update($data);

        return response()->json($treatmentNote->load(['dentist', 'patient', 'appointment']));
    }

    public function destroy(Request $request, TreatmentNote $treatmentNote)
    {
        $this->authorize('delete', $treatmentNote);
        $treatmentNote->delete();

        return response()->json(null, 204);
    }
}
