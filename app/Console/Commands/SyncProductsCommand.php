<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\TravelConnection\TravelConnectionClient;
use Illuminate\Console\Command;

class SyncProductsCommand extends Command
{
    protected $signature = 'tc:sync-products';

    protected $description = 'Sync football match products from the Travel Connection API';

    public function handle(TravelConnectionClient $client): int
    {
        $this->info('Syncing football match products...');

        $page = 1;
        $pageSize = 100;
        $synced = [];

        do {
            $response = $client->get('product', [
                'query' => [
                    'type' => 'football_match',
                    'start_date' => now()->format('Y-m-d'),
                    'page' => [
                        'size' => $pageSize,
                        'number' => $page,
                    ],
                ],
            ]);

            $products = $response['data'] ?? [];

            foreach ($products as $productData) {
                $match = $productData['match'] ?? [];

                Product::updateOrCreate(
                    ['id' => $productData['id']],
                    [
                        'name' => $productData['name'],
                        'slug' => $productData['slug'],
                        'type' => $productData['type'],
                        'starts_at' => $match['start']['utc'] ?? null,
                        'timezone' => $match['start']['tz'] ?? null,
                        'time_confirmed' => filter_var($match['time_confirmed'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'home_team_id' => $match['home'] ?? null,
                        'away_team_id' => $match['away'] ?? null,
                        'competition_id' => $match['competition'] ?? null,
                        'venue_id' => $productData['venue'] ?? null,
                        'status' => $match['status'] ?? null,
                        'min_order' => $productData['min_order'] ?? 1,
                        'max_order' => $productData['max_order'] ?? 99,
                        'main_image' => $productData['images']['main'] ?? null,
                        'thumb_image' => $productData['images']['thumb'] ?? null,
                        'information' => $productData['information'] ?? null,
                        'notes' => $productData['notes'] ?? null,
                        'timetable' => $productData['timetable'] ?? null,
                        'currency' => $productData['currency'] ?? null,
                        'categories' => $productData['categories'] ?? null,
                        'seating_plans' => $productData['seating_plan'] ?? null,
                    ],
                );

                $synced[] = $productData['id'];
            }

            $this->line("Page {$page} processed (" . count($products) . " products)");
            $page++;
        } while (count($products) === $pageSize);

        if (count($synced)) {
            Product::whereNotIn('id', $synced)->delete();
        }

        $syncCount = count($synced);

        $this->info("Synced {$syncCount} products.");

        return self::SUCCESS;
    }
}
