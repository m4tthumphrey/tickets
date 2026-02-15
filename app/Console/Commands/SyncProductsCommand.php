<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\TicketOption;
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

        $this->syncTicketOptions($client, $synced);

        return self::SUCCESS;
    }

    private function syncTicketOptions(TravelConnectionClient $client, array $productIds): void
    {
        $this->info('Syncing ticket options...');

        $syncedByProduct = [];

        foreach (array_chunk($productIds, 100) as $chunk) {
            $response = $client->post('inventory-status', [
                'products' => $chunk,
                'page' => [
                    'size' => 100,
                    'number' => 1,
                ],
            ]);

            foreach ($response['data'] ?? [] as $product) {
                $ticketOptions = $product['ticket_options'] ?? [];
                $rows = [];

                foreach ($ticketOptions as $option) {
                    $rows[] = [
                        'id' => $option['id'],
                        'product_id' => $product['id'],
                        'ticket_category' => $option['ticket_category'],
                        'name' => $option['name'],
                        'cost' => $option['price'],
                        'price' => ceil($option['price'] * 1.15 / 5) * 5,
                        'available' => $option['available'],
                        'max_purchase_qty' => $option['max_purchase_qty'],
                        'delivery_methods' => json_encode($option['delivery_methods'] ?? null),
                    ];

                    $syncedByProduct[$product['id']][] = $option['id'];
                }

                if ($rows) {
                    TicketOption::upsert(
                        $rows,
                        ['id'],
                        ['product_id', 'ticket_category', 'name', 'cost', 'price', 'available', 'max_purchase_qty', 'delivery_methods'],
                    );
                }
            }
        }

        // Delete stale ticket options
        $deleted = 0;

        foreach ($syncedByProduct as $productId => $optionIds) {
            $deleted += TicketOption::where('product_id', $productId)
                ->whereNotIn('id', $optionIds)
                ->delete();
        }

        // Delete all ticket options for products that returned none
        $productsWithoutOptions = array_diff($productIds, array_keys($syncedByProduct));

        if ($productsWithoutOptions) {
            $deleted += TicketOption::whereIn('product_id', $productsWithoutOptions)->delete();
        }

        $optionCount = array_sum(array_map('count', $syncedByProduct));
        $this->info("Synced {$optionCount} ticket options. Removed {$deleted} stale options.");
    }
}
