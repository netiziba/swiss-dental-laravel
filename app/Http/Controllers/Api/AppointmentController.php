<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AppointmentController extends Controller
{
    /** These statuses do not reserve a slot (patient cancelled or practice declined). */
    private const STATUS_DOES_NOT_BLOCK_SLOT = ['cancelled', 'declined'];

    /**
     * Slot start times in minutes from midnight (1 h each), aligned with Terminbuchung.tsx.
     * ISO weekday N: Mon=1 … Sat=6, Sun=7.
     */
    private static function slotStartsMinutesForYmd(string $ymd): array
    {
        $n = (int) Carbon::parse($ymd)->format('N');
        if ($n === 7) {
            return [];
        }
        if ($n === 6) {
            return [540, 600, 660, 720];
        }

        return [480, 540, 600, 660, 780, 840, 900, 960];
    }

    /** @return list<string> H:i start labels */
    private static function slotStartStringsForYmd(string $ymd): array
    {
        return array_map(
            fn (int $m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60),
            self::slotStartsMinutesForYmd($ymd)
        );
    }

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

    /**
     * Start times (H:i) for hourly slots that overlap an existing appointment — all patients.
     * Uses interval overlap so e.g. 08:00–09:00 blocks the 08:00 button, 09:00–10:00 blocks 09:00.
     */
    public function bookedSlots(Request $request)
    {
        $data = $request->validate([
            'date'       => 'required|date_format:Y-m-d',
            'dentist_id' => 'required|exists:users,id',
        ]);

        $appointments = Appointment::query()
            ->where('dentist_id', $data['dentist_id'])
            ->whereDate('appointment_date', $data['date'])
            ->whereNotIn('status', self::STATUS_DOES_NOT_BLOCK_SLOT)
            ->get(['start_time', 'end_time']);

        return response()->json(['slots' => self::blockedSlotTimesForDay($appointments, $data['date'])]);
    }

    /**
     * Dates in [from, to] where every hourly slot is taken (calendar: disable whole day).
     */
    public function fullDates(Request $request)
    {
        $data = $request->validate([
            'dentist_id' => 'required|exists:users,id',
            'from'       => 'required|date_format:Y-m-d',
            'to'         => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->startOfDay();
        if ($from->diffInDays($to) > 120) {
            return response()->json(['message' => 'Zeitraum zu groß (max. 120 Tage).'], 422);
        }

        $appointments = Appointment::query()
            ->where('dentist_id', $data['dentist_id'])
            ->whereBetween('appointment_date', [$data['from'], $data['to']])
            ->whereNotIn('status', self::STATUS_DOES_NOT_BLOCK_SLOT)
            ->get(['appointment_date', 'start_time', 'end_time']);

        $byDate = $appointments->groupBy(fn ($a) => $a->appointment_date->format('Y-m-d'));

        $fullDates = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $dStr = $cursor->format('Y-m-d');
            $slots = self::slotStartsMinutesForYmd($dStr);
            if ($slots === []) {
                $cursor->addDay();

                continue;
            }
            $dayAppts = $byDate->get($dStr, collect());
            $blocked = self::blockedSlotTimesForDay($dayAppts, $dStr);
            if (count($blocked) >= count($slots)) {
                $fullDates[] = $dStr;
            }
            $cursor->addDay();
        }

        return response()->json(['full_dates' => $fullDates]);
    }

    private static function blockedSlotTimesForDay(Collection $appointments, string $ymd): array
    {
        $blocked = [];
        foreach (self::slotStartsMinutesForYmd($ymd) as $slotStartMin) {
            $slotEndMin = $slotStartMin + 60;
            foreach ($appointments as $appt) {
                $s = self::timeStringToMinutes($appt->start_time);
                $e = self::timeStringToMinutes($appt->end_time);
                if ($s < $slotEndMin && $e > $slotStartMin) {
                    $blocked[] = sprintf('%02d:%02d', intdiv($slotStartMin, 60), $slotStartMin % 60);
                    break;
                }
            }
        }

        return array_values(array_unique($blocked));
    }

    /**
     * Wall-clock minutes from midnight. Handles TIME strings, DateTime/PDO, and
     * datetime strings (substr(0,5) must not be used on "YYYY-MM-DD HH:MM:SS").
     */
    private static function timeStringToMinutes(mixed $t): int
    {
        if ($t instanceof \DateTimeInterface) {
            return (int) $t->format('H') * 60 + (int) $t->format('i');
        }
        $s = trim((string) $t);
        if ($s === '') {
            return 0;
        }
        if (preg_match_all('/(\d{1,2}):(\d{2})(?::\d{2})?/', $s, $m, PREG_SET_ORDER)) {
            $last = end($m);

            return (int) $last[1] * 60 + (int) $last[2];
        }

        return 0;
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

        $dateStr = Carbon::parse($data['appointment_date'])->format('Y-m-d');
        $allowedStarts = self::slotStartStringsForYmd($dateStr);
        if ($allowedStarts === []) {
            return response()->json(['message' => 'An diesem Tag sind keine Online-Termine möglich.'], 422);
        }
        $startNorm = strlen($data['start_time']) >= 5 ? substr($data['start_time'], 0, 5) : $data['start_time'];
        if (! in_array($startNorm, $allowedStarts, true)) {
            return response()->json(['message' => 'Ungültige Uhrzeit für diesen Wochentag.'], 422);
        }

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
            'status'           => 'sometimes|in:pending,confirmed,cancelled,completed,no_show,declined',
            'notes'            => 'nullable|string',
            'decline_reason'   => 'nullable|string|max:5000',
        ]);

        if (isset($data['status']) && $data['status'] === 'declined') {
            $reason = trim((string) ($data['decline_reason'] ?? ''));
            if ($reason === '') {
                return response()->json(['message' => 'Grund für die Ablehnung ist erforderlich.'], 422);
            }
            $data['decline_reason'] = $reason;
        } elseif (isset($data['status']) && $data['status'] !== 'declined') {
            $data['decline_reason'] = null;
        }

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
