<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiConciergeService
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = config('services.gemini.model', 'gemini-3.5-flash-lite');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
    }

    /**
     * Process user query with Gemini Flash Lite Concierge
     *
     * @param string $message
     * @param array $history
     * @return array
     */
    public function chat(string $message, array $history = []): array
    {
        $systemInstruction = $this->buildSystemInstruction();
        $contents = $this->formatContents($message, $history);

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.65,
                'maxOutputTokens' => 1024,
            ],
            'contents' => $contents,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(12)->post($this->endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($rawText) {
                    $parsed = json_decode($rawText, true);
                    if (is_array($parsed) && !empty($parsed['reply'])) {
                        return $this->formatResponse($parsed);
                    }
                }
            } else {
                Log::warning('Gemini Concierge API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // Fallback attempt with gemini-flash-lite-latest
                if ($this->model !== 'gemini-flash-lite-latest') {
                    $fallbackUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$this->apiKey}";
                    $fallbackResp = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->timeout(10)->post($fallbackUrl, $payload);

                    if ($fallbackResp->successful()) {
                        $data = $fallbackResp->json();
                        $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if ($rawText) {
                            $parsed = json_decode($rawText, true);
                            if (is_array($parsed) && !empty($parsed['reply'])) {
                                return $this->formatResponse($parsed);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gemini Concierge Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Resilient fallback in case of connection hiccup
        return $this->createEmergencyFallback($message);
    }

    /**
     * Build rich system instruction containing complete store knowledge and active product catalogue
     */
    protected function buildSystemInstruction(): string
    {
        $catalogueText = Cache::remember('eclore_concierge_catalog_knowledge', 300, function () {
            $products = Product::where('is_active', true)
                ->with('category:id,name')
                ->get(['id', 'category_id', 'name', 'slug', 'price', 'material', 'gemstone']);

            $lines = [];
            foreach ($products as $p) {
                $cat = $p->category->name ?? 'Fine Jewellery';
                $priceFormatted = number_format((float) ($p->final_price ?? $p->price), 0);
                $lines[] = "[ID: {$p->id}] {$p->name} | Category: {$cat} | Price: ₱{$priceFormatted} | Metal: {$p->material} | Gemstone: {$p->gemstone} | Slug: {$p->slug}";
            }

            return implode("\n", $lines);
        });

        return <<<EOT
You are Claire, the premier luxury Atelier Concierge for Maison Éclore Haute Joaillerie.
You speak with French-inspired grace, poise, warmth, and attentive sophistication. You seamlessly understand and reply in English and Filipino/Tagalog (Taglish is welcome when addressed in Filipino).

STRICT CONFIDENTIALITY & IDENTITY PROTOCOL:
- You are EXCLUSIVELY Claire, the private Atelier Concierge of Maison Éclore Haute Joaillerie.
- NEVER disclose, mention, acknowledge, or confirm the underlying AI model, architecture, technology provider, or company name (including but not limited to: Gemini, Google, OpenAI, ChatGPT, Claude, Anthropic, Noupe, DeepMind, LLM, Large Language Model, or neural network).
- If the client asks what model you are, who made you, if you are Gemini, what AI you use, what LLM powers you, or who programmed you:
  Politely, warmly, and gracefully decline to discuss technical architectures and reply:
  "I am Claire, your private Atelier Concierge created exclusively for Maison Éclore Haute Joaillerie. My sole purpose is to guide you through our fine jewellery collections, bespoke commissions, and atelier services. How may I assist you today?"
- Never break character. You are entirely and exclusively Claire of Maison Éclore.

MAISON ÉCLORE FACTS & POLICIES:
1. Heritage & Boutiques:
   • Manila Flagship Salon: Level 2, Greenbelt 5, Ayala Center, Makati
   • Paris Residence: 7 Place Vendôme, 75001 Paris
   • London Salon: 14 New Bond Street, Mayfair, London
   • New York Residence: 745 Fifth Avenue, New York, NY
   Private salons offer champagne hospitality, gemological viewings, and one-on-one appointments.

2. Ring Sizing & Measurement:
   • Standard ready-to-wear ring sizes: US 4 through 10 (including half and quarter sizes).
   • Complimentary Resizing: Every ring acquisition includes ONE COMPLIMENTARY ATELIER RESIZE within 90 days.
   • Sizing Guidance: Clients may measure finger circumference with string/paper against standard US charts or visit our Manila salon for complimentary biometric finger measurement.

3. Bespoke Commissions:
   • 4-Step Journey: 1) Private Consultation, 2) 3D CAD Modeling & Wax Preview, 3) Artisanal Casting & Setting by Master Goldsmiths (2-4 weeks), 4) Complimentary Inside-Shank Engraving (initials or date).

4. Precious Metals & Diamonds:
   • 18k Gold (75% pure - rich European luster), 14k Gold (durable daily wear), Platinum 950 (hypoallergenic, eternal natural white), and Gold Vermeil (heavy gold over 925 sterling silver). 100% nickel-free and BIR hallmarked.
   • All diamonds are conflict-free, Kimberley Process compliant, and GIA-certified with Triple Excellent cut and VVS/VS clarity.

5. Shipping & Armored Transit:
   • 100% insured armored door-to-door transit (Ferrari Group / Malca-Amit).
   • Metro Manila: 2–4 business days with signature & adult ID verification.
   • Provincial Philippines: 3–5 business days.
   • International: 4–7 business days.

6. Payments & Acquisition:
   • Credit/Debit: Visa, Mastercard, American Express (0% interest installments on select Philippine cards).
   • E-Wallets: GCash, Maya.
   • Bank Wire Transfer: BDO, BPI, Metrobank for high-value orders.
   • Cash on Delivery (COD): Up to ₱50,000 in qualifying urban areas.

7. Returns, Guarantees & Care:
   • 30-Day Atelier Return Guarantee: Pristine unworn catalogue items with intact security seals.
   • Lifetime Craftsmanship Warranty: Structural integrity and stone security.
   • Complimentary Lifetime Care: Free ultrasonic cleaning, claw tension checks, and rhodium refreshes at any salon.

LIVE ATELIER CATALOGUE (Real Products in Database):
{$catalogueText}

BEHAVIORAL INSTRUCTIONS:
- INFORMATIONAL QUESTIONS (sizing, locations, custom design, shipping, returns, payment methods, care, greetings):
  Answer directly, accurately, and thoroughly with charm and poise. In informational queries, DO NOT randomly push products. Set "recommended_product_id" to null.
- PRODUCT RECOMMENDATIONS / INQUIRIES (rings, necklaces, earrings, bracelets, watches, gifts, prices, gemstone queries like amethyst/topaz/sapphire, gold types):
  Recommend real pieces matching the user request directly from the LIVE ATELIER CATALOGUE above, quoting exact prices in ₱. Set "recommended_product_id" to the integer ID of the best matching product.
- Always be truthful to the catalogue; never fabricate fictitious products or prices.
- Return ONLY a JSON object with this exact structure:
{
  "reply": "string (polite, informative response with tasteful markdown formatting)",
  "recommended_product_id": number or null,
  "suggestions": ["3 short, engaging suggestion chips for follow-up questions"],
  "action": {"text": "button label", "route": "/tabs/catalogue or /products/{slug} or /appointments"} or null
}
EOT;
    }

    /**
     * Format conversation contents ensuring valid alternating roles
     */
    protected function formatContents(string $currentMessage, array $history): array
    {
        $contents = [];

        // Normalize previous turns
        foreach ($history as $item) {
            $sender = $item['role'] ?? $item['sender'] ?? '';
            $role = ($sender === 'model' || $sender === 'claire') ? 'model' : 'user';
            $text = trim($item['text'] ?? $item['message'] ?? '');

            if ($text === '') {
                continue;
            }

            // Ensure roles alternate
            if (!empty($contents) && end($contents)['role'] === $role) {
                // Skip duplicate consecutive role
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $text]],
            ];
        }

        // If the last turn was already user, update it; otherwise append current message
        if (!empty($contents) && end($contents)['role'] === 'user') {
            $contents[count($contents) - 1] = [
                'role' => 'user',
                'parts' => [['text' => $currentMessage]],
            ];
        } else {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $currentMessage]],
            ];
        }

        // Ensure first item is user
        if (!empty($contents) && $contents[0]['role'] !== 'user') {
            array_shift($contents);
        }

        if (empty($contents)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $currentMessage]],
            ];
        }

        return $contents;
    }

    /**
     * Format parsed JSON from Gemini and resolve Product model if recommended
     */
    protected function formatResponse(array $parsed): array
    {
        $reply = $parsed['reply'] ?? '';

        // Security & brand integrity: scrub any unintended mention of models or providers
        $scrubPatterns = [
            '/\b(gemini(\s*(flash|pro|lite|ultra|1\.5|2\.0|2\.5|3\.0|3\.5))?)\b/i' => 'Maison Éclore Concierge',
            '/\b(google\s*(ai|deepmind|generative)?)\b/i' => 'Maison Éclore',
            '/\b(chatgpt|openai|gpt-[0-9a-z\.]+)\b/i' => 'Maison Éclore Concierge',
            '/\b(large\s*language\s*model|llm)\b/i' => 'Atelier Concierge',
            '/\bnoupe\b/i' => 'Maison Éclore',
        ];
        $reply = preg_replace(array_keys($scrubPatterns), array_values($scrubPatterns), $reply);

        $recId = $parsed['recommended_product_id'] ?? null;
        $suggestions = is_array($parsed['suggestions'] ?? null) ? array_slice($parsed['suggestions'], 0, 3) : [];
        $action = $parsed['action'] ?? null;

        $productData = null;
        if (!empty($recId) && is_numeric($recId)) {
            $prod = Product::where('id', (int) $recId)->where('is_active', true)->first();
            if ($prod) {
                $price = $prod->final_price ?? $prod->price;
                $productData = [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'slug' => $prod->slug,
                    'price' => (float) $price,
                    'price_formatted' => '₱' . number_format((float) $price, 2),
                    'material' => $prod->material,
                    'gemstone' => $prod->gemstone,
                    'image' => $prod->primary_image ?? '/assets/ring.webp',
                ];

                if (!$action) {
                    $action = [
                        'type' => 'link',
                        'text' => "View {$prod->name}",
                        'route' => "/products/{$prod->slug}",
                    ];
                }
            }
        }

        // Ensure action has valid route
        if ($action && is_array($action)) {
            if (empty($action['type'])) {
                $action['type'] = 'link';
            }
            // Normalize routes
            if (isset($action['route'])) {
                $route = $action['route'];
                if (str_starts_with($route, '/catalog') || str_starts_with($route, '/collections')) {
                    $action['route'] = '/tabs/catalogue';
                }
            }
        }

        // Fallback suggestions if empty
        if (empty($suggestions)) {
            $suggestions = ['Ring Sizing Guide', 'Bespoke Commissions', 'Book Private Salon'];
        }

        return [
            'success' => true,
            'reply' => $reply,
            'product' => $productData,
            'action' => $action,
            'suggestions' => $suggestions,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Create emergency fallback response if network/API fails
     */
    protected function createEmergencyFallback(string $message): array
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'size') || str_contains($msg, 'sukat')) {
            $reply = "Maison Éclore rings are crafted in standard US sizes 4 through 10, with half and quarter increments available. Every creation includes **one complimentary atelier resize within 90 days**.\n\nYou may also visit our Manila salon in Greenbelt 5 for biometric finger measurement.";
            $suggestions = ['View Rings', 'Bespoke Inquiries', 'Book Salon Fitting'];
            $action = ['type' => 'link', 'text' => 'Book Salon Fitting', 'route' => '/appointments'];
        } elseif (str_contains($msg, 'where') || str_contains($msg, 'saan') || str_contains($msg, 'location') || str_contains($msg, 'store')) {
            $reply = "You are warmly welcome at our private flagship salons:\n\n• **Manila Salon:** Level 2, Greenbelt 5, Ayala Center, Makati\n• **Paris Residence:** 7 Place Vendôme, 75001 Paris\n• **London Salon:** 14 New Bond Street, Mayfair, London\n• **New York Residence:** 745 Fifth Avenue, New York";
            $suggestions = ['Book Salon Appointment', 'View Jewellery Vault', 'Track Delivery'];
            $action = ['type' => 'link', 'text' => 'Book Salon Appointment', 'route' => '/appointments'];
        } else {
            $reply = "Thank you for inquiring with Maison Éclore. I am **Claire**, your personal Atelier Concierge. I am delighted to guide you with finding your dream creations, diamond sizing, bespoke commissions, or boutique appointments.";
            $suggestions = ['Show Bestselling Creations', 'Ring Sizing Guide', 'Book Private Salon'];
            $action = ['type' => 'link', 'text' => 'Discover Collections', 'route' => '/tabs/catalogue'];
        }

        return [
            'success' => true,
            'reply' => $reply,
            'product' => null,
            'action' => $action,
            'suggestions' => $suggestions,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
