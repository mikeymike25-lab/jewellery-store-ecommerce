<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of all appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Sort
        $sortBy = $request->get('sort', 'appointment_date');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $appointments = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
        ];

        return view('admin.appointments.index', compact('appointments', 'stats'));
    }

    /**
     * Display a single appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load('user');

        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $previousStatus = $appointment->status;
        $appointment->update(['status' => $request->status]);

        // Send customer notification if status changed and patron has a linked user
        if ($previousStatus !== $request->status) {
            $userId = $appointment->user_id;
            if (! $userId) {
                $user = \App\Models\User::where('email', $appointment->email)->first();
                $userId = $user?->id;
            }

            if ($userId) {
                $statusLabel = ucfirst($request->status);
                \App\Models\Notification::create([
                    'type' => 'system',
                    'recipient_type' => 'user',
                    'recipient_id' => $userId,
                    'title' => "Salon Appointment {$statusLabel}",
                    'message' => "Your salon appointment at {$appointment->location} on {$appointment->appointment_date} at {$appointment->appointment_time} is now {$statusLabel}.",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'service_type' => $appointment->service_type,
                        'location' => $appointment->location,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                        'status' => $appointment->status,
                        'type' => 'appointment',
                    ],
                    'status' => 'sent',
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated.',
                'status' => $appointment->status,
            ]);
        }

        return redirect()->back()->with('success', 'Appointment status updated to '.$request->status.'.');
    }

    /**
     * Delete an appointment.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->to(admin_route('appointments.index'))
            ->with('success', 'Appointment deleted successfully.');
    }
}
