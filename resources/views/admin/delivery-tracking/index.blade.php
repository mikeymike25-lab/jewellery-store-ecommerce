@extends('admin.layouts.app')

@section('title', 'Delivery Tracking')

@section('content')
<div class="min-h-screen">
    <!-- Header -->
    <div class="border-b border-stone-200 dark:border-strokedark">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">Delivery Tracking</h1>
                <p class="mt-1 text-sm text-stone-600">Track and manage order deliveries</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="pt-6 pb-3">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            <!-- Total Shipped -->
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-stone-500">Total Shipped</p>
                        <p class="text-2xl font-semibold text-stone-900">{{ number_format($stats['total_shipped'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- In Transit -->
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-stone-500">In Transit</p>
                        <p class="text-2xl font-semibold text-stone-900">{{ number_format($stats['in_transit'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Delivered -->
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-stone-500">Total Delivered</p>
                        <p class="text-2xl font-semibold text-stone-900">{{ number_format($stats['total_delivered'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Returns -->
            <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="refresh-cw" class="w-5 h-5 text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-stone-500">Active RMAs</p>
                        <p class="text-2xl font-semibold text-stone-900">{{ number_format($stats['active_returns'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="pt-3 pb-6">
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6">
            <form method="GET" action="{{ admin_route('delivery-tracking.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[300px]">
                    <label for="search" class="block text-sm font-medium text-stone-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Order #, RMA #, Tracking #, Customer..."
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 whitespace-nowrap">
                        Filter
                    </button>
                    <a href="{{ admin_route('delivery-tracking.index') }}" class="inline-flex items-center justify-center rounded-lg border border-stone-300 px-4 py-2.5 text-sm hover:bg-stone-50">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tracking List -->
    <div class="pb-8">
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200">
                        <thead class="bg-stone-50 text-stone-500">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tracking / RMA</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Last Update</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-stone-200">
                            @foreach($items as $item)
                                <tr class="hover:bg-stone-50/50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->tracking_type === 'order')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                                Outgoing
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                                Return/Repair
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-stone-900">
                                            {{ $item->tracking_type === 'order' ? '#' . $item->order_number : ($item->rma_number ?: 'Pending RMA') }}
                                        </div>
                                        @if($item->tracking_type === 'return' && $item->order)
                                            <div class="text-xs text-stone-500">Order #{{ $item->order->order_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-900">
                                        <div class="font-medium font-stone-900">{{ $item->user->first_name }} {{ $item->user->last_name }}</div>
                                        <div class="text-stone-400 text-xs">{{ $item->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->tracking_type === 'order')
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 bg-stone-100 rounded text-xs font-mono text-stone-600">
                                                    {{ $item->tracking_number }}
                                                </span>
                                                <button onclick="copyTrackingNumber('{{ $item->tracking_number }}')" class="p-1 hover:bg-stone-100 rounded transition-colors" title="Copy">
                                                    <i data-lucide="copy" class="w-3.5 h-3.5 text-stone-400"></i>
                                                </button>
                                            </div>
                                            @if($item->carrier)
                                                <div class="text-[10px] text-stone-400 uppercase mt-1 px-0.5 tracking-wider">{{ $item->carrier }}</div>
                                            @endif
                                        @else
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 bg-purple-50 rounded text-xs font-mono text-purple-600 border border-purple-100">
                                                    {{ $item->rma_number ?: 'NOT ASSIGNED' }}
                                                </span>
                                                @if($item->rma_number)
                                                <button onclick="copyTrackingNumber('{{ $item->rma_number }}')" class="p-1 hover:bg-purple-100 rounded transition-colors" title="Copy">
                                                    <i data-lucide="copy" class="w-3.5 h-3.5 text-purple-400"></i>
                                                </button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = match($item->status) {
                                                'delivered', 'completed', 'refunded' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'shipped', 'approved', 'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'received' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                default => 'bg-stone-100 text-stone-800 border-stone-200'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-500">
                                        @if($item->tracking_type === 'order')
                                            {{ $item->shipped_at ? $item->shipped_at->format('M d, Y') : '---' }}
                                        @else
                                            {{ $item->updated_at ? $item->updated_at->format('M d, Y') : '---' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($item->tracking_type === 'order')
                                                <a href="{{ admin_route('delivery-tracking.show', $item) }}" class="p-2 text-stone-600 hover:text-emerald-600 transition-colors" title="Track Details">
                                                    <i data-lucide="map-pin" class="w-4.5 h-4.5"></i>
                                                </a>
                                                <a href="{{ admin_route('orders.show', $item) }}" class="p-2 text-stone-600 hover:text-emerald-600 transition-colors" title="View Order">
                                                    <i data-lucide="external-link" class="w-4.5 h-4.5"></i>
                                                </a>
                                            @else
                                                <a href="#" class="p-2 text-stone-600 hover:text-purple-600 transition-colors" title="View Return Details">
                                                    <i data-lucide="eye" class="w-4.5 h-4.5"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-16 text-center">
                    <div class="mx-auto h-20 w-20 rounded-full bg-stone-50 flex items-center justify-center mb-6">
                        <i data-lucide="truck" class="h-10 w-10 text-stone-300"></i>
                    </div>
                    <h3 class="text-stone-900 font-bold text-lg">No Logistics Activity</h3>
                    <p class="text-stone-500 max-w-sm mx-auto mt-2">There are currently no active deliveries or confirmed returns in the system.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyTrackingNumber(trackingNumber) {
    navigator.clipboard.writeText(trackingNumber).then(() => {
        // Show notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 px-6 py-3 rounded-xl text-white z-50 shadow-lg bg-green-500';
        notification.textContent = 'Tracking number copied!';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>
@endpush
@endsection
