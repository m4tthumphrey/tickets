<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Services\TravelConnection\TravelConnectionClient;
use Illuminate\Console\Command;

class SyncCompetitionsCommand extends Command
{
    protected $signature = 'tc:sync-competitions';

    protected $description = 'Sync competitions from the Travel Connection API';

    public function handle(TravelConnectionClient $client): int
    {
        $this->info('Syncing competitions...');

        $page = 1;
        $pageSize = 100;
        $synced = 0;

        do {
            $response = $client->get('competitions', [
                'query' => [
                    'page' => [
                        'size' => $pageSize,
                        'number' => $page,
                    ],
                ],
            ]);

            $competitions = $response['data'] ?? [];

            foreach ($competitions as $competitionData) {
                Competition::updateOrCreate(
                    ['id' => $competitionData['id']],
                    [
                        'name' => $competitionData['name'],
                    ],
                );

                $synced++;
            }

            $this->line("Page {$page} processed (" . count($competitions) . " competitions)");
            $page++;
        } while (count($competitions) === $pageSize);

        $this->info("Synced {$synced} competitions.");

        return self::SUCCESS;
    }
}
