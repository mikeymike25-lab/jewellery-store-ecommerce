<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Handle a new newsletter subscription.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        NewsletterSubscription::firstOrCreate(
            ['email' => $validated['email']],
            ['is_active' => true]
        );

        if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for joining the world of Éclore.',
            ]);
        }

        return back()->with('success', 'Thank you for joining the world of Éclore.');
    }
}
