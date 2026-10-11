<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'location' => 'required|string|max:255',
            'service_type' => 'required|string|max:255',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user() ?: auth()->user();
        $userId = $user?->id;
        if (! $userId) {
            $matchedUser = \App\Models\User::where('email', $validated['email'])->first();
            $userId = $matchedUser?->id;
        }

        $appointment = Appointment::create(array_merge($validated, [
            'user_id' => $userId,
            'status' => 'pending',
        ]));

        // Create in-app customer notification if a user account is linked
        try {
            if ($userId) {
                \App\Models\Notification::create([
                    'type' => 'system',
                    'recipient_type' => 'user',
                    'recipient_id' => $userId,
                    'title' => 'Salon Appointment Requested',
                    'message' => "Your salon appointment at {$appointment->location} on {$appointment->appointment_date} at {$appointment->appointment_time} is being coordinated by our concierge.",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'service_type' => $appointment->service_type,
                        'location' => $appointment->location,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                        'type' => 'appointment',
                    ],
                    'status' => 'sent',
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to create user appointment notification: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Your appointment request has been received. Our concierge will contact you shortly.',
                'data' => $appointment,
            ]);
        }

        return back()->with('success', 'Your appointment request has been received. Our concierge will contact you shortly.');
    }

    /**
     * List appointments for the authenticated customer.
     */
    public function userAppointments(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $appointments = Appointment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $appointments->items(),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }
}
