<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MenuScanService
{
    /**
     * Scan a menu image and extract structured categories and items.
     *
     * @param  UploadedFile|string  $image  File or base64 data string
     * @param  string|null  $customApiKey  User provided API key
     * @param  string  $provider  'gemini' or 'openai'
     * @param  string|null  $businessType  'restaurant', 'salon', 'retail'
     * @return array{success: bool, categories?: array, message?: string, raw_text?: string}
     */
    public function scanImage(
        UploadedFile|string $image,
        ?string $customApiKey = null,
        string $provider = 'gemini',
        ?string $businessType = 'restaurant'
    ): array {
        $apiKey = $customApiKey ?: config("services.{$provider}.key");

        if (empty($apiKey)) {
            return [
                'success' => false,
                'needs_key' => true,
                'message' => 'AI API key is not configured. Please provide a Google Gemini API Key (free from https://aistudio.google.com/apikey) or set GEMINI_API_KEY in your .env.',
            ];
        }

        // Prepare image data and mime type
        [$base64Data, $mimeType] = $this->extractImageData($image);

        if ($provider === 'openai') {
            return $this->scanWithOpenAi($base64Data, $mimeType, $apiKey, $businessType);
        }

        return $this->scanWithGemini($base64Data, $mimeType, $apiKey, $businessType);
    }

    /**
     * Extract base64 and mime type from UploadedFile or base64 string.
     *
     * @return array{0: string, 1: string}
     */
    protected function extractImageData(UploadedFile|string $image): array
    {
        if ($image instanceof UploadedFile) {
            $mimeType = $image->getMimeType() ?: 'image/jpeg';
            $base64Data = base64_encode(file_get_contents($image->getRealPath()));

            return [$base64Data, $mimeType];
        }

        // If it's a data URL like "data:image/jpeg;base64,...."
        if (preg_match('/^data:([^;]+);base64,(.+)$/', $image, $matches)) {
            return [$matches[2], $matches[1]];
        }

        return [$image, 'image/jpeg'];
    }

    /**
     * Scan with Google Gemini Vision API.
     */
    protected function scanWithGemini(
        string $base64Data,
        string $mimeType,
        string $apiKey,
        ?string $businessType
    ): array {
        $candidateModels = $this->getCandidateGeminiModels($apiKey);
        $systemPrompt = $this->buildSystemPrompt($businessType);
        $lastErrorMessage = '';

        foreach ($candidateModels as $model) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(60)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpoint, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $systemPrompt],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data' => $base64Data,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'response_mime_type' => 'application/json',
                            'temperature' => 0.2,
                        ],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    return $this->parseJsonResponse($text);
                }

                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                $lastErrorMessage = $errorMessage;

                // If the model is not found, deprecated, or doesn't support image input, try next candidate model
                if (
                    $response->status() === 404 ||
                    str_contains($errorMessage, 'is not found') ||
                    str_contains($errorMessage, 'not supported for generateContent') ||
                    str_contains($errorMessage, 'no longer available') ||
                    str_contains($errorMessage, 'modality is not enabled')
                ) {
                    Log::info("Gemini model {$model} not suitable ({$errorMessage}), trying next fallback model.");

                    continue;
                }

                // If it's an auth error or other error, break and return
                Log::warning('Gemini API Error: '.$errorMessage);

                return [
                    'success' => false,
                    'message' => 'Gemini API failed: '.$errorMessage,
                ];
            } catch (Exception $e) {
                Log::error("Menu scan exception with model {$model}: ".$e->getMessage());
                $lastErrorMessage = $e->getMessage();
            }
        }

        return [
            'success' => false,
            'message' => 'Gemini API failed: '.($lastErrorMessage ?: 'No supported Gemini model found for this key.'),
        ];
    }

    /**
     * Get candidate models in priority order, querying ListModels API if possible.
     *
     * @return array<string>
     */
    protected function getCandidateGeminiModels(string $apiKey): array
    {
        $configured = config('services.gemini.model');
        $preferredList = array_values(array_filter(array_unique([
            $configured,
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-2.5-flash',
            'gemini-1.5-pro',
            'gemini-2.0-flash-exp',
            'gemini-flash-latest',
        ])));

        try {
            $listResponse = Http::timeout(8)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
            if ($listResponse->successful()) {
                $models = $listResponse->json('models') ?? [];
                $available = [];

                foreach ($models as $m) {
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods, true)) {
                        $name = preg_replace('/^models\//', '', $m['name'] ?? '');

                        // Filter out text-to-speech, image generation, audio transcription, or non-multimodal models
                        if (
                            empty($name) ||
                            str_contains($name, 'tts') ||
                            str_contains($name, 'transcribe') ||
                            str_contains($name, 'embedding') ||
                            str_contains($name, 'image') ||
                            str_contains($name, 'banana')
                        ) {
                            continue;
                        }

                        $available[] = $name;
                    }
                }

                if (! empty($available)) {
                    $ordered = [];
                    foreach ($preferredList as $pref) {
                        if (in_array($pref, $available, true)) {
                            $ordered[] = $pref;
                        }
                    }
                    foreach ($available as $avail) {
                        if (str_contains($avail, 'flash') && ! in_array($avail, $ordered, true)) {
                            $ordered[] = $avail;
                        }
                    }
                    foreach ($available as $avail) {
                        if (! in_array($avail, $ordered, true)) {
                            $ordered[] = $avail;
                        }
                    }

                    if (! empty($ordered)) {
                        return array_unique($ordered);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info('ListModels lookup failed: '.$e->getMessage());
        }

        return $preferredList;
    }

    /**
     * Scan with OpenAI GPT-4o / GPT-4o-mini Vision API.
     */
    protected function scanWithOpenAi(
        string $base64Data,
        string $mimeType,
        string $apiKey,
        ?string $businessType
    ): array {
        $model = config('services.openai.model', 'gpt-4o-mini');
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $systemPrompt = $this->buildSystemPrompt($businessType);

        try {
            $response = Http::timeout(60)
                ->withToken($apiKey)
                ->post($endpoint, [
                    'model' => $model,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Extract all categories and dishes/items from this menu image with exact prices and details.',
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:{$mimeType};base64,{$base64Data}",
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'temperature' => 0.2,
                ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();

                return [
                    'success' => false,
                    'message' => 'OpenAI API failed: '.$errorMessage,
                ];
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';

            return $this->parseJsonResponse($text);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to OpenAI service: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Build prompt customized to business type.
     */
    protected function buildSystemPrompt(?string $businessType): string
    {
        $context = 'restaurant or food cafe menu';
        if ($businessType === 'salon') {
            $context = 'salon, beauty parlor, barber shop or spa rate card';
        } elseif ($businessType === 'retail') {
            $context = 'retail shop, grocery or product price catalog';
        }

        return <<<PROMPT
You are an expert menu digitizer and OCR specialist for a {$context}.
Analyze the provided menu image carefully and extract all items grouped logically into categories.

Strict Rules:
1. Extract ALL visible categories (e.g. Starters, Main Course, Breads, Beverages, Desserts, Combos, or Haircut, Facial, Spa services for salon).
2. For each item extract:
   - "name": Clean item title (e.g. "Paneer Butter Masala", "Cappuccino", "Chicken Biryani").
   - "description": Short description or ingredients if mentioned on menu, else null or empty string.
   - "price": Numerical price only (float/integer without currency symbols like ₹, $, etc.). If price has variants like Full/Half, use the base/full price or create two items like "Paneer Tikka (Half)" and "Paneer Tikka (Full)".
   - "food_type": For restaurants, strictly one of: "veg", "non-veg", or null (for beverages/drinks/desserts/water).
   - "duration_minutes": For salon services only, estimated duration in minutes (integer, default 30), else null.
3. For each category provide:
   - "name": Clean category name.
   - "icon": A matching FontAwesome 6 icon class (e.g. "fa-solid fa-utensils", "fa-solid fa-pizza-slice", "fa-solid fa-burger", "fa-solid fa-mug-hot", "fa-solid fa-ice-cream", "fa-solid fa-bowl-rice", "fa-solid fa-scissors").
   - "items": Array of items belonging to this category.

Respond ONLY with valid JSON in this exact structure:
{
  "categories": [
    {
      "name": "Category Name",
      "icon": "fa-solid fa-utensils",
      "description": "",
      "items": [
        {
          "name": "Dish Name",
          "description": "Tasty grilled cottage cheese",
          "price": 240,
          "food_type": "veg",
          "duration_minutes": null
        }
      ]
    }
  ]
}
PROMPT;
    }

    /**
     * Parse and sanitize JSON response from LLM.
     *
     * @return array{success: bool, categories?: array, message?: string}
     */
    protected function parseJsonResponse(string $text): array
    {
        // Strip markdown backticks if any
        $cleaned = trim($text);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        }

        $decoded = json_decode($cleaned, true);

        if (! is_array($decoded) || ! isset($decoded['categories']) || ! is_array($decoded['categories'])) {
            return [
                'success' => false,
                'message' => 'Could not parse menu structure from image. Please ensure the photo is clear and well-lit.',
                'raw_text' => $text,
            ];
        }

        // Sanitize categories and items
        $sanitizedCategories = [];
        $totalItems = 0;

        foreach ($decoded['categories'] as $cat) {
            $catName = trim($cat['name'] ?? '');
            if (empty($catName)) {
                continue;
            }

            $sanitizedItems = [];
            $items = $cat['items'] ?? [];

            if (is_array($items)) {
                foreach ($items as $item) {
                    $itemName = trim($item['name'] ?? '');
                    if (empty($itemName)) {
                        continue;
                    }

                    $rawPrice = $item['price'] ?? 0;
                    // Filter numbers out of price string if necessary
                    if (is_string($rawPrice)) {
                        $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
                    }
                    $price = max(0, floatval($rawPrice));

                    $foodType = strtolower(trim($item['food_type'] ?? ''));
                    if (! in_array($foodType, ['veg', 'non-veg'], true)) {
                        $foodType = null;
                    }

                    $sanitizedItems[] = [
                        'name' => $itemName,
                        'description' => trim($item['description'] ?? ''),
                        'price' => $price,
                        'food_type' => $foodType,
                        'duration_minutes' => isset($item['duration_minutes']) ? (int) $item['duration_minutes'] : null,
                    ];
                    $totalItems++;
                }
            }

            if (! empty($sanitizedItems) || ! empty($catName)) {
                $sanitizedCategories[] = [
                    'name' => $catName,
                    'icon' => $cat['icon'] ?? 'fa-solid fa-utensils',
                    'description' => $cat['description'] ?? '',
                    'items' => $sanitizedItems,
                ];
            }
        }

        if ($totalItems === 0) {
            return [
                'success' => false,
                'message' => 'No dishes or items could be recognized in the image. Please try a clearer photo or angle.',
            ];
        }

        return [
            'success' => true,
            'categories' => $sanitizedCategories,
            'total_items' => $totalItems,
        ];
    }
}
