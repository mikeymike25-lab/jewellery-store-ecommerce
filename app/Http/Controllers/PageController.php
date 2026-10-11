<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    /**
     * Display the Privacy Policy page.
     */
    public function privacyPolicy()
    {
        return view('privacy-policy');
    }

    /**
     * Display the Terms of Service page.
     */
    public function termsOfService()
    {
        return view('terms-of-service');
    }

    /**
     * Display the Conditions of Sale page.
     */
    public function conditionsOfSale()
    {
        return view('conditions-of-sale');
    }

    /**
     * Display the Cookie Policy page.
     */
    public function cookiePolicy()
    {
        return view('cookie-policy');
    }

    /**
     * Display the Cookie Center page.
     */
    public function cookieCenter()
    {
        return view('cookie-center');
    }

    /**
     * Display the About page.
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Display the Collections page.
     */
    public function collections()
    {
        $heroProducts = \App\Models\Product::where('is_active', true)
            ->latest()
            ->take(6)
            ->get();

        $bestsellerProducts = \App\Models\Product::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->take(4)
            ->get();

        // Get a specific product for the Talisman section or a default one
        $talismanProduct = \App\Models\Product::where('is_active', true)
            ->where('slug', 'eternal-harmony-ring')
            ->first() ?: \App\Models\Product::where('is_active', true)->first();

        return view('collections', compact('heroProducts', 'bestsellerProducts', 'talismanProduct'));
    }

    /**
     * Display the Newsroom page.
     */
    public function newsroom(\Illuminate\Http\Request $request)
    {
        $currentCategory = $request->get('category', 'Latest');

        $newsTypes = ['press_release', 'exhibition', 'insight', 'blog', 'news', 'update'];

        $query = \App\Models\CmsPage::published()
            ->whereIn('type', $newsTypes)
            ->orderBy('published_at', 'desc');

        $typeMap = [
            'Press Releases' => ['press_release'],
            'Exhibitions' => ['exhibition'],
            'Insights' => ['insight', 'blog', 'news', 'update'],
        ];

        if ($currentCategory !== 'Latest' && isset($typeMap[$currentCategory])) {
            $query->whereIn('type', $typeMap[$currentCategory]);
        }

        // Featured story (only for 'Latest' or if no specific category is selected, or just the top one for the category)
        $featuredStory = (clone $query)->where('is_featured', true)->first() ?: (clone $query)->first();

        // Recent Highlights (exclude featured)
        $highlights = (clone $query)->where('id', '!=', $featuredStory?->id)->limit(3)->get();

        // Secondary Feed with Pagination (6 per page)
        $stories = $query->where('id', '!=', $featuredStory?->id)
            ->paginate(6)
            ->withQueryString();

        // If AJAX, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $stories->items(),
                'meta' => [
                    'current_page' => $stories->currentPage(),
                    'last_page' => $stories->lastPage(),
                    'per_page' => $stories->perPage(),
                    'total' => $stories->total(),
                    'current_category' => $currentCategory,
                ],
            ]);
        }

        return view('newsroom', compact('featuredStory', 'highlights', 'stories', 'currentCategory'));
    }

    /**
     * Display the VIP Club page.
     */
    public function vipClub()
    {
        return view('vip-club');
    }

    /**
     * Display the Corporate Responsibility page.
     */
    public function corporateResponsibility()
    {
        return view('corporate-responsibility');
    }

    /**
     * Display the Help page.
     */
    public function help()
    {
        return view('help');
    }

    /**
     * Display the Accessibility page.
     */
    public function accessibility()
    {
        return view('accessibility');
    }

    /**
     * Display the Orders & Payments page.
     */
    public function ordersPayments()
    {
        return view('orders-payments');
    }

    /**
     * Display the Track Your Order page.
     */
    public function trackOrder()
    {
        return view('track-order');
    }

    /**
     * Display the Contact Us page.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Display the Boutiques & Appointments page.
     */
    public function boutiques()
    {
        return view('boutiques');
    }

    /**
     * Display the Care & Maintenance page.
     */
    public function care()
    {
        return view('care');
    }

    /**
     * REST API: List Boutiques & Salons with coordinates and hours.
     */
    public function getBoutiquesApi()
    {
        $boutiques = [
            [
                'id' => 1,
                'name' => 'Paris, Vendôme',
                'city' => 'Paris',
                'country' => 'France',
                'type' => 'FLAGSHIP',
                'address' => '7 Place Vendôme, 75001 Paris, France',
                'phone' => '+33 1 42 68 55 00',
                'hours' => 'Mon - Sat: 10:30 - 19:30',
                'lat' => 48.867,
                'lng' => 2.329,
                'image' => '/assets/boutique_exterior_night_noir_1774358435137.png',
                'services' => ['High Jewellery Viewing', 'Bespoke Commission', 'Bridal Salon', 'Valuation & Restoration'],
            ],
            [
                'id' => 2,
                'name' => 'London, New Bond',
                'city' => 'London',
                'country' => 'United Kingdom',
                'type' => 'BOUTIQUE',
                'address' => '174 New Bond St, London W1S 4RG, United Kingdom',
                'phone' => '+44 20 7499 8888',
                'hours' => 'Mon - Sat: 10:00 - 19:00',
                'lat' => 51.512,
                'lng' => -0.142,
                'image' => '/assets/boutique_exterior_night_noir_1774358435137.png',
                'services' => ['Private Salon Viewing', 'Bespoke Atelier', 'Bridal Consultation'],
            ],
            [
                'id' => 3,
                'name' => 'New York, 5th Ave',
                'city' => 'New York',
                'country' => 'United States',
                'type' => 'SALON',
                'address' => '712 5th Ave, New York, NY 10019, United States',
                'phone' => '+1 212 582 5890',
                'hours' => 'Mon - Sat: 11:00 - 19:00',
                'lat' => 40.762,
                'lng' => -73.974,
                'image' => '/assets/boutique_exterior_night_noir_1774358435137.png',
                'services' => ['VIP Suite', 'Haute Joaillerie Viewing', 'Personal Styling'],
            ],
            [
                'id' => 4,
                'name' => 'Tokyo, Ginza',
                'city' => 'Tokyo',
                'country' => 'Japan',
                'type' => 'BOUTIQUE',
                'address' => '5 Chome-2-1 Ginza, Chuo City, Tokyo 104-0061, Japan',
                'phone' => '+81 3 3567 1100',
                'hours' => 'Mon - Sun: 11:00 - 20:00',
                'lat' => 35.671,
                'lng' => 139.765,
                'image' => '/assets/boutique_exterior_night_noir_1774358435137.png',
                'services' => ['Bespoke Consultation', 'Rare Gemstone Viewing', 'Tea Ceremony Salon'],
            ],
            [
                'id' => 5,
                'name' => 'Manila Atelier, Makati',
                'city' => 'Manila',
                'country' => 'Philippines',
                'type' => 'FLAGSHIP ATELIER',
                'address' => 'Greenbelt 5, Legazpi St, Makati City, Metro Manila, Philippines',
                'phone' => '+63 2 8812 3456',
                'hours' => 'Mon - Sun: 11:00 - 21:00',
                'lat' => 14.552,
                'lng' => 121.021,
                'image' => '/assets/boutique_exterior_night_noir_1774358435137.png',
                'services' => ['Master Goldsmith Consultation', 'Custom Engagement Solitaires', 'Official BIR Appraisal'],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $boutiques,
        ]);
    }

    /**
     * REST API: Get Editorial Stories from CMS with Category Filters.
     */
    public function getStoriesApi(\Illuminate\Http\Request $request)
    {
        $category = $request->get('category', 'All');
        $query = \App\Models\CmsPage::published()
            ->whereIn('type', ['blog', 'news', 'press_release', 'exhibition', 'insight', 'update'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('published_at', 'desc');

        if ($category && ! in_array($category, ['All', 'Latest', 'all'])) {
            $query->where(function ($q) use ($category) {
                $q->where('category', $category)
                    ->orWhere('type', strtolower(str_replace(' ', '_', $category)));
            });
        }

        $featured = (clone $query)->where('is_featured', true)->first() ?: (clone $query)->first();
        $stories = $query->paginate(9);

        return response()->json([
            'success' => true,
            'featured' => $featured,
            'stories' => $stories->items(),
            'meta' => [
                'current_page' => $stories->currentPage(),
                'last_page' => $stories->lastPage(),
                'total' => $stories->total(),
                'category' => $category,
            ],
        ]);
    }

    /**
     * REST API: Get Story Detail with Curated Products.
     */
    public function getStoryDetailApi($slug)
    {
        $story = \App\Models\CmsPage::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $story) {
            return response()->json([
                'success' => false,
                'message' => 'Editorial story not found.',
            ], 404);
        }

        $featuredProducts = \App\Models\Product::where('is_active', true)
            ->inRandomOrder()
            ->take(3)
            ->get();

        $relatedStories = \App\Models\CmsPage::published()
            ->where('id', '!=', $story->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return response()->json([
            'success' => true,
            'story' => $story,
            'featured_products' => $featuredProducts,
            'related_stories' => $relatedStories,
        ]);
    }

    /**
     * REST API: Get Institutional Legal & CMS Content.
     */
    public function getContentPageApi($slug)
    {
        $aliasMap = [
            'terms' => 'terms-of-service',
            'privacy' => 'privacy-policy',
            'conditions' => 'conditions-of-sale',
            'care-guide' => 'care',
            'sustainability' => 'corporate-responsibility',
            'orders' => 'orders-payments',
        ];

        $targetSlug = $aliasMap[$slug] ?? $slug;

        $cmsPage = \App\Models\CmsPage::where('slug', $targetSlug)
            ->where('is_active', true)
            ->first();

        if ($cmsPage) {
            return response()->json([
                'success' => true,
                'page' => $cmsPage,
            ]);
        }

        // Standard institutional default content definitions for legal/care pages
        $pageDefinitions = [
            'terms-of-service' => [
                'title' => 'Terms of Service',
                'subtitle' => 'Legal conditions governing the use of Maison Éclore digital services',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Scope of Agreement',
                        'content' => 'By accessing and utilizing the Maison Éclore mobile and digital boutique platforms, you agree to comply with and be bound by the terms, conditions, and provisions set forth herein. All transactions are governed under authoritative Philippine and international commerce laws.',
                    ],
                    [
                        'heading' => '2. Intellectual Property & Atelier Rights',
                        'content' => 'All designs, motifs, technical schematics, bespoke renderings, trademarks, and photography appearing on this service represent the proprietary intellectual property of Éclore Joaillerie. Unauthorized reproduction, imitation, or redistribution is strictly prohibited.',
                    ],
                    [
                        'heading' => '3. Pricing & BIR VAT Compliance',
                        'content' => 'All prices displayed are denominated in Philippine Peso (PHP) and are fully inclusive of the statutory 12% Value Added Tax (VAT). Formal Bureau of Internal Revenue (BIR) compliant sales invoices are dispatched with every physical acquisition.',
                    ],
                    [
                        'heading' => '4. Bespoke Creations & Commission Retainers',
                        'content' => 'Custom atelier commissions require an initial non-refundable design deposit. Once gemstone acquisition and metal casting commence, modifications to the technical design cannot be accommodated.',
                    ],
                ],
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'subtitle' => 'Our commitment to protecting your personal data and confidentiality',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Collection of Client Information',
                        'content' => 'Maison Éclore collects personal data essential for fulfilling high-jewellery transactions, including identity verification, insured shipping addresses, ring dimensions, and private salon appointments.',
                    ],
                    [
                        'heading' => '2. Security & Tokenized Payment Protection',
                        'content' => 'Financial transactions are processed through PCI-DSS Level 1 certified gateways. We do not store unencrypted credit card details on our servers. All client communication is encrypted with TLS 1.3 protocol.',
                    ],
                    [
                        'heading' => '3. Philippine Data Privacy Act Compliance',
                        'content' => 'We operate in strict compliance with the Republic Act No. 10173 (Data Privacy Act of 2012 of the Philippines). You maintain the right to access, rectify, or request the deletion of your personal records at any time.',
                    ],
                ],
            ],
            'conditions-of-sale' => [
                'title' => 'Conditions of Sale',
                'subtitle' => 'Contractual provisions governing high jewellery acquisitions',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Order Acceptance & Verification',
                        'content' => 'Each order placed constitutes an offer to purchase our creations. Given the rarity of our stones, acceptance is confirmed only upon dispatch of our official Order Confirmation with unique gemstone certificate registry.',
                    ],
                    [
                        'heading' => '2. Insured Armed Transport',
                        'content' => 'All domestic and international shipments are conveyed via specialized armored courier services with full door-to-door transit insurance. Delivery requires an adult signature and biometric identity verification upon hand-over.',
                    ],
                    [
                        'heading' => '3. Returns, RMA & Exchange Policy',
                        'content' => 'Creations may be returned in pristine, unworn condition with all original packaging, seals, and certificates within 14 calendar days from receipt. Bespoke and engraved creations are final sale.',
                    ],
                ],
            ],
            'care' => [
                'title' => 'Care & Maintenance Guide',
                'subtitle' => 'Atelier guidance on preserving the everlasting beauty of your creations',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Daily Wear & Precious Metals',
                        'content' => '18K Gold and 950 Platinum require gentle handling. Avoid contact with perfumes, chlorine pools, and household chemicals. We advise putting your jewellery on last when dressing and removing it first when preparing for sleep.',
                    ],
                    [
                        'heading' => '2. Cleansing Diamonds & Hard Gemstones',
                        'content' => 'Immerse your piece in lukewarm water with a mild, neutral soap. Gently brush around the prongs using a soft-bristled brush. Rinse thoroughly in purified water and pat dry with the complimentary Éclore microfiber chamois.',
                    ],
                    [
                        'heading' => '3. Delicate Organic Gems & Emeralds',
                        'content' => 'Emeralds, pearls, and opals require specialized care. Never expose them to ultrasonic baths or steam cleaners. Clean only with a damp, soft cloth and schedule an annual inspection at your nearest Éclore salon.',
                    ],
                    [
                        'heading' => '4. Complimentary Lifetime Salon Servicing',
                        'content' => 'Every Éclore acquisition includes complimentary annual prong inspection, professional ultrasonic polishing, and ultrasonic claw re-tightening at any of our global boutiques.',
                    ],
                ],
            ],
            'corporate-responsibility' => [
                'title' => 'Corporate Responsibility & Sourcing',
                'subtitle' => 'Ethical craftsmanship, RJC certified conflict-free gemstones, and sustainability',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Responsible Jewellery Council (RJC)',
                        'content' => 'Maison Éclore is a certified member of the Responsible Jewellery Council. We enforce strict compliance with human rights, labor standards, and environmental practices throughout our entire supply chain.',
                    ],
                    [
                        'heading' => '2. The Kimberley Process & Conflict-Free Diamonds',
                        'content' => 'Every natural diamond mounted in our atelier is accompanied by warranties confirming compliant origin under the Kimberley Process Certification Scheme and United Nations resolutions.',
                    ],
                    [
                        'heading' => '3. 100% Recycled Precious Metals',
                        'content' => 'Our foundry casts exclusively with certified recycled 18K yellow, white, and rose gold, reducing carbon emissions and mining impact while retaining unmatched metallurgical purity.',
                    ],
                ],
            ],
            'help' => [
                'title' => 'Client Care & Assistance',
                'subtitle' => 'Our Concierge is at your service 7 days a week',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Contacting the Concierge',
                        'content' => 'Our client advisors are available via telephone at +63 2 8812 3456 or concierge@eclore.com Monday through Sunday from 09:00 to 21:00 PHT.',
                    ],
                    [
                        'heading' => '2. Private Salon Appointments',
                        'content' => 'You may schedule a private consultation at any of our worldwide boutiques via the online Appointments portal or directly through our in-app Concierge.',
                    ],
                    [
                        'heading' => '3. Ring Sizing & Atelier Resizing',
                        'content' => 'We provide one complimentary resizing within 90 days of purchase for all solitaire rings. Bespoke eternity bands with continuous stone settings must be ordered in exact dimensions.',
                    ],
                ],
            ],
            'accessibility' => [
                'title' => 'Accessibility Statement',
                'subtitle' => 'Ensuring an inclusive luxury experience across all digital surfaces',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Digital Inclusivity Standards',
                        'content' => 'Maison Éclore strives to ensure that its digital interfaces comply with the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA standards, featuring high-contrast typography and assistive screen-reader compatibility.',
                    ],
                    [
                        'heading' => '2. Boutique Accessibility',
                        'content' => 'All Éclore flagship residences feature barrier-free street-level entrances, elevator access to private salons, and trained concierge personnel to accommodate all patrons.',
                    ],
                ],
            ],
            'orders-payments' => [
                'title' => 'Orders & Insured Transport',
                'subtitle' => 'Details on payment options, processing times, and secure armored delivery',
                'last_updated' => 'January 2026',
                'sections' => [
                    [
                        'heading' => '1. Accepted Payment Methods',
                        'content' => 'We accept Visa, Mastercard, American Express, JCB, GCash, Maya, Bank Wire Transfers, and Cash on Delivery (COD) for qualifying orders up to ₱50,000.',
                    ],
                    [
                        'heading' => '2. Fulfillment & Armored Transport',
                        'content' => 'Ready-to-wear creations ship within 24 to 48 hours. Bespoke creations require 2 to 4 weeks atelier casting. All deliveries are conveyed via armed courier with real-time tracking.',
                    ],
                ],
            ],
        ];

        if (isset($pageDefinitions[$targetSlug])) {
            return response()->json([
                'success' => true,
                'page' => $pageDefinitions[$targetSlug],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Institutional document not found.',
        ], 404);
    }

    /**
     * REST API: AI Concierge Assistant Chatbot (Claire powered by Google Gemini Flash Lite)
     */
    public function conciergeChatApi(\Illuminate\Http\Request $request, \App\Services\GeminiConciergeService $gemini)
    {
        $rawMessage = (string) $request->input('message', '');

        if (trim($rawMessage) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Message cannot be empty.',
            ], 422);
        }

        $history = (array) $request->input('history', []);
        $result = $gemini->chat($rawMessage, $history);

        return response()->json($result);
    }
}
