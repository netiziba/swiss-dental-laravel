<?php

namespace App\Policies;

use App\Models\TreatmentNote;
use App\Models\User;

class TreatmentNotePolicy
{
    public function view(User $user, TreatmentNote $note): bool
    {
        return $user->id === $note->dentist_id
            || $user->id === $note->patient_id
            || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('dentist') || $user->hasRole('admin');
    }

    public function update(User $user, TreatmentNote $note): bool
    {
        return $user->id === $note->dentist_id || $user->hasRole('admin');
    }

    public function delete(User $user, TreatmentNote $note): bool
    {
        return $user->id === $note->dentist_id || $user->hasRole('admin');
    }
}
