<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $users = User::with('roles')->orderBy('created_at')->get();

        return response()->json($users);
    }

    public function updateRole(Request $request, User $user)
    {
        if (! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'role' => 'required|in:patient,dentist,admin',
        ]);

        // Replace the first role (each user has one primary role)
        $user->roles()->delete();
        UserRole::create(['user_id' => $user->id, 'role' => $data['role']]);

        return response()->json($user->load('roles'));
    }

    public function destroy(Request $request, User $user)
    {
        if (! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
