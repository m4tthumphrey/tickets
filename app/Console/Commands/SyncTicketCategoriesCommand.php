<?php

namespace App\Console\Commands;

use App\Models\TicketCategory;
use App\Models\Venue;
use App\Services\Anthropic\AnthropicClient;
use App\Services\TravelConnection\TravelConnectionClient;
use Illuminate\Console\Command;

class SyncTicketCategoriesCommand extends Command
{
    protected $signature = 'tc:sync-ticket-categories {--parse : Parse descriptions using AI}';

    protected $description = 'Sync ticket categories from the Travel Connection API';

    private bool $parsingEnabled = false;

    public function handle(TravelConnectionClient $client, AnthropicClient $anthropic): int
    {
        $this->info('Syncing ticket categories...');

        $this->parsingEnabled = $this->option('parse');

        if ($this->parsingEnabled) {
            $this->info('AI description parsing enabled.');
        }

        $venueIds = Venue::pluck('id')->flip();

        $page = 1;
        $pageSize = 100;
        $synced = 0;

        do {
            $response = $client->get('ticket-categories', [
                'query' => [
                    'page' => [
                        'size' => $pageSize,
                        'number' => $page,
                    ],
                ],
            ]);

            $categories = $response['data'] ?? [];

            foreach ($categories as $categoryData) {
                if ($categoryData['product_type'] !== 'football_match') {
                    continue;
                }

                $existing = TicketCategory::find($categoryData['id']);

                $baseData = [
                    'product_type' => $categoryData['product_type'] ?? null,
                    'venue_id' => isset($categoryData['venue']) && $venueIds->has($categoryData['venue'])
                        ? $categoryData['venue']
                        : null,
                    'name' => $categoryData['name'] ?? '',
                    'description' => $categoryData['description'] ?? null,
                    'consumer_info' => $categoryData['consumer_info'] ?? null,
                    'color' => $categoryData['color'] ?? null,
                    'delivery_methods' => $categoryData['delivery_methods'] ?? null,
                ];

                $needsParsing = !$existing?->human_description;

                if ($needsParsing) {
                    $parsed = $this->parseDescription(
                        $anthropic,
                        $categoryData['name'] ?? '',
                        $categoryData['description'] ?? null,
                        $categoryData['consumer_info'] ?? null,
                    );
                    $baseData = [...$baseData, ...$parsed];
                }

                TicketCategory::updateOrCreate(
                    ['id' => $categoryData['id']],
                    $baseData,
                );

                $synced++;
            }

            $this->line("Page {$page} processed (" . count($categories) . " categories)");
            $page++;
        } while (count($categories) === $pageSize);

        $this->info("Synced {$synced} ticket categories.");

        return self::SUCCESS;
    }

    private function parseDescription(AnthropicClient $anthropic, string $name, ?string $description, ?string $consumerInfo): array
    {
        $plainDescription = $description ? strip_tags($description) : '';
        $plainConsumerInfo = $consumerInfo ? strip_tags($consumerInfo) : '';
        $combined = trim($plainDescription . "\n" . $plainConsumerInfo);

        if ($combined === '' || !$this->parsingEnabled) {
            return $this->emptyParsedFields();
        }

        $this->line("  Parsing: {$name}");

        usleep(500_000); // 500ms delay to avoid rate limiting

        $prompt = <<<PROMPT
        You are parsing a ticket category description from an events/sports ticketing platform. Extract structured information from this description.

        Category name: {$name}

        Description:
        {$combined}

        Return ONLY a JSON object (no markdown, no explanation) with these fields:
        - seat_location (string|null): Stand, block, tier, side, row info
        - is_hospitality (boolean): Whether this is a hospitality/VIP package vs general admission
        - has_food_included (boolean): Whether any complimentary food is included
        - food_description (string|null): What food is included
        - has_drinks_included (boolean): Whether any complimentary drinks are included
        - drinks_description (string|null): What drinks are included
        - has_lounge_access (boolean): Whether there's access to a lounge/suite
        - lounge_name (string|null): Name of the lounge/suite if applicable
        - opening_times (string|null): When lounge/venue opens relative to kick-off
        - dress_code (string|null): Dress code requirements
        - is_family_friendly (boolean): Whether families and children are welcome (default true unless stated otherwise)
        - supporter_section (string|null): "home", "away", or "neutral"
        - has_padded_seats (boolean): Whether seats are padded/premium
        - ticket_delivery (string|null): How tickets are delivered
        - included_extras (array|null): Array of bonus inclusions (tours, vouchers, programmes, etc.)
        - accessibility_notes (string|null): Accessibility info/limitations
        - human_description (string): A clean, readable 2-3 sentence summary of this ticket category highlighting the key selling points. Write in a friendly, informative tone.

        Important:
        - Only set booleans to true if there's clear evidence in the description
        - Leave fields as null if the information isn't mentioned
        - For is_family_friendly, default to true unless the description suggests otherwise
        PROMPT;

        try {
            $response = $anthropic->message($prompt, 'claude-haiku-4-5-20251001', 1024);
            $json = json_decode($response, true);

            if (! is_array($json)) {
                $this->warn("  Failed to parse JSON response for: {$name}");

                return $this->emptyParsedFields();
            }

            return [
                'seat_location' => $json['seat_location'] ?? null,
                'is_hospitality' => $json['is_hospitality'] ?? false,
                'has_food_included' => $json['has_food_included'] ?? false,
                'food_description' => $json['food_description'] ?? null,
                'has_drinks_included' => $json['has_drinks_included'] ?? false,
                'drinks_description' => $json['drinks_description'] ?? null,
                'has_lounge_access' => $json['has_lounge_access'] ?? false,
                'lounge_name' => $json['lounge_name'] ?? null,
                'opening_times' => $json['opening_times'] ?? null,
                'dress_code' => $json['dress_code'] ?? null,
                'is_family_friendly' => $json['is_family_friendly'] ?? true,
                'supporter_section' => $json['supporter_section'] ?? null,
                'has_padded_seats' => $json['has_padded_seats'] ?? false,
                'ticket_delivery' => $json['ticket_delivery'] ?? null,
                'included_extras' => $json['included_extras'] ?? null,
                'accessibility_notes' => $json['accessibility_notes'] ?? null,
                'human_description' => $json['human_description'] ?? null,
            ];
        } catch (\Exception $e) {
            $this->warn("  AI parsing failed for: {$name} - {$e->getMessage()}");

            return $this->emptyParsedFields();
        }
    }

    private function emptyParsedFields(): array
    {
        return [
            'seat_location' => null,
            'is_hospitality' => false,
            'has_food_included' => false,
            'food_description' => null,
            'has_drinks_included' => false,
            'drinks_description' => null,
            'has_lounge_access' => false,
            'lounge_name' => null,
            'opening_times' => null,
            'dress_code' => null,
            'is_family_friendly' => true,
            'supporter_section' => null,
            'has_padded_seats' => false,
            'ticket_delivery' => null,
            'included_extras' => null,
            'accessibility_notes' => null,
            'human_description' => null,
        ];
    }
}
