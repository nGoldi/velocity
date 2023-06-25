<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Ports\Outbound\IAbsenceProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class PersonioAdapter // implements IAbsenceProvider
{
    private const BASE_URL = 'https://api.personio.de/v1';
    private string $authToken;

    public function __construct(public HttpClientInterface $httpClient)
    {
    }

    public function getTimeOffs(): array
    {
        return $this->getData('/company/time-offs');
    }

    private function makeGetRequest(string $url): array
    {
        $response = $this->httpClient->request(
            'GET', $url,
            [
                'auth_basic' => [$_ENV['USERNAME'], $_ENV['JIRA_API_TOKEN']],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

        return $response->toArray();
    }

    private function getData(string $uri): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_URL . $uri, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->authToken,
                ]
            ]);

            return $response->toArray();
        } catch (\Throwable $ex) {
            echo $ex->getMessage() . PHP_EOL;
            return null;
        }
    }

    private function getInitialAuthToken(): string
    {
        try {
            $response = $this->httpClient->request('POST', self::BASE_URL . '/auth', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'client_id' => $_ENV['PERSONIO_CLIENT_ID'],
                    'client_secret' => $_ENV['PERSONIO_CLIENT_SECRET'],
                ],
            ]);

            $result = $response->toArray();
        } catch (\Throwable $ex) {
            echo $ex->getMessage() . PHP_EOL;
            exit;
        }

        if (!isset($result['data']['token'])) {
            echo 'Unable to retrieve personio auth token' . PHP_EOL;
            exit;
        }

        return $result['data']['token'];
    }
}