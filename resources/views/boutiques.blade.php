@extends('layouts.app')

@section('title', 'Boutiques & Appointments — Éclore Maison')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .boutique-hero {
        height: 90vh;
        position: relative;
        overflow: hidden;
        background: #0a0a0a;
    }
    .hero-overlay {
        background: linear-gradient(to right, rgba(10,10,10,0.95) 0%, rgba(10,10,10,0.4) 50%, rgba(10,10,10,0) 100%);
    }
    .map-container {
        height: 600px;
        width: 100%;
        z-index: 10;
        background: #f8f8f8;
    }
    .boutique-card {
        border-bottom: 1px solid #eee;
        padding: 3rem 0;
        transition: all 0.4s ease;
    }
    .boutique-card:hover {
        padding-left: 2rem;
        background: #fafafa;
    }
    .appointment-form {
        background: #fff;
        padding: 4rem;
        box-shadow: 0 50px 100px rgba(0,0,0,0.02);
    }
    .minimal-input {
        width: 100%;
        border-bottom: 1px solid #ddd;
        padding: 1rem 0;
        font-family: 'Azeret Mono', monospace;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        outline: none;
        transition: border-color 0.3s ease;
    }
    .minimal-input:focus {
        border-color: #B6965D;
    }
    .region-dropdown {
        background: #fff;
        padding: 1.5rem;
        border: 1px solid #f0f0f0;
        cursor: pointer;
        width: 240px;
        transition: all 0.3s ease;
        position: relative;
    }
    .region-dropdown:hover {
        border-color: #B6965D;
    }
    .region-list {
        position: absolute;
        bottom: 100%;
        left: -1px;
        width: calc(100% + 2px);
        background: #fff;
        border: 1px solid #f0f0f0;
        visibility: hidden;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
        z-index: 50;
    }
    .region-dropdown.active .region-list {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }
    .region-item {
        padding: 1rem 1.5rem;
        font-family: 'Playfair Display', serif;
        font-size: 1rem;
        transition: all 0.2s ease;
    }
    .region-item:hover {
        background: #fcfcfc;
        color: #B6965D;
    }
    .leaflet-container {
        font-family: 'Azeret Mono', sans-serif !important;
    }
    .map-container .leaflet-tile {
        filter: grayscale(100%) brightness(102%) contrast(90%);
    }
    .custom-marker {
        width: 12px;
        height: 12px;
        background: #B6965D;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(182, 150, 93, 0.5);
    }
</style>
@endpush

