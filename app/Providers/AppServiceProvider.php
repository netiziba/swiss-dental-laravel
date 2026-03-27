<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Document;
use App\Models\Message;
use App\Models\Service;
use App\Models\TreatmentNote;
use App\Policies\AppointmentPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\MessagePolicy;
use App\Policies\ServicePolicy;
use App\Policies\TreatmentNotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Appointment::class   => AppointmentPolicy::class,
        Document::class      => DocumentPolicy::class,
        Message::class       => MessagePolicy::class,
        Service::class       => ServicePolicy::class,
        TreatmentNote::class => TreatmentNotePolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
