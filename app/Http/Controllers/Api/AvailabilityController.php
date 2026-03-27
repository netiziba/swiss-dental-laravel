<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DentistAvailability;
use App\Models\User;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $dentistId = $request->query('dentist_id');

        $query = DentistAvailability::query();

        if ($dentistId) {
            $query->where('dentist_id', $dentistId);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->hasRole('dentist') && ! $user->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'day_of_week'  => 'required|integer|between:0,6',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
        ]);

        $data['dentist_id'] = $user->id;

        $availability = DentistAvailability::create($data);

        return response()->json($availability, 201);
    }

    public function update(Request $request, DentistAvailability $availability)
    {
        $user = $request->user();

        if ($user->id !== $availability->dentist_id && ! $user->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'start_time'   => 'sometimes|date_format:H:i',
            'end_time'     => 'sometimes|date_format:H:i',
            'is_available' => 'boolean',
        ]);

        $availability->update($data);

        return response()->json($availability);
    }

    public function destroy(Request $request, DentistAvailability $availability)
    {
        $user = $request->user();

        if ($user->id !== $availability->dentist_id && ! $user->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $availability->delete();

        return response()->json(null, 204);
    }

    public function dentists()
    {
        $dentists = User::whereHas('roles', fn ($q) => $q->where('role', 'dentist'))
            ->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json($dentists);
    }
}