@section('content')
<main>
    <!-- Hero Section -->
    <section class="boutique-hero flex items-center">
        <div class="absolute inset-0 z-0 h-full w-full">
            <img src="{{ asset('frontend/assets/boutique_exterior_night_noir_1774358435137.png') }}" class="w-full h-full object-cover grayscale-[20%]" alt="Éclore Flagship">
            <div class="absolute inset-0 hero-overlay"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-transparent to-transparent"></div>
        </div>
        <div class="relative z-10 w-full container mx-auto px-6 lg:px-24">
            <div class="max-w-3xl">
                <nav class="mb-12" data-aos="fade-right">
                    <span class="font-azeret text-[11px] tracking-[0.5em] text-[#B6965D] uppercase font-bold">The Global Presence</span>
                </nav>
                <h1 class="font-playfair text-7xl md:text-9xl text-white leading-[0.8] mb-12" data-aos="fade-up">World Of Éclore</h1>
                <p class="font-azeret text-[10px] tracking-[0.4em] text-gray-400 uppercase max-w-lg leading-loose" data-aos="fade-up" data-aos-delay="100">
                    Artistry knows no borders. Experience the Maison first-hand within our flagship residences across the world's most distinguished addresses.
                </p>
            </div>
        </div>
    </section>

    <!-- Map & Locations -->
    <section class="py-32 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-stretch">
                <!-- Location List -->
                <div class="flex flex-col justify-center" data-aos="fade-right">
                    <h2 class="font-playfair text-4xl mb-12">Global Presence</h2>
                    
                    <div class="boutique-card group cursor-pointer" onclick="mapTo(48.867, 2.329)">
                        <span class="font-azeret text-[10px] text-[#B6965D] tracking-[0.4em] block mb-2">FLAGSHIP</span>
                        <h3 class="font-playfair text-3xl mb-4 text-gray-900">Paris, Vendôme</h3>
                        <p class="font-azeret text-[11px] text-gray-400 tracking-widest leading-loose uppercase">7 Place Vendôme, 75001 Paris, France</p>
                    </div>

                    <div class="boutique-card group cursor-pointer" onclick="mapTo(51.512, -0.142)">
                        <span class="font-azeret text-[10px] text-[#B6965D] tracking-[0.4em] block mb-2">BOUTIQUE</span>
                        <h3 class="font-playfair text-3xl mb-4 text-gray-900">London, New Bond</h3>
                        <p class="font-azeret text-[11px] text-gray-400 tracking-widest leading-loose uppercase">174 New Bond St, London W1S 4RG, UK</p>
                    </div>

                    <div class="boutique-card group cursor-pointer" onclick="mapTo(40.762, -73.974)">
                        <span class="font-azeret text-[10px] text-[#B6965D] tracking-[0.4em] block mb-2">SALON</span>
                        <h3 class="font-playfair text-3xl mb-4 text-gray-900">New York, 5th Ave</h3>
                        <p class="font-azeret text-[11px] text-gray-400 tracking-widest leading-loose uppercase">712 5th Ave, New York, NY 10019, USA</p>
                    </div>

                    <div class="boutique-card group cursor-pointer border-none" onclick="mapTo(35.671, 139.765)">
                        <span class="font-azeret text-[10px] text-[#B6965D] tracking-[0.4em] block mb-2">BOUTIQUE</span>
                        <h3 class="font-playfair text-3xl mb-4 text-gray-900">Tokyo, Ginza</h3>
                        <p class="font-azeret text-[11px] text-gray-400 tracking-widest leading-loose uppercase">5 Chome-2-1 Ginza, Chuo City, Tokyo, Japan</p>
                    </div>
                </div>

                <!-- Interactive Map -->
                <div class="relative h-[700px] lg:h-auto" data-aos="fade-left">
                    <div id="map" class="map-container h-full shadow-2xl"></div>
                    <div class="absolute bottom-8 right-8 z-30">
                        <div class="region-dropdown" id="regionSelector">
                            <p class="font-azeret text-[9px] tracking-widest text-[#999] mb-1.5 uppercase">Switch Region</p>
                            <div class="flex items-center justify-between">
                                <span id="current-location-text" class="font-playfair text-xl text-gray-900">Paris, France</span>
                                <i data-lucide="chevron-up" class="w-4 h-4 text-[#B6965D]"></i>
                            </div>
                            
                            <!-- Dropdown List -->
                            <div class="region-list shadow-2xl" id="regionList">
                                <div class="region-item" onclick="selectRegion('Paris, France', [48.867, 2.329])">Paris, France</div>
                                <div class="region-item" onclick="selectRegion('London, UK', [51.512, -0.142])">London, UK</div>
                                <div class="region-item" onclick="selectRegion('New York, USA', [40.762, -73.974])">New York, USA</div>
                                <div class="region-item" onclick="selectRegion('Tokyo, Japan', [35.671, 139.765])">Tokyo, Japan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Appointment Section -->
    <section class="py-28 bg-[#FAF9F6]">
        <div class="container mx-auto px-12">
            <div class="mx-auto flex flex-col md:flex-row items-center gap-20">
                <div class="md:w-1/2" data-aos="fade-right">
                    <h2 class="font-playfair text-5xl md:text-7xl text-gray-900 mb-10">Private Appointments</h2>
                    <p class="font-azeret text-xs tracking-widest text-gray-400 uppercase leading-loose mb-12">
                        Bespoke care for your most cherished acquisitions. Schedule a personal viewing, fitting, or restoration consultation with our master artisans.
                    </p>
                    <ul class="space-y-6">
                        <li class="flex items-center gap-6 font-azeret text-[10px] tracking-[0.3em] text-gray-500 uppercase">
                            <span class="w-1.5 h-1.5 bg-[#B6965D] rounded-full"></span>
                            Complimentary Accessory Fitting
                        </li>
                        <li class="flex items-center gap-6 font-azeret text-[10px] tracking-[0.3em] text-gray-500 uppercase">
                            <span class="w-1.5 h-1.5 bg-[#B6965D] rounded-full"></span>
                            One-on-One Collection Preview
                        </li>
                        <li class="flex items-center gap-6 font-azeret text-[10px] tracking-[0.3em] text-gray-500 uppercase">
                            <span class="w-1.5 h-1.5 bg-[#B6965D] rounded-full"></span>
                            Restoration & Cleaning Services
                        </li>
                    </ul>
                </div>
                
                <div class="md:w-3/5" data-aos="fade-left">
                    <div class="appointment-form">
                        @if(session('success'))
                            <div class="mb-8 p-6 bg-[#C5B391]/10 border border-[#C5B391]/20 text-[#C5B391] font-azeret text-[10px] tracking-widest uppercase text-center leading-loose">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                                <div>
                                    <label class="font-azeret text-[9px] tracking-[0.4em] text-gray-300 uppercase">First Name</label>
                                    <input type="text" name="first_name" placeholder="GIVEN NAME" class="minimal-input" required>
                                </div>
                                <div>
                                    <label class="font-azeret text-[9px] tracking-[0.4em] text-gray-300 uppercase">Last Name</label>
                                    <input type="text" name="last_name" placeholder="SURNAME" class="minimal-input" required>
                                </div>
                            </div>
                            <div class="mb-12">
                                <label class="font-azeret text-[9px] tracking-[0.4em] text-gray-300 uppercase">Email Address</label>
                                <input type="email" name="email" placeholder="YOUR@EMAIL.COM" class="minimal-input" required>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                                <div>
                                    <label class="font-azeret text-[9px] tracking-[0.4em] text-gray-300 uppercase">Preferred Location</label>
                                    <select name="location" class="minimal-input" required>
                                        <option value="PARIS, VENDÔME">PARIS, VENDÔME</option>
                                        <option value="LONDON, NEW BOND">LONDON, NEW BOND</option>
                                        <option value="NEW YORK, 5TH AVE">NEW YORK, 5TH AVE</option>
                                        <option value="TOKYO, GINZA">TOKYO, GINZA</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-azeret text-[9px] tracking-[0.4em] text-gray-300 uppercase">Service Type</label>
                                    <select name="service_type" class="minimal-input" required>
                                        <option value="ACCESSORY FITTING">ACCESSORY FITTING</option>
                                        <option value="PRIVATE VIEWING">PRIVATE VIEWING</option>
                                        <option value="RESTORATION CONSULT">RESTORATION CONSULT</option>
                                        <option value="VIP CONCEIRGE">VIP CONCEIRGE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-16">
                                <label class="font-azeret text-[9px] tracking-[0.4em] text-gray-300 uppercase">Preferred Date & Time</label>
                                <div class="flex gap-4">
                                    <input type="date" name="appointment_date" class="minimal-input" required>
                                    <input type="time" name="appointment_time" class="minimal-input" required>
                                </div>
                            </div>
                            <div class="mb-12">
                                <label class="font-azeret text-[9px] tracking-[0.4em] text-gray-300 uppercase">Special Requests (Optional)</label>
                                <textarea name="notes" class="minimal-input h-24 pt-4 resize-none" placeholder="ADDITIONAL DETAILS..."></textarea>
                            </div>
                            <button type="submit" class="w-full bg-black text-white py-6 text-[11px] tracking-[0.4em] uppercase font-bold hover:bg-[#B6965D] transition-all flex items-center justify-center gap-4">
                                <span class="w-2 h-2 bg-white rounded-full"></span>
                                Confirm Reservation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map;
    const locations = {
        'Paris, France': [48.867, 2.329],
        'London, UK': [51.512, -0.142],
        'New York, USA': [40.762, -73.974],
        'Tokyo, Japan': [35.671, 139.765]
    };

    document.addEventListener('DOMContentLoaded', function() {
        map = L.map('map', {
            center: [48.867, 2.329],
            zoom: 13,
            scrollWheelZoom: false,
            zoomControl: false
        });

        @php
            $cartoKey = config('services.carto.api_key');
        @endphp
        @if(!empty($cartoKey))
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key={{ $cartoKey }}', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            maxZoom: 19
        }).addTo(map);
        @else
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        @endif

        const customIcon = L.divIcon({
            className: 'custom-marker',
            iconSize: [12, 12]
        });

        Object.entries(locations).forEach(([name, coords]) => {
            L.marker(coords, { icon: customIcon }).addTo(map)
                .bindPopup(`<span class="font-azeret text-[9px] tracking-[0.2em] uppercase">${name}</span>`);
        });

        // Click outside to close dropdown
        document.addEventListener('click', function(e) {
            const selector = document.getElementById('regionSelector');
            if (!selector.contains(e.target)) {
                selector.classList.remove('active');
            }
        });

        document.getElementById('regionSelector').addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
        });
    });

    function selectRegion(name, coords) {
        mapTo(coords[0], coords[1]);
        document.getElementById('current-location-text').innerText = name;
        document.getElementById('regionSelector').classList.remove('active');
    }

    function mapTo(lat, lng) {
        map.flyTo([lat, lng], 14, {
            duration: 2.5,
            easeLinearity: 0.25
        });
        
        // Update text manually if called from sidebar
        const matched = Object.keys(locations).find(key => locations[key][0] === lat);
        if(matched) document.getElementById('current-location-text').innerText = matched;
    }
</script>
@endpush
