<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::active()->get());
    }

    public function show(Service $service)
    {
        return response()->json($service);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Service::class);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'price_chf'        => 'nullable|numeric|min:0',
            'category'         => 'nullable|string|max:100',
            'is_active'        => 'boolean',
        ]);

        return response()->json(Service::create($data), 201);
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $data = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'duration_minutes' => 'sometimes|integer|min:1',
            'price_chf'        => 'nullable|numeric|min:0',
            'category'         => 'nullable|string|max:100',
            'is_active'        => 'boolean',
        ]);

        $service->update($data);

        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);
        $service->delete();

        return response()->json(null, 204);
    }
}
