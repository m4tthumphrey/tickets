<?php

namespace App\Services\Anthropic;

use GuzzleHttp\Client;

class AnthropicClient
{
    private Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.anthropic.com/v1/',
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function message(string $prompt, string $model = 'claude-sonnet-4-5-20250929', int $maxTokens = 4096): string
    {
        $response = $this->client->post('messages', [
            'json' => [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        return $body['content'][0]['text'] ?? '';
    }
}
