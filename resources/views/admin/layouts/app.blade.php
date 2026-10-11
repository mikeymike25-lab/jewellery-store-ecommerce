<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - Éclore Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/favicon.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Preline UI -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.css">
    
    <!-- Dark Mode Root Sync -->
    <script>
        if (localStorage.getItem('darkMode') === 'true' || 
            (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3C50E0',
                        secondary: '#004D61', // dark teal (Accent 1)
                        success: '#3E5641', // forest green (CTA)
                        danger: '#822659', // deep ruby (Accent 2)
                        warning: '#FFA70B',
                        info: '#0FADCF',
                        
                        // Brand Colors - Light Mode
                        'brand-dark': '#0D1E1E',      // Primary dark navy
                        'brand-green': '#52734F',     // Sage green accent
                        'brand-beige': '#D3D0CF',     // Light beige background
                        'brand-brown': '#6C464E',     // Muted brown
                        'brand-rose': '#96616B',      // Rose accent
                        
                        // Dark Mode Variants
                        'brand-dark-dm': '#1A1A1A',   
                        'brand-green-dm': '#3E5641',  
                        'brand-beige-dm': '#2A2826',  
                        'brand-brown-dm': '#8B5D68',  
                        'brand-rose-dm': '#B38791',   
                        
                        // Custom Private Palette
                        'dm-bg': '#121212',      // Material Dark Base
                        'dm-surface': '#1E1E1E', // Material Elevation Surface
                        'dm-teal': '#80CBC4',    // Accessible Teal
                        'dm-ruby': '#F48FB1',    // Accessible Ruby
                        'dm-green': '#A5D6A7',   // Accessible Forest Green
                        'dm-text-primary': 'rgba(255, 255, 255, 0.87)',
                        'dm-text-secondary': 'rgba(255, 255, 255, 0.60)',
                     
                        dark: '#1C2434',
                        'body': '#64748B',
                        'bodydark': '#F0F0F0', // off-white
                        'bodydark1': '#DEE4EE',
                        'bodydark2': '#8A99AF',
                        'stroke': '#E2E8F0',
                        'gray': '#EFF4FB',
                        'graydark': '#333A48',
                        'whiten': '#F1F5F9',
                        'whiter': '#F5F7FD',
                        'boxdark': '#242424', // cards/sidebar
                        'boxdark-2': '#1A1A1A', // background
                        'strokedark': '#333333',
                        'form-strokedark': '#3d4d60',
                        'form-input': '#1d2a39',
                        'meta-1': '#DC3545',
                        'meta-2': '#EFF2F7',
                        'meta-3': '#10B981',
                        'meta-4': '#313D4A',
                        'meta-5': '#259AE6',
                        'meta-6': '#FFBA00',
                        'meta-7': '#FF6766',
                        'meta-8': '#F0950C',
                        'meta-9': '#E5E7EB',
                        'meta-10': '#0FADCF',
                    },
                    fontSize: {
                        'title-xxl': ['44px', '55px'],
                        'title-xl': ['36px', '45px'],
                        'title-xl2': ['33px', '45px'],
                        'title-lg': ['28px', '35px'],
                        'title-md': ['24px', '30px'],
                        'title-md2': ['26px', '30px'],
                        'title-sm': ['20px', '26px'],
                        'title-sm2': ['22px', '28px'],
                        'title-xsm': ['18px', '24px'],
                    },
                    spacing: {
                        '4.5': '1.125rem',
                        '5.5': '1.375rem',
                        '6.5': '1.625rem',
                        '7.5': '1.875rem',
                        '8.5': '2.125rem',
                        '9.5': '2.375rem',
                        '10.5': '2.625rem',
                        '11.5': '2.875rem',
                        '12.5': '3.125rem',
                        '13': '3.25rem',
                        '13.5': '3.375rem',
                        '14.5': '3.625rem',
                        '15': '3.75rem',
                        '15.5': '3.875rem',
                        '16.5': '4.125rem',
                        '17': '4.25rem',
                        '17.5': '4.375rem',
                        '18': '4.5rem',
                        '18.5': '4.625rem',
                        '19': '4.75rem',
                        '19.5': '4.875rem',
                        '21': '5.25rem',
                        '21.5': '5.375rem',
                        '22': '5.5rem',
                        '22.5': '5.625rem',
                        '24.5': '6.125rem',
                        '25': '6.25rem',
                        '25.5': '6.375rem',
                        '26': '6.5rem',
                        '27': '6.75rem',
                        '27.5': '6.875rem',
                        '29': '7.25rem',
                        '29.5': '7.375rem',
                        '30': '7.5rem',
                        '32.5': '8.125rem',
                        '34': '8.5rem',
                        '35': '8.75rem',
                        '36.5': '9.125rem',
                        '37.5': '9.375rem',
                        '39': '9.75rem',
                        '39.5': '9.875rem',
                        '40': '10rem',
                        '42.5': '10.625rem',
                        '44': '11rem',
                        '45': '11.25rem',
                        '46': '11.5rem',
                        '47.5': '11.875rem',
                        '49': '12.25rem',
                        '50': '12.5rem',
                        '52.5': '13.125rem',
                        '54': '13.5rem',
                        '54.5': '13.625rem',
                        '55': '13.75rem',
                        '55.5': '13.875rem',
                        '59': '14.75rem',
                        '60': '15rem',
                        '62.5': '15.625rem',
                        '65': '16.25rem',
                        '67': '16.75rem',
                        '67.5': '16.875rem',
                        '70': '17.5rem',
                        '72.5': '18.125rem',
                        '73': '18.25rem',
                        '75': '18.75rem',
                        '90': '22.5rem',
                        '94': '23.5rem',
                        '95': '23.75rem',
                        '100': '25rem',
                        '115': '28.75rem',
                        '125': '31.25rem',
                        '132.5': '33.125rem',
                        '150': '37.5rem',
                        '171.5': '42.875rem',
                        '180': '45rem',
                        '187.5': '46.875rem',
                        '203': '50.75rem',
                        '230': '57.5rem',
                        '242.5': '60.625rem',
                    },
                    maxWidth: {
                        '2.5xl': '45rem',
                        '3xl': '48rem',
                        '4xl': '56rem',
                        '5xl': '64rem',
                        '6xl': '72rem',
                        '7xl': '80rem',
                    },
                    zIndex: {
                        '999': '999',
                        '9999': '9999',
                        '99999': '99999',
                        '999999': '999999',
                        '9999999': '9999999',
                    },
                }
            }
        }
    </script>
    
    <!-- Lucide Icons - Local File -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        });
    </script>
    
    <!-- Alpine.js Persist Plugin -->
    <script defer src="https://unpkg.com/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <!-- Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <!-- Laravel Echo -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Hide scrollbar but keep functionality */
        .no-scrollbar {
            -ms-overflow-style: none;  /* Internet Explorer 10+ */
            scrollbar-width: none;  /* Firefox */
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;  /* Safari and Chrome */
        }
        
        /* Quill Editor Custom Styles */
        .ql-editor {
            font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .ql-toolbar {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            border-bottom: none;
        }
        
        .ql-container {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        
        .ql-toolbar:hover,
        .ql-container:hover {
            border-color: #cbd5e1;
        }
        
        .ql-toolbar:focus-within,
        .ql-container:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .dark .ql-toolbar {
            background-color: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }
        
        .dark .ql-container {
            background-color: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }
        
        .dark .ql-editor {
            color: #f9fafb;
        }
        
        .dark .ql-stroke {
            stroke: #f9fafb;
        }
        
        .dark .ql-fill {
            fill: #f9fafb;
        }
        
        .dark .ql-picker-label {
            color: #f9fafb;
        }
        
        .dark .ql-picker-options {
            background-color: #374151;
            border-color: #4b5563;
        }
        
        .dark .ql-picker-item {
            color: #f9fafb;
        }
        
        .dark .ql-picker-item:hover {
            background-color: #4b5563;
        }
        
        /* Media Library Modal Styles */
        .media-library-modal {
            z-index: 999999;
        }
        
        .media-library-overlay {
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .media-library-content {
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .image-item {
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .image-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .image-item.selected {
            border: 2px solid #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .image-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        .image-item .image-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 0.5rem;
            font-size: 0.75rem;
        }
    </style>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Root & Body Global Dark Mode */
        .dark, .dark body { 
            background-color: #121212 !important; 
            color: rgba(255, 255, 255, 0.87) !important; 
        }

        /* Comprehensive Material Overrides */
        .dark .bg-white, 
        .dark .bg-stone-50, 
        .dark .bg-stone-100, 
        .dark .bg-gray-50, 
        .dark .bg-gray-100,
        .dark .bg-slate-50,
        .dark .bg-slate-100,
        .dark .bg-zinc-50,
        .dark .bg-zinc-100,
        .dark .bg-indigo-50,
        .dark .bg-amber-50,
        .dark .bg-violet-50,
        .dark .bg-rose-50,
        .dark .bg-cyan-50,
        .dark .bg-green-50,
        .dark .bg-blue-50,
        .dark .bg-purple-50,
        .dark .bg-emerald-50,
        .dark .bg-fuchsia-50,
        .dark .bg-pink-50,
        .dark .bg-teal-50 { 
            background-color: #1E1E1E !important; 
        }

        /* Nested Container Backgrounds */
        .dark .min-h-screen.bg-white,
        .dark .bg-[#F7F9FC],
        .dark .bg-[#F1F5F9] {
            background-color: #121212 !important;
        }

        /* Text Overrides - High Emphasis */
        .dark .text-stone-950, .dark .text-stone-900, .dark .text-stone-800, .dark .text-stone-700,
        .dark .text-gray-950, .dark .text-gray-900, .dark .text-gray-800, .dark .text-gray-700,
        .dark .text-slate-950, .dark .text-slate-900, .dark .text-slate-800, .dark .text-slate-700,
        .dark .text-zinc-950, .dark .text-zinc-900, .dark .text-zinc-800, .dark .text-zinc-700,
        .dark .text-black, .dark .text-white { 
            color: rgba(255, 255, 255, 0.87) !important; 
        }
        
        /* Text Overrides - Medium Emphasis */
        .dark .text-stone-600, .dark .text-stone-500, .dark .text-stone-400,
        .dark .text-gray-600, .dark .text-gray-500, .dark .text-gray-400,
        .dark .text-slate-600, .dark .text-slate-500, .dark .text-slate-400,
        .dark .text-zinc-600, .dark .text-zinc-500, .dark .text-zinc-400 { 
            color: rgba(255, 255, 255, 0.60) !important; 
        }

        /* Border Overrides */
        .dark .border-stone-200, .dark .border-stone-300,
        .dark .border-gray-200, .dark .border-gray-300,
        .dark .border-slate-200, .dark .border-slate-300,
        .dark .border-stone-100 { 
            border-color: #262626 !important; 
        }

        /* Table & Divider Overrides */
        .dark .divide-stone-200, .dark .divide-gray-200, .dark .divide-slate-200 {
            border-color: #262626 !important;
        }

        /* Form Controls */
        .dark input, .dark select, .dark textarea {
            background-color: #121212 !important;
            border-color: #333333 !important;
            color: rgba(255, 255, 255, 0.87) !important;
        }
        
        /* Material Accent Backgrounds (Desaturated) - Targeted for performance */
        .dark .bg-blue-50, .dark .bg-blue-100 { background-color: rgba(128, 203, 196, 0.12) !important; color: #80CBC4 !important; }
        .dark .bg-green-50, .dark .bg-green-100 { background-color: rgba(165, 214, 167, 0.12) !important; color: #A5D6A7 !important; }
        .dark .bg-purple-50, .dark .bg-purple-100 { background-color: rgba(244, 143, 177, 0.12) !important; color: #F48FB1 !important; }
        .dark .bg-yellow-50, .dark .bg-yellow-100 { background-color: rgba(255, 213, 79, 0.12) !important; color: #FFD54F !important; }
        .dark .bg-red-50, .dark .bg-red-100 { background-color: rgba(239, 154, 154, 0.12) !important; color: #EF9A9A !important; }
        
        /* Optimized Gradients - Killing all pale light-mode gradients */
        .dark .from-slate-50, .dark .from-slate-100, .dark .to-slate-50, .dark .to-slate-100,
        .dark .from-gray-50, .dark .from-gray-100, .dark .to-gray-50, .dark .to-gray-100,
        .dark .from-zinc-50, .dark .from-zinc-100, .dark .to-zinc-50, .dark .to-zinc-100,
        .dark .from-neutral-50, .dark .from-neutral-100, .dark .to-neutral-50, .dark .to-neutral-100,
        .dark .from-stone-50, .dark .from-stone-100, .dark .to-stone-50, .dark .to-stone-100,
        .dark .from-red-50, .dark .from-red-100, .dark .to-red-50, .dark .to-red-100,
        .dark .from-orange-50, .dark .from-orange-100, .dark .to-orange-50, .dark .to-orange-100,
        .dark .from-amber-50, .dark .from-amber-100, .dark .to-amber-50, .dark .to-amber-100,
        .dark .from-yellow-50, .dark .from-yellow-100, .dark .to-yellow-50, .dark .to-yellow-100,
        .dark .from-lime-50, .dark .from-lime-100, .dark .to-lime-50, .dark .to-lime-100,
        .dark .from-green-50, .dark .from-green-100, .dark .to-green-50, .dark .to-green-100,
        .dark .from-emerald-50, .dark .from-emerald-100, .dark .to-emerald-50, .dark .to-emerald-100,
        .dark .from-teal-50, .dark .from-teal-100, .dark .to-teal-50, .dark .to-teal-100,
        .dark .from-cyan-50, .dark .from-cyan-100, .dark .to-cyan-50, .dark .to-cyan-100,
        .dark .from-sky-50, .dark .from-sky-100, .dark .to-sky-50, .dark .to-sky-100,
        .dark .from-blue-50, .dark .from-blue-100, .dark .to-blue-50, .dark .to-blue-100,
        .dark .from-indigo-50, .dark .from-indigo-100, .dark .to-indigo-50, .dark .to-indigo-100,
        .dark .from-violet-50, .dark .from-violet-100, .dark .to-violet-50, .dark .to-violet-100,
        .dark .from-purple-50, .dark .from-purple-100, .dark .to-purple-50, .dark .to-purple-100,
        .dark .from-fuchsia-50, .dark .from-fuchsia-100, .dark .to-fuchsia-50, .dark .to-fuchsia-100,
        .dark .from-pink-50, .dark .from-pink-100, .dark .to-pink-50, .dark .to-pink-100,
        .dark .from-rose-50, .dark .from-rose-100, .dark .to-rose-50, .dark .to-rose-100,
        .dark .from-white, .dark .to-white {
            background-image: none !important;
            --tw-gradient-from: #1E1E1E !important;
            --tw-gradient-to: #1E1E1E !important;
        }

        /* High-Performance Hover Overrides - Fixing "White-out" on row hover */
        .dark .hover\:bg-stone-50:hover, 
        .dark .hover\:bg-gray-50:hover,
        .dark .hover\:bg-gray-100:hover,
        .dark .hover\:bg-slate-50:hover,
        .dark .hover\:bg-slate-100:hover,
        .dark .hover\:bg-zinc-50:hover,
        .dark .hover\:bg-zinc-100:hover,
        .dark .hover\:bg-white:hover {
            background-color: #2C2C2C !important;
        }

        /* Scrollbar Styling */
        .dark ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .dark ::-webkit-scrollbar-track {
            background: #121212;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #2C2C2C;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #3D3D3D;
        }

        /* ApexCharts Global Dark Mode Overrides */
        .dark .apexcharts-canvas {
            filter: drop-shadow(0 0 0 transparent) !important;
        }
        .dark .apexcharts-tooltip {
            background: #1E1E1E !important;
            border: 1px solid #333333 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5) !important;
            color: rgba(255, 255, 255, 0.87) !important;
        }
        .dark .apexcharts-tooltip-title {
            background: #2D2D2D !important;
            border-bottom: 1px solid #333333 !important;
            color: rgba(255, 255, 255, 0.87) !important;
            font-weight: 600 !important;
        }
        .dark .apexcharts-tooltip.apexcharts-theme-light {
            background: #1E1E1E !important;
            color: rgba(255, 255, 255, 0.87) !important;
        }
        .dark .apexcharts-text tspan, .dark .apexcharts-text {
            fill: rgba(255, 255, 255, 0.60) !important;
        }
        .dark .apexcharts-gridline {
            stroke: #262626 !important;
        }
        .dark .apexcharts-legend-text {
            color: rgba(255, 255, 255, 0.87) !important;
        }
        .dark .apexcharts-xaxis-label, .dark .apexcharts-yaxis-label {
            fill: rgba(255, 255, 255, 0.60) !important;
        }
        .dark .apexcharts-menu {
            background: #1E1E1E !important;
            border: 1px solid #333333 !important;
            color: rgba(255, 255, 255, 0.87) !important;
        }
        .dark .apexcharts-menu-item:hover {
            background: #2D2D2D !important;
        }
        .dark .apexcharts-xcrosshairs, .dark .apexcharts-ycrosshairs {
            stroke: #333333 !important;
        }
    </style>
</head>
<body class="bg-[#F7F9FC] dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F0F0F0] selection:bg-primary/10" x-data="{ 
    sidebarOpen: false, 
    darkMode: localStorage.getItem('darkMode') === 'true' || false,
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' || false,
    notifications: [],
    unreadCount: 0,
    echo: null
}" 
x-init="
    $watch('darkMode', val => {
        localStorage.setItem('darkMode', val);
        document.documentElement.classList.toggle('dark', val);
    });
    $watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val));
    
    // Auto-collapse on mobile
    if (window.innerWidth < 1024) {
        sidebarCollapsed = false;
    }
    
    // Store component reference for notification functions
    window.notificationComponent = $data;
    
    // Load notifications immediately after Alpine initializes
    $nextTick(() => {
        if (typeof loadNotifications === 'function') {
            loadNotifications();
        }
    });
    
    // Also try after a short delay to ensure everything is ready
    setTimeout(() => {
        if (typeof loadNotifications === 'function') {
            loadNotifications();
        }
    }, 1000);
    
    // Initialize real-time notifications
    if (typeof initializeRealtimeNotifications === 'function') {
        initializeRealtimeNotifications();
    }
    
    // Refresh notifications every 30 seconds
    setInterval(() => {
        if (typeof loadNotifications === 'function') {
            loadNotifications();
        }
    }, 30000);
" 
:class="{ 'dark': darkMode }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.partials.sidebar')
        
        <!-- Content Area -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            @include('admin.partials.header')
            
            <!-- Main Content -->
            <main>
                <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                    @include('admin.partials.alerts')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <!-- Mobile sidebar overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         class="fixed inset-0 z-9999 bg-black bg-opacity-50 lg:hidden"
         x-cloak></div>
    
    <!-- Permission Denied Modal -->
    @include('admin.partials.modal-permission-denied')
    
    @stack('scripts')
    
    <!-- Global Confirmation Modal -->
    @include('admin.partials.modal-confirm')
    
    <!-- Initialize Lucide Icons -->
    <script>
        // Simple icon initialization
        function initIcons() {
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        }
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', initIcons);
        
        // Re-initialize after Alpine loads
        document.addEventListener('alpine:initialized', () => {
            setTimeout(initIcons, 100);
        });
    </script>
    
    <!-- Preline UI Script -->
    <script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
    
    <!-- Quill Editor Initialization -->
    <script>
        // Initialize Quill Editor
        function initQuill() {
            if (typeof Quill !== 'undefined') {
                // Find all textareas with quill class
                const quillTextareas = document.querySelectorAll('textarea.quill-editor');
                
                quillTextareas.forEach(textarea => {
                    if (textarea.dataset.quillInitialized) return;
                    textarea.dataset.quillInitialized = 'true';
                    
                    // Create container for Quill
                    const quillContainer = document.createElement('div');
                    quillContainer.className = 'quill-container';
                    quillContainer.style.height = '400px';
                    
                    // Insert container after textarea
                    textarea.parentNode.insertBefore(quillContainer, textarea.nextSibling);
                    
                    // Hide original textarea
                    textarea.style.display = 'none';
                    
                    // Initialize Quill
                    const quill = new Quill(quillContainer, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'indent': '-1'}, { 'indent': '+1' }],
                                [{ 'align': [] }],
                                ['link', 'image', 'video'],
                                ['blockquote', 'code-block'],
                                ['clean']
                            ]
                        },
                        placeholder: 'Start writing...'
                    });
                    
                    // Set initial content
                    if (textarea.value) {
                        quill.root.innerHTML = textarea.value;
                    }
                    
                    // Update textarea on content change
                    quill.on('text-change', function() {
                        textarea.value = quill.root.innerHTML;
                    });
                    
                    // Custom image handler
                    const toolbar = quill.getModule('toolbar');
                    toolbar.addHandler('image', function() {
                        const input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/jpeg,image/png,image/jpg,image/webp');
                        input.click();

                        input.onchange = () => {
                            const file = input.files[0];
                            if (/^image\//.test(file.type)) {
                                const formData = new FormData();
                                formData.append('images[]', file);
                                formData.append('type', 'cms');
                                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                                // Show loading state if needed
                                
                                fetch("{{ admin_route('images.upload') }}", {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success && result.images && result.images.length > 0) {
                                        const imageUrl = result.images[0].url;
                                        const range = quill.getSelection();
                                        if (range) {
                                            quill.insertEmbed(range.index, 'image', imageUrl);
                                            quill.setSelection(range.index + 1);
                                        }
                                    } else {
                                        alert('Upload failed: ' + (result.message || 'Unknown error'));
                                    }
                                })
                                .catch(error => {
                                    console.error('Error uploading image:', error);
                                    alert('Error uploading image');
                                });
                            }
                        };
                    });
                    
                    // Mark as initialized
                    textarea.dataset.quillInitialized = 'true';
                    textarea.quillInstance = quill;
                });
            }
        }
        
        // Initialize Quill when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initQuill();
        });
        
        // Re-initialize Quill after Alpine updates
        document.addEventListener('alpine:initialized', () => {
            setTimeout(() => {
                initQuill();
            }, 100);
        });
        
        // Initialize Quill for dynamically loaded content
        function reinitQuill() {
            if (typeof Quill !== 'undefined') {
                initQuill();
            }
        }
        
        // Make reinitQuill globally available
        window.reinitQuill = reinitQuill;
        
        // Media Library Functions
        function openMediaLibrary(callback) {
            // Create modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 media-library-overlay media-library-modal flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white dark:bg-boxdark rounded-lg shadow-xl max-w-4xl w-full mx-4 media-library-content">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Select Image</h3>
                            <button onclick="closeMediaLibrary()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex gap-2">
                                <button onclick="uploadNewImage()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Upload New Image
                                </button>
                                <input type="file" id="imageUpload" accept="image/*" class="hidden" onchange="handleImageUpload(this)">
                            </div>
                        </div>
                        
                        <div id="imageGrid" class="image-grid">
                            <!-- Images will be loaded here -->
                        </div>
                        
                        <div class="mt-4 flex justify-end gap-2">
                            <button onclick="closeMediaLibrary()" class="px-4 py-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                                Cancel
                            </button>
                            <button id="selectImageBtn" onclick="selectImage()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>
                                Select Image
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Store callback
            modal.callback = callback;
            window.currentMediaLibrary = modal;
            
            // Load images
            loadMediaLibraryImages();
        }
        
        function closeMediaLibrary() {
            if (window.currentMediaLibrary) {
                window.currentMediaLibrary.remove();
                window.currentMediaLibrary = null;
            }
        }
        
        function loadMediaLibraryImages() {
            const imageGrid = document.getElementById('imageGrid');
            if (!imageGrid) return;
            
            // Fetch CMS images
            fetch('/admin/api/cms-images')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        imageGrid.innerHTML = '';
                        data.images.forEach(image => {
                            const imageItem = document.createElement('div');
                            imageItem.className = 'image-item';
                            imageItem.dataset.imageUrl = image.url;
                            imageItem.innerHTML = `
                                <img src="${image.url}" alt="${image.filename}">
                                <div class="image-info">
                                    <div class="truncate">${image.filename}</div>
                                    <div>${(image.size / 1024 / 1024).toFixed(2)} MB</div>
                                </div>
                            `;
                            
                            imageItem.addEventListener('click', function() {
                                // Remove previous selection
                                document.querySelectorAll('.image-item.selected').forEach(item => {
                                    item.classList.remove('selected');
                                });
                                
                                // Select this image
                                this.classList.add('selected');
                                document.getElementById('selectImageBtn').disabled = false;
                            });
                            
                            imageGrid.appendChild(imageItem);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading images:', error);
                    imageGrid.innerHTML = '<p class="text-gray-500">Error loading images</p>';
                });
        }
        
        function selectImage() {
            const selectedItem = document.querySelector('.image-item.selected');
            if (selectedItem && window.currentMediaLibrary && window.currentMediaLibrary.callback) {
                const imageUrl = selectedItem.dataset.imageUrl;
                window.currentMediaLibrary.callback(imageUrl);
                closeMediaLibrary();
            }
        }
        
        function uploadNewImage() {
            document.getElementById('imageUpload').click();
        }
        
        function handleImageUpload(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('images[]', input.files[0]);
                formData.append('type', 'cms');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        
                        fetch("{{ admin_route('images.upload') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                        loadMediaLibraryImages(); // Reload images
                            } else {
                        alert('Upload failed: ' + result.message);
                            }
                        })
                        .catch(error => {
                    alert('Upload failed: ' + error.message);
                });
            }
        }
        
        // Make functions globally available
        window.openMediaLibrary = openMediaLibrary;
        window.closeMediaLibrary = closeMediaLibrary;
        window.selectImage = selectImage;
        window.uploadNewImage = uploadNewImage;
        window.handleImageUpload = handleImageUpload;
        
        // Store reference to Alpine component
        let notificationComponent = null;
        
        // Load notifications from database
        function loadNotifications() {
            // Get the Alpine component from the body element
            let component = null;
            const bodyEl = document.body;
            
            if (bodyEl && bodyEl.__x) {
                component = bodyEl.__x.$data;
            } else if (typeof Alpine !== 'undefined' && Alpine.$data) {
                try {
                    component = Alpine.$data(document.body);
                } catch (e) {
                    // Ignore errors
                }
            }
            
            // If component not found, try again after a short delay
            if (!component) {
                setTimeout(() => {
                    loadNotifications();
                }, 100);
                return;
            }
            
            // Store reference for other functions
            notificationComponent = component;
            
            fetch('{{ admin_route("notifications.api") }}?limit=10', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Notifications API response:', data);
                if (data.success && component) {
                    // Map notifications with data field
                    const mappedNotifications = (data.notifications || []).map(n => ({
                        id: n.id,
                        title: n.title || 'Notification',
                        message: n.message || '',
                        type: n.type || 'info',
                        priority: 'medium',
                        timestamp: new Date(n.timestamp),
                        read: n.read || false,
                        data: n.data || {}
                    }));
                    
                    const unreadNotifications = mappedNotifications.filter(n => !n.read);
                    console.log('Mapped notifications:', mappedNotifications);
                    console.log('Unread notifications count:', unreadNotifications.length);
                    console.log('Unread notifications:', unreadNotifications);
                    console.log('Component before update:', component);
                    
                    // Update notifications array directly - use Alpine's reactivity
                    if (component.notifications) {
                        // Clear and add new notifications - this triggers Alpine reactivity
                        component.notifications.length = 0;
                        component.notifications.push(...mappedNotifications);
                    } else {
                        component.notifications = mappedNotifications;
                    }
                    
                    // Update unread count
                    component.unreadCount = data.unread_count || 0;
                    
                    console.log('Updated component notifications:', component.notifications);
                    console.log('Updated unread count:', component.unreadCount);
                    console.log('Unread after update:', component.notifications.filter(n => !n.read).length);
                    console.log('Component after update:', component);
                    
                    // Dispatch custom event to notify dropdown
                    if (typeof window !== 'undefined') {
                        window.dispatchEvent(new CustomEvent('notifications-updated', {
                            detail: { notifications: mappedNotifications, unreadCount: data.unread_count || 0 }
                        }));
                    }
                    
                    // Force Alpine to update
                    if (typeof Alpine !== 'undefined') {
                        // Trigger reactivity
                        if (component.$nextTick) {
                            component.$nextTick(() => {
                                // Reinitialize Lucide icons for new notifications
                                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                                    lucide.createIcons();
                                }
                            });
                        }
                    }
                } else {
                    console.error('Failed to load notifications:', data);
                    if (component) {
                        console.error('Component state:', component);
                    }
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
        }
        
        // Make loadNotifications available globally
        window.loadNotifications = loadNotifications;
        
        // Store component reference when Alpine initializes
        document.addEventListener('alpine:init', () => {
            setTimeout(() => {
                loadNotifications();
            }, 500);
        });
        
        // Also try to load when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    if (typeof loadNotifications === 'function') {
                        loadNotifications();
                    }
                }, 1500);
            });
        } else {
            // DOM is already ready
            setTimeout(() => {
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            }, 1500);
        }
        
        // Real-time Notifications
        function initializeRealtimeNotifications() {
            // Check if Pusher and Echo are available
            if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') {
                return;
            }
            
            // Initialize Echo with Pusher
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ env('PUSHER_APP_KEY', 'your-pusher-key') }}',
                cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }
            });
            
            // Listen for admin notifications
            window.Echo.private('admin.notifications')
                .listen('.system.notification', (e) => {
                    addNotification(e);
                })
                .listen('.order.created', (e) => {
                    addNotification(e);
                })
                .listen('.order.status.changed', (e) => {
                    addNotification(e);
                })
                .listen('.inventory.low.stock', (e) => {
                    addNotification(e);
                })
                .listen('.review.created', (e) => {
                    addNotification(e);
                })
                .listen('.refund.request.created', (e) => {
                    addNotification(e);
                });
        }
        
        function addNotification(data) {
            console.log('addNotification called with data:', data);
            // When a real-time notification comes in, reload from database
            // This ensures we get the actual database ID and all details
            if (typeof loadNotifications === 'function') {
                setTimeout(() => {
                    console.log('Refreshing notifications after real-time event');
                    loadNotifications();
                }, 1500); // Increased delay to ensure notification is saved to DB
            }
            
            // Also add to local array for immediate display
            const notification = {
                id: Date.now(),
                title: data.title || getNotificationTitle(data.type),
                message: data.message,
                type: data.type || 'info',
                priority: data.priority || 'medium',
                timestamp: new Date(),
                read: false
            };
            
            // Add to notifications array
            if (notificationComponent) {
                notificationComponent.notifications.unshift(notification);
                notificationComponent.unreadCount++;
            }
            
            // Show browser notification if permission granted
            if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                new Notification(notification.title, {
                    body: notification.message,
                    icon: '/favicon.ico',
                    tag: notification.id
                });
            }
        }
        
        function getNotificationTitle(type) {
            const titles = {
                'order': 'New Order',
                'order_status': 'Order Status Update',
                'inventory': 'Low Stock Alert',
                'review': 'New Review',
                'info': 'System Notification'
            };
            return titles[type] || 'Notification';
        }
        
        function markNotificationAsRead(notificationId) {
            if (!notificationComponent) {
                const bodyEl = document.body;
                if (bodyEl && bodyEl.__x) {
                    notificationComponent = bodyEl.__x.$data;
                }
            }
            
            if (!notificationComponent) return;
            
            const notification = notificationComponent.notifications.find(n => n.id === notificationId);
            if (notification && !notification.read) {
                // Mark as read on server first
                fetch(`{{ admin_route("notifications.mark-read", ["id" => ":id"]) }}`.replace(':id', notificationId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove notification from dropdown when marked as read
                        const index = notificationComponent.notifications.findIndex(n => n.id === notificationId);
                        if (index !== -1) {
                            notificationComponent.notifications.splice(index, 1);
                        }
                        
                        // Update unread count
                        notificationComponent.unreadCount = Math.max(0, notificationComponent.unreadCount - 1);
                        
                        // Dispatch event to update dropdown
                        window.dispatchEvent(new CustomEvent('notifications-updated', {
                            detail: { 
                                notifications: notificationComponent.notifications, 
                                unreadCount: notificationComponent.unreadCount 
                            }
                        }));
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            }
        }
        
        function markAllAsRead() {
            if (!notificationComponent) {
                const bodyEl = document.body;
                if (bodyEl && bodyEl.__x) {
                    notificationComponent = bodyEl.__x.$data;
                }
            }
            
            if (!notificationComponent) return;
            
            // Mark all as read on server
            fetch('{{ admin_route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear all notifications from dropdown
                    notificationComponent.notifications = [];
                    notificationComponent.unreadCount = 0;
                    
                    // Dispatch event to update dropdown
                    window.dispatchEvent(new CustomEvent('notifications-updated', {
                        detail: { 
                            notifications: [], 
                            unreadCount: 0 
                        }
                    }));
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
                // Reload notifications on error
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            });
        }
        
        function formatTime(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const diffInSeconds = Math.floor((now - time) / 1000);
            
            if (diffInSeconds < 60) {
                return 'Just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            } else {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} day${days > 1 ? 's' : ''} ago`;
            }
        }
        
        function getNotificationIcon(type) {
            const icons = {
                'order': 'O',
                'order_status': 'S',
                'inventory': 'L',
                'message': 'M',
                'customer': 'C',
                'review': 'R',
                'refund': 'R',
                'info': 'I'
            };
            return icons[type] || 'N';
        }
        
        function getNotificationTitle(type) {
            const titles = {
                'order': 'New Order',
                'order_status': 'Order Status Update',
                'inventory': 'Low Stock Alert',
                'message': 'Customer Message',
                'customer': 'New Customer',
                'review': 'Product Review',
                'refund': 'Refund Request',
                'info': 'System Notification'
            };
            return titles[type] || 'Notification';
        }
        
        function getNotificationContent(notification) {
            const data = notification.data || {};
            const type = notification.type;
            
            switch(type) {
                case 'order':
                    // New Order - must include ORD number
                    if (data.order_number) {
                        const customerName = data.customer_name || 'Guest';
                        return `Order #${data.order_number} from ${customerName}`;
                    }
                    return `New order received`;
                    
                case 'order_status':
                    // Order Status Update - must include ORD number saying order status has been updated
                    if (data.order_number && data.new_status) {
                        return `Order #${data.order_number} status updated to ${data.new_status}`;
                    } else if (data.order_number) {
                        return `Order #${data.order_number} status has been updated`;
                    }
                    return `Order status has been updated`;
                    
                case 'message':
                    // Customer Message - new message from customer's name
                    if (data.customer_name) {
                        return `New message from ${data.customer_name}`;
                    }
                    return `New customer message received`;
                    
                case 'inventory':
                    // Low Stock Alert - product's name saying low stock
                    if (data.product_name) {
                        return `${data.product_name} is low on stock`;
                    }
                    return `Product is running low on stock`;
                    
                case 'customer':
                    // New Customer - idk (I'll use a reasonable default)
                    if (data.customer_name) {
                        return `New customer registered: ${data.customer_name}`;
                    }
                    return `New customer registration`;
                    
                case 'review':
                    // Product Review - new review on product's name
                    if (data.product_name) {
                        return `New review on ${data.product_name}`;
                    }
                    return `New product review received`;
                    
                case 'refund':
                    // Refund Request - request for refund with RMA number
                    if (data.rma_number) {
                        return `Refund request for RMA #${data.rma_number}`;
                    } else if (data.order_number) {
                        return `Refund request for Order #${data.order_number}`;
                    }
                    return `New refund request received`;
                    
                default:
                    return notification.message || 'New notification';
            }
        }
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
        
        // Make functions globally available
        window.addNotification = addNotification;
        window.markNotificationAsRead = markNotificationAsRead;
        window.markAllAsRead = markAllAsRead;
        window.formatTime = formatTime;
        window.getNotificationIcon = getNotificationIcon;
        window.getNotificationTitle = getNotificationTitle;
        window.getNotificationContent = getNotificationContent;
    </script>
</body>
</html>