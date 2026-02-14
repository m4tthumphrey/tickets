<?php

namespace App\Console\Commands;

use App\Models\Venue;
use App\Services\TravelConnection\TravelConnectionClient;
use Illuminate\Console\Command;

class SyncVenuesCommand extends Command
{
    protected $signature = 'tc:sync-venues';

    protected $description = 'Sync venues from the Travel Connection API';

    public function handle(TravelConnectionClient $client): int
    {
        $this->info('Syncing venues...');

        $page = 1;
        $pageSize = 100;
        $synced = 0;

        do {
            $response = $client->get('venues', [
                'query' => [
                    'type' => 'football_match',
                    'page' => [
                        'size' => $pageSize,
                        'number' => $page,
                    ],
                ],
            ]);

            $venues = $response['data'] ?? [];

            foreach ($venues as $venueData) {
                Venue::updateOrCreate(
                    ['id' => $venueData['id']],
                    [
                        'name' => $venueData['name'],
                        'address' => $venueData['address'] ?? null,
                        'description' => $venueData['description'] ?? null,
                        'latitude' => $venueData['coordinates']['lat'] ?? null,
                        'longitude' => $venueData['coordinates']['lng'] ?? null,
                        'postcode' => $venueData['postcode'] ?? null,
                        'city' => $venueData['city'] ?? null,
                        'country' => $venueData['country'] ?? null,
                        'stadium_image' => $venueData['images']['stadium'] ?? null,
                        'seating_image' => $venueData['images']['seating'] ?? null,
                    ],
                );

                $synced++;
            }

            $this->line("Page {$page} processed (" . count($venues) . " venues)");
            $page++;
        } while (count($venues) === $pageSize);

        $this->info("Synced {$synced} venues.");

        return self::SUCCESS;
    }
}
