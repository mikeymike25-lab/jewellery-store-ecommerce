<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Search for an order by order number and email.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->where(function ($query) use ($validated) {
                $query->whereHas('user', function ($userQuery) use ($validated) {
                    $userQuery->where('email', $validated['email']);
                })->orWhere('shipping_address->email', $validated['email']);
            })
            ->with(['orderItems.product'])
            ->first();

        if (! $order) {
            return back()->withErrors(['order_number' => 'Order not found. Please verify your order number and email.']);
        }

        return view('track-order', compact('order'));
    }

    /**
     * REST API v1 endpoint to track order by order number and email.
     */
    public function trackOrderApi(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));
        $order = Order::where('order_number', trim($validated['order_number']))
            ->where(function ($query) use ($email) {
                $query->whereHas('user', function ($userQuery) use ($email) {
                    $userQuery->whereRaw('LOWER(email) = ?', [$email]);
                })
                ->orWhereRaw("LOWER(json_extract(shipping_address, '$.email')) = ?", [$email])
                ->orWhereRaw("LOWER(json_extract(billing_address, '$.email')) = ?", [$email]);
            })
            ->with(['orderItems.product'])
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'No order found matching the provided order number and email address.',
            ], 404);
        }

        // Build 4-stage tracking timeline
        $timeline = [
            [
                'stage' => 'placed',
                'title' => 'Order Placed',
                'description' => 'Your acquisition was received and confirmed.',
                'date' => $order->created_at ? $order->created_at->format('M d, Y h:i A') : null,
                'completed' => true,
                'current' => in_array($order->status, ['pending', 'payment_pending']),
            ],
            [
                'stage' => 'crafting',
                'title' => 'Atelier Crafting',
                'description' => 'Fine jewellery verified, certified and prepared for dispatch.',
                'date' => in_array($order->status, ['processing', 'shipped', 'delivered']) ? ($order->updated_at ? $order->updated_at->format('M d, Y') : null) : null,
                'completed' => in_array($order->status, ['processing', 'shipped', 'delivered']),
                'current' => $order->status === 'processing',
            ],
            [
                'stage' => 'transit',
                'title' => 'Armored Courier In Transit',
                'description' => $order->tracking_number ? 'En route via insured carrier (' . ($order->courier ?? 'Armored Express Logistics') . ')' : 'Prepared for armored carrier handoff.',
                'date' => in_array($order->status, ['shipped', 'delivered']) ? ($order->updated_at ? $order->updated_at->format('M d, Y') : null) : null,
                'completed' => in_array($order->status, ['shipped', 'delivered']),
                'current' => $order->status === 'shipped',
            ],
            [
                'stage' => 'delivered',
                'title' => 'Delivered',
                'description' => 'Safely received at customer delivery destination.',
                'date' => $order->status === 'delivered' ? ($order->updated_at ? $order->updated_at->format('M d, Y') : null) : null,
                'completed' => $order->status === 'delivered',
                'current' => $order->status === 'delivered',
            ],
        ];

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'tracking_number' => $order->tracking_number,
                'courier' => $order->courier ?? 'Armored Express Logistics',
                'shipping_carrier' => $order->courier ?? 'Armored Express Logistics',
                'estimated_delivery' => $order->estimated_delivery,
                'subtotal' => (float) $order->subtotal,
                'tax_amount' => (float) $order->tax_amount,
                'shipping_amount' => (float) $order->shipping_amount,
                'total_amount' => (float) $order->total_amount,
                'currency' => $order->currency ?? 'PHP',
                'created_at' => $order->created_at ? $order->created_at->format('F j, Y') : null,
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'product_sku' => $item->product_sku,
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total_price' => (float) $item->total_price,
                        'image_url' => $item->product ? $item->product->image_url : null,
                    ];
                }),
                'shipping_address' => is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address,
                'timeline' => $timeline,
            ],
        ]);
    }
}
