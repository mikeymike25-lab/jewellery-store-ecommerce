@extends('admin.layouts.app')

@section('title', 'Edit Order')

@push('styles')
<style>
    .psgc-disabled {
        background-color: #f3f4f6 !important;
        color: #6b7280 !important;
        cursor: not-allowed !important;
    }
    
    .psgc-disabled option {
        color: #6b7280 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-xl shadow-lg">
                    <i data-lucide="edit" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-stone-900 dark:text-white">Edit Order - {{ $order->order_number }}</h1>
                    <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Update order information and details</p>
                </div>
            </div>
            <a href="{{ admin_route('orders.show', $order) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-stone-200 bg-white text-sm font-medium text-stone-700 transition-all duration-200 hover:bg-stone-50 hover:border-stone-300 dark:border-strokedark dark:bg-boxdark dark:text-white dark:hover:bg-gray-800">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Order
            </a>
        </div>
</div>

    <form action="{{ admin_route('orders.update', $order) }}" method="POST" class="space-y-8">
    @csrf
    @method('PUT')

    @php
        $bAddress = is_array($order->billing_address) ? $order->billing_address : [];
        $bName = old('billing_address.name', $bAddress['name'] ?? trim(($bAddress['first_name'] ?? '') . ' ' . ($bAddress['last_name'] ?? '')) ?: ($order->user->name ?? trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? ''))));
        $bStreet = old('billing_address.street', $bAddress['street'] ?? ($bAddress['address_line_1'] ?? ($order->user->street ?? '')));
        $bRegion = old('billing_address.region', $bAddress['region'] ?? ($order->user->region ?? ''));
        $bProvince = old('billing_address.province', $bAddress['province'] ?? ($order->user->province ?? ''));
        $bCity = old('billing_address.city', $bAddress['city'] ?? ($order->user->city ?? ''));
        $bBarangay = old('billing_address.barangay', $bAddress['barangay'] ?? ($order->user->barangay ?? ''));
        $bZip = old('billing_address.zip_code', $bAddress['zip_code'] ?? ($bAddress['postal_code'] ?? ($order->user->zip_code ?? '')));

        $sAddress = is_array($order->shipping_address) ? $order->shipping_address : [];
        $sName = old('shipping_address.name', $sAddress['name'] ?? trim(($sAddress['first_name'] ?? '') . ' ' . ($sAddress['last_name'] ?? '')) ?: ($order->user->name ?? trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? ''))));
        $sStreet = old('shipping_address.street', $sAddress['street'] ?? ($sAddress['address_line_1'] ?? ($order->user->street ?? '')));
        $sRegion = old('shipping_address.region', $sAddress['region'] ?? ($order->user->region ?? ''));
        $sProvince = old('shipping_address.province', $sAddress['province'] ?? ($order->user->province ?? ''));
        $sCity = old('shipping_address.city', $sAddress['city'] ?? ($order->user->city ?? ''));
        $sBarangay = old('shipping_address.barangay', $sAddress['barangay'] ?? ($order->user->barangay ?? ''));
        $sZip = old('shipping_address.zip_code', $sAddress['zip_code'] ?? ($sAddress['postal_code'] ?? ($order->user->zip_code ?? '')));
    @endphp
    
        <!-- Main Grid Layout -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Left Column - Main Content -->
            <div class="xl:col-span-2 space-y-8">
            <!-- Order Status -->
                <div class="bg-white dark:bg-boxdark rounded-2xl shadow-xl border border-stone-200 dark:border-strokedark overflow-hidden">
                    <div class="px-8 py-6 border-b border-stone-200 dark:border-strokedark bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl">
                                <i data-lucide="activity" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white">Order Status</h3>
                        </div>
                        <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Update order and payment status</p>
                </div>
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                    Order Status <span class="text-red-500">*</span>
                            </label>
                                <select name="status" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="returned" {{ $order->status === 'returned' ? 'selected' : '' }}>Returned</option>
                            </select>
                        </div>
                        
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                    Payment Status <span class="text-red-500">*</span>
                            </label>
                                <select name="payment_status" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                Shipping Method
                            </label>
                                <input type="text" 
                                       name="shipping_method" 
                                       value="{{ $order->shipping_method }}" 
                                       placeholder="e.g., Standard, Express, Free"
                                       class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                        </div>
                        
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                Tracking Number
                            </label>
                                <input type="text" 
                                       name="tracking_number" 
                                       value="{{ $order->tracking_number }}" 
                                       placeholder="Enter tracking number"
                                       class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
                <div class="bg-white dark:bg-boxdark rounded-2xl shadow-xl border border-stone-200 dark:border-strokedark overflow-hidden">
                    <div class="px-8 py-6 border-b border-stone-200 dark:border-strokedark bg-gradient-to-r from-rose-50 to-pink-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl">
                                <i data-lucide="credit-card" class="w-5 h-5 text-white"></i>
                </div>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white">Billing Address</h3>
                        </div>
                        <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Customer billing information</p>
                    </div>
                    <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="billing_address[name]" 
                                           value="{{ $bName }}"
                                           placeholder="Enter customer full name"
                                           class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        Street Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="billing_address[street]" 
                                           value="{{ $bStreet }}"
                                           placeholder="Enter street address"
                                           class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                </div>
                        
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                            Region <span class="text-red-500">*</span>
                                        </label>
                                        <select name="billing_address[region]" 
                                                id="billing_region"
                                                data-selected="{{ $bRegion }}"
                                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                            <option value="">Select Region</option>
                                            @if($bRegion)
                                                <option value="{{ $bRegion }}" selected>{{ $bRegion }}</option>
                                            @endif
                                        </select>
                                    </div>
                        
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                            Province <span class="text-red-500">*</span>
                                        </label>
                                        <select name="billing_address[province]" 
                                                id="billing_province"
                                                data-selected="{{ $bProvince }}"
                                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                            <option value="">Select Province</option>
                                            @if($bProvince)
                                                <option value="{{ $bProvince }}" selected>{{ $bProvince }}</option>
                                            @endif
                                        </select>
                                    </div>
                        
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                            City <span class="text-red-500">*</span>
                                        </label>
                                        <select name="billing_address[city]" 
                                                id="billing_city"
                                                data-selected="{{ $bCity }}"
                                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                            <option value="">Select City</option>
                                            @if($bCity)
                                                <option value="{{ $bCity }}" selected>{{ $bCity }}</option>
                                            @endif
                                        </select>
                                    </div>
                        
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                            Barangay <span class="text-red-500">*</span>
                                        </label>
                                        <select name="billing_address[barangay]" 
                                                id="billing_barangay"
                                                data-selected="{{ $bBarangay }}"
                                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                            <option value="">Select Barangay</option>
                                            @if($bBarangay)
                                                <option value="{{ $bBarangay }}" selected>{{ $bBarangay }}</option>
                                            @endif
                                        </select>
                                    </div>
                        
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                            Zip Code <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="billing_address[zip_code]" 
                                               id="billing_zip_code"
                                               value="{{ $bZip }}"
                                               placeholder="Enter zip code"
                                               class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                    </div>
                        
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                            Country
                                        </label>
                                        <input type="text" 
                                               name="billing_address[country]" 
                                               value="Philippines"
                                               readonly
                                               class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500 dark:border-strokedark dark:bg-stone-800 dark:text-stone-400">
                                    </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
                <div class="bg-white dark:bg-boxdark rounded-2xl shadow-xl border border-stone-200 dark:border-strokedark overflow-hidden">
                    <div class="px-8 py-6 border-b border-stone-200 dark:border-strokedark bg-gradient-to-r from-cyan-50 to-teal-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-cyan-500 to-teal-600 rounded-xl">
                                <i data-lucide="truck" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white">Shipping Address</h3>
                </div>
                        <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Delivery information</p>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="sameAsBilling" 
                                   class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="sameAsBilling" class="ml-2 text-sm font-medium text-stone-700 dark:text-stone-300">
                                Same as billing address
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="shipping_address[name]" 
                                       value="{{ $sName }}"
                                       placeholder="Enter recipient full name"
                                       class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                    Street Address <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="shipping_address[street]" 
                                       value="{{ $sStreet }}"
                                       placeholder="Enter street address"
                                       class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        Region <span class="text-red-500">*</span>
                                    </label>
                                    <select name="shipping_address[region]" 
                                            id="shipping_region"
                                            data-selected="{{ $sRegion }}"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                        <option value="">Select Region</option>
                                        @if($sRegion)
                                            <option value="{{ $sRegion }}" selected>{{ $sRegion }}</option>
                                        @endif
                                    </select>
                                </div>
                        
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        Province <span class="text-red-500">*</span>
                                    </label>
                                    <select name="shipping_address[province]" 
                                            id="shipping_province"
                                            data-selected="{{ $sProvince }}"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                        <option value="">Select Province</option>
                                        @if($sProvince)
                                            <option value="{{ $sProvince }}" selected>{{ $sProvince }}</option>
                                        @endif
                                    </select>
                                </div>
                        
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        City <span class="text-red-500">*</span>
                                    </label>
                                    <select name="shipping_address[city]" 
                                            id="shipping_city"
                                            data-selected="{{ $sCity }}"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                        <option value="">Select City</option>
                                        @if($sCity)
                                            <option value="{{ $sCity }}" selected>{{ $sCity }}</option>
                                        @endif
                                    </select>
                                </div>
                        
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        Barangay <span class="text-red-500">*</span>
                                    </label>
                                    <select name="shipping_address[barangay]" 
                                            id="shipping_barangay"
                                            data-selected="{{ $sBarangay }}"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                        <option value="">Select Barangay</option>
                                        @if($sBarangay)
                                            <option value="{{ $sBarangay }}" selected>{{ $sBarangay }}</option>
                                        @endif
                                    </select>
                                </div>
                        
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        Zip Code <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="shipping_address[zip_code]" 
                                           id="shipping_zip_code"
                                           value="{{ $sZip }}"
                                           placeholder="Enter zip code"
                                           class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                                </div>
                        
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                        Country
                                    </label>
                                    <input type="text" 
                                           name="shipping_address[country]" 
                                           value="Philippines"
                                           readonly
                                           class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500 dark:border-strokedark dark:bg-stone-800 dark:text-stone-400">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="xl:col-span-1 space-y-8">
            <!-- Order Summary -->
                <div class="bg-white dark:bg-boxdark rounded-2xl shadow-xl border border-stone-200 dark:border-strokedark overflow-hidden">
                    <div class="px-8 py-6 border-b border-stone-200 dark:border-strokedark bg-gradient-to-r from-violet-50 to-fuchsia-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-violet-500 to-fuchsia-600 rounded-xl">
                                <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                </div>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white">Order Summary</h3>
                        </div>
                        <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Financial breakdown and order details</p>
                    </div>
                    <div class="p-8">
                        <div class="space-y-4">
                        <div class="flex justify-between">
                                <span class="text-stone-900 dark:text-white">Order Number:</span>
                                <span class="text-stone-900 dark:text-white font-medium">{{ $order->order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                                <span class="text-stone-900 dark:text-white">Subtotal:</span>
                                <span class="text-stone-900 dark:text-white">₱{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                                <span class="text-stone-900 dark:text-white">Tax:</span>
                                <span class="text-stone-900 dark:text-white">₱{{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                            <div class="flex justify-between">
                                <span class="text-stone-900 dark:text-white">Shipping:</span>
                                <span class="text-stone-900 dark:text-white">₱{{ number_format($order->shipping_amount, 2) }}</span>
                            </div>
                            <div class="border-t border-stone-200 pt-4 dark:border-strokedark">
                                <div class="flex justify-between">
                                    <span class="font-semibold text-stone-900 dark:text-white">Total:</span>
                                    <span class="font-semibold text-stone-900 dark:text-white">₱{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
                <div class="bg-white dark:bg-boxdark rounded-2xl shadow-xl border border-stone-200 dark:border-strokedark overflow-hidden">
                    <div class="px-8 py-6 border-b border-stone-200 dark:border-strokedark bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl">
                                <i data-lucide="user" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white">Customer</h3>
                        </div>
                        <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Customer information</p>
                </div>
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center shadow-lg">
                                <span class="text-white font-medium text-sm">
                                @if($order->user)
                                    {{ substr($order->user->first_name, 0, 1) }}{{ substr($order->user->last_name, 0, 1) }}
                                @else
                                    GU
                                @endif
                            </span>
                        </div>
                        <div>
                                <h4 class="font-medium text-stone-900 dark:text-white">
                                @if($order->user)
                                    {{ $order->user->first_name }} {{ $order->user->last_name }}
                                @else
                                    Guest User
                                @endif
                            </h4>
                                <p class="text-sm text-stone-500">
                                @if($order->user)
                                    {{ $order->user->email }}
                                @else
                                    No email available
                                @endif
                            </p>
                        </div>
                    </div>
                        
                        @if($order->user)
                            <a href="{{ admin_route('users.show', $order->user) }}" class="w-full inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-amber-500 to-yellow-600 px-4 py-3 text-center font-medium text-white hover:from-amber-600 hover:to-yellow-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                                View Customer
                            </a>
                        @else
                            <div class="w-full inline-flex items-center justify-center rounded-xl border border-stone-300 px-4 py-3 text-center font-medium text-stone-500 cursor-not-allowed">
                                <i data-lucide="user-x" class="w-4 h-4 mr-2"></i>
                                Guest Order
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="bg-white dark:bg-boxdark rounded-2xl shadow-xl border border-stone-200 dark:border-strokedark overflow-hidden">
                    <div class="px-8 py-6 border-b border-stone-200 dark:border-strokedark bg-gradient-to-r from-lime-50 to-green-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-lime-500 to-green-600 rounded-xl">
                                <i data-lucide="sticky-note" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white">Order Notes</h3>
                        </div>
                        <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Additional notes and comments</p>
                    </div>
                    <div class="p-8">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                Notes
                            </label>
                            <textarea name="notes" 
                                      rows="4" 
                                      placeholder="Add any notes about this order..."
                                      class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">{{ $order->notes }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions -->
                <div class="bg-white dark:bg-boxdark rounded-2xl shadow-xl border border-stone-200 dark:border-strokedark overflow-hidden">
                    <div class="px-8 py-6 border-b border-stone-200 dark:border-strokedark bg-gradient-to-r from-slate-50 to-gray-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-slate-500 to-gray-600 rounded-xl">
                                <i data-lucide="settings" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white">Actions</h3>
                        </div>
                        <p class="mt-1 text-sm text-stone-600 dark:text-gray-400">Save or cancel changes</p>
                    </div>
                    <div class="p-8">
                        <div class="space-y-4">
                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-blue-600 px-6 py-3 text-center font-medium text-white hover:from-emerald-700 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Update Order
                        </button>
                        
                            <a href="{{ admin_route('orders.show', $order) }}" class="w-full inline-flex items-center justify-center rounded-xl border border-stone-200 px-6 py-3 text-center font-medium text-stone-700 hover:bg-stone-50 transition-all duration-200 dark:border-strokedark dark:text-white dark:hover:bg-gray-800">
                            <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
</div>

@endsection

@push('scripts')
<script>
    // PSGC API Integration
    const PSGC_BASE_URL = 'https://psgc.cloud/api';
    
    // Helper to populate a select and preserve selection
    function populateSelect(selectElement, items, placeholder = 'Select Option', selectedValue = null) {
        if (!selectElement) return;
        const currentVal = selectedValue || selectElement.value || selectElement.dataset.selected || '';
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        let matched = false;
        
        items.forEach(item => {
            const name = typeof item === 'string' ? item : item.name;
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            if (currentVal && name.toLowerCase() === currentVal.toLowerCase()) {
                option.selected = true;
                matched = true;
            }
            selectElement.appendChild(option);
        });

        // Ensure pre-selected server rendered value isn't lost if not in remote list
        if (currentVal && !matched) {
            const fallbackOption = document.createElement('option');
            fallbackOption.value = currentVal;
            fallbackOption.textContent = currentVal;
            fallbackOption.selected = true;
            selectElement.appendChild(fallbackOption);
        }
    }

    // Load regions
    async function loadRegions() {
        try {
            const response = await fetch(`${PSGC_BASE_URL}/regions`);
            const regions = await response.json();
            
            const billingRegion = document.getElementById('billing_region');
            const shippingRegion = document.getElementById('shipping_region');

            if (billingRegion) {
                populateSelect(billingRegion, regions, 'Select Region', billingRegion.dataset.selected || billingRegion.value);
            }
            if (shippingRegion) {
                populateSelect(shippingRegion, regions, 'Select Region', shippingRegion.dataset.selected || shippingRegion.value);
            }
        } catch (error) {
            console.error('Error loading regions:', error);
        }
    }
    
    // Load provinces for a region
    async function loadProvinces(regionName, provinceSelectId, selectedValue = null) {
        const provinceSelect = document.getElementById(provinceSelectId);
        if (!provinceSelect) return;
        if (!regionName) {
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            return;
        }

        if (regionName.toLowerCase().includes('ncr') || regionName.toLowerCase().includes('national capital region')) {
            provinceSelect.disabled = true;
            provinceSelect.classList.add('psgc-disabled');
            provinceSelect.innerHTML = '<option value="N/A (NCR)" selected>N/A (NCR)</option>';
            return;
        }

        provinceSelect.disabled = false;
        provinceSelect.classList.remove('psgc-disabled');

        try {
            const response = await fetch(`${PSGC_BASE_URL}/regions/${encodeURIComponent(regionName)}/provinces`);
            const provinces = await response.json();
            populateSelect(provinceSelect, provinces, 'Select Province', selectedValue || provinceSelect.dataset.selected || provinceSelect.value);
        } catch (error) {
            console.error('Error loading provinces:', error);
        }
    }
    
    // Load cities for a province or NCR
    async function loadCities(regionName, provinceName, citySelectId, selectedValue = null) {
        const citySelect = document.getElementById(citySelectId);
        if (!citySelect) return;

        try {
            let url;
            if (regionName && (regionName.toLowerCase().includes('ncr') || regionName.toLowerCase().includes('national capital region'))) {
                url = `${PSGC_BASE_URL}/regions/National Capital Region/cities`;
            } else if (provinceName && provinceName !== 'N/A (NCR)') {
                url = `${PSGC_BASE_URL}/provinces/${encodeURIComponent(provinceName)}/cities-municipalities`;
            } else {
                return;
            }

            let response = await fetch(url);
            let cities = await response.json();

            if (!Array.isArray(cities) || cities.length === 0) {
                if (provinceName && provinceName !== 'N/A (NCR)') {
                    response = await fetch(`${PSGC_BASE_URL}/provinces/${encodeURIComponent(provinceName)}/cities`);
                    cities = await response.json();
                }
            }

            populateSelect(citySelect, Array.isArray(cities) ? cities : [], 'Select City', selectedValue || citySelect.dataset.selected || citySelect.value);
        } catch (error) {
            console.error('Error loading cities:', error);
        }
    }
    
    // Load barangays for a city
    async function loadBarangays(cityName, barangaySelectId, selectedValue = null) {
        const barangaySelect = document.getElementById(barangaySelectId);
        if (!barangaySelect || !cityName) return;

        try {
            let response = await fetch(`${PSGC_BASE_URL}/cities-municipalities/${encodeURIComponent(cityName)}/barangays`);
            let barangays = await response.json();

            if (!Array.isArray(barangays) || barangays.length === 0) {
                response = await fetch(`${PSGC_BASE_URL}/cities/${encodeURIComponent(cityName)}/barangays`);
                barangays = await response.json();
            }

            populateSelect(barangaySelect, Array.isArray(barangays) ? barangays : [], 'Select Barangay', selectedValue || barangaySelect.dataset.selected || barangaySelect.value);
        } catch (error) {
            console.error('Error loading barangays:', error);
        }
    }
    
    // Initialize PSGC functionality
    function initializePSGC() {
        loadRegions();
        
        // Setup Billing Address listeners
        const billingRegion = document.getElementById('billing_region');
        const billingProvince = document.getElementById('billing_province');
        const billingCity = document.getElementById('billing_city');
        const billingBarangay = document.getElementById('billing_barangay');

        billingRegion?.addEventListener('change', function() {
            const regionName = this.value;
            this.dataset.selected = regionName;
            billingProvince.innerHTML = '<option value="">Select Province</option>';
            billingCity.innerHTML = '<option value="">Select City</option>';
            billingBarangay.innerHTML = '<option value="">Select Barangay</option>';
            billingProvince.dataset.selected = '';
            billingCity.dataset.selected = '';
            billingBarangay.dataset.selected = '';
            
            if (regionName) {
                if (regionName.toLowerCase().includes('ncr') || regionName.toLowerCase().includes('national capital region')) {
                    billingProvince.disabled = true;
                    billingProvince.classList.add('psgc-disabled');
                    billingProvince.innerHTML = '<option value="N/A (NCR)" selected>N/A (NCR)</option>';
                    loadCities(regionName, null, 'billing_city');
                } else {
                    billingProvince.disabled = false;
                    billingProvince.classList.remove('psgc-disabled');
                    loadProvinces(regionName, 'billing_province');
                }
            }
        });
        
        billingProvince?.addEventListener('focus', function() {
            if (this.options.length <= 2 && billingRegion?.value && !billingRegion.value.toLowerCase().includes('ncr')) {
                loadProvinces(billingRegion.value, 'billing_province', this.value);
            }
        });

        billingProvince?.addEventListener('change', function() {
            this.dataset.selected = this.value;
            billingCity.innerHTML = '<option value="">Select City</option>';
            billingBarangay.innerHTML = '<option value="">Select Barangay</option>';
            billingCity.dataset.selected = '';
            billingBarangay.dataset.selected = '';
            
            if (this.value && this.value !== 'N/A (NCR)') {
                loadCities(billingRegion?.value, this.value, 'billing_city');
            }
        });

        billingCity?.addEventListener('focus', function() {
            if (this.options.length <= 2 && (billingProvince?.value || billingRegion?.value)) {
                loadCities(billingRegion?.value, billingProvince?.value, 'billing_city', this.value);
            }
        });
        
        billingCity?.addEventListener('change', function() {
            this.dataset.selected = this.value;
            billingBarangay.innerHTML = '<option value="">Select Barangay</option>';
            billingBarangay.dataset.selected = '';
            
            if (this.value) {
                loadBarangays(this.value, 'billing_barangay');
            }
        });

        billingBarangay?.addEventListener('focus', function() {
            if (this.options.length <= 2 && billingCity?.value) {
                loadBarangays(billingCity.value, 'billing_barangay', this.value);
            }
        });

        billingBarangay?.addEventListener('change', function() {
            this.dataset.selected = this.value;
        });
        
        // Setup Shipping Address listeners
        const shippingRegion = document.getElementById('shipping_region');
        const shippingProvince = document.getElementById('shipping_province');
        const shippingCity = document.getElementById('shipping_city');
        const shippingBarangay = document.getElementById('shipping_barangay');

        shippingRegion?.addEventListener('change', function() {
            const regionName = this.value;
            this.dataset.selected = regionName;
            shippingProvince.innerHTML = '<option value="">Select Province</option>';
            shippingCity.innerHTML = '<option value="">Select City</option>';
            shippingBarangay.innerHTML = '<option value="">Select Barangay</option>';
            shippingProvince.dataset.selected = '';
            shippingCity.dataset.selected = '';
            shippingBarangay.dataset.selected = '';
            
            if (regionName) {
                if (regionName.toLowerCase().includes('ncr') || regionName.toLowerCase().includes('national capital region')) {
                    shippingProvince.disabled = true;
                    shippingProvince.classList.add('psgc-disabled');
                    shippingProvince.innerHTML = '<option value="N/A (NCR)" selected>N/A (NCR)</option>';
                    loadCities(regionName, null, 'shipping_city');
                } else {
                    shippingProvince.disabled = false;
                    shippingProvince.classList.remove('psgc-disabled');
                    loadProvinces(regionName, 'shipping_province');
                }
            }
        });

        shippingProvince?.addEventListener('focus', function() {
            if (this.options.length <= 2 && shippingRegion?.value && !shippingRegion.value.toLowerCase().includes('ncr')) {
                loadProvinces(shippingRegion.value, 'shipping_province', this.value);
            }
        });
        
        shippingProvince?.addEventListener('change', function() {
            this.dataset.selected = this.value;
            shippingCity.innerHTML = '<option value="">Select City</option>';
            shippingBarangay.innerHTML = '<option value="">Select Barangay</option>';
            shippingCity.dataset.selected = '';
            shippingBarangay.dataset.selected = '';
            
            if (this.value && this.value !== 'N/A (NCR)') {
                loadCities(shippingRegion?.value, this.value, 'shipping_city');
            }
        });

        shippingCity?.addEventListener('focus', function() {
            if (this.options.length <= 2 && (shippingProvince?.value || shippingRegion?.value)) {
                loadCities(shippingRegion?.value, shippingProvince?.value, 'shipping_city', this.value);
            }
        });
        
        shippingCity?.addEventListener('change', function() {
            this.dataset.selected = this.value;
            shippingBarangay.innerHTML = '<option value="">Select Barangay</option>';
            shippingBarangay.dataset.selected = '';
            
            if (this.value) {
                loadBarangays(this.value, 'shipping_barangay');
            }
        });

        shippingBarangay?.addEventListener('focus', function() {
            if (this.options.length <= 2 && shippingCity?.value) {
                loadBarangays(shippingCity.value, 'shipping_barangay', this.value);
            }
        });

        shippingBarangay?.addEventListener('change', function() {
            this.dataset.selected = this.value;
        });

        // Same as billing address checkbox
        const sameAsBilling = document.getElementById('sameAsBilling');
        sameAsBilling?.addEventListener('change', function() {
            if (this.checked) {
                const fields = ['name', 'street', 'region', 'province', 'city', 'barangay', 'zip_code'];
                fields.forEach(field => {
                    const bEl = document.querySelector(`[name="billing_address[${field}]"]`);
                    const sEl = document.querySelector(`[name="shipping_address[${field}]"]`);
                    if (bEl && sEl) {
                        if (sEl.tagName === 'SELECT') {
                            if (bEl.value && !Array.from(sEl.options).some(o => o.value === bEl.value)) {
                                const opt = document.createElement('option');
                                opt.value = bEl.value;
                                opt.textContent = bEl.value;
                                sEl.appendChild(opt);
                            }
                            sEl.value = bEl.value;
                            sEl.dataset.selected = bEl.value;
                        } else {
                            sEl.value = bEl.value;
                        }
                    }
                });
            }
        });
    }
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializePSGC();
        lucide.createIcons();
    });
</script>
@endpush