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

        $appointment = Appointment::create(array_merge($validated, [
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]));

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
