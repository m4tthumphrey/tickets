<?php

namespace App\Services\TravelConnection;

use App\Services\TravelConnection\Exception\TooManyRequestsException;
use App\Services\TravelConnection\Exception\TravelConnectionException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class TravelConnectionClient
{
    private readonly Client $client;
    private ?string $token = null;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function request(string $method, string $uri, array $options = [])
    {
        if ($this->token) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->token;
        }

        try {
            $response = $this->client->request($method, $uri, $options);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function post(string $uri, array $payload = [], array $options = [])
    {
        $options['json'] = $payload;

        return $this->request('post', $uri, $options);
    }

    public function put(string $uri, array $payload = [], array $options = [])
    {
        $options['json'] = $payload;

        return $this->request('put', $uri, $options);
    }

    public function get(string $uri, array $options = [])
    {
        return $this->request('get', $uri, $options);
    }

    private function handleResponse(ResponseInterface $response): mixed
    {
        if ('application/json' === $response->getHeaderLine('Content-type')) {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new TravelConnectionException('Non-JSON response');
    }

    private function handleException(Exception $exception)
    {
        if (!($exception instanceof RequestException && $exception->hasResponse())) {
            throw $exception;
        }

        $response = $exception->getResponse();

        if ('application/json' === $response->getHeaderLine('Content-type')) {
            $json = json_decode($response->getBody()->getContents(), true);

            if (isset($json['error'])) {
                throw new TravelConnectionException($json['error']);
            }

            $message = isset($json[0]) ? $json[0]['message'] : $json['message'];

            if (Response::HTTP_TOO_MANY_REQUESTS === $response->getStatusCode()) {
                throw new TooManyRequestsException($message);
            }

            throw new TravelConnectionException($message);
        }

        throw $exception;
    }
}
