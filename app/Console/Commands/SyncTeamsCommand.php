<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\TravelConnection\TravelConnectionClient;
use Illuminate\Console\Command;

class SyncTeamsCommand extends Command
{
    protected $signature = 'tc:sync-teams';

    protected $description = 'Sync teams from the Travel Connection API';

    public function handle(TravelConnectionClient $client): int
    {
        $this->info('Syncing teams...');

        $page = 1;
        $pageSize = 100;
        $synced = 0;

        do {
            $response = $client->get('teams', [
                'query' => [
                    'page' => [
                        'size' => $pageSize,
                        'number' => $page,
                    ],
                ],
            ]);

            $teams = $response['data'] ?? [];

            foreach ($teams as $teamData) {
                Team::updateOrCreate(
                    ['id' => $teamData['id']],
                    [
                        'name' => $teamData['name'],
                    ],
                );

                $synced++;
            }

            $this->line("Page {$page} processed (" . count($teams) . " teams)");
            $page++;
        } while (count($teams) === $pageSize);

        $this->info("Synced {$synced} teams.");

        return self::SUCCESS;
    }
}
