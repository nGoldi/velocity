<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Model\Sprint;
use App\Domain\Ports\Outbound\ISprintProvider;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

readonly class JiraAdapter implements ISprintProvider
{
    private const BASE_URL = 'https://sevdesk.atlassian.net/rest';
    private const REPORT_URL = '/greenhopper/1.0/rapid/charts/velocity.json?rapidViewId=';
    private string $boardId;

    public function __construct(private HttpClientInterface $httpClient, private LoggerInterface $logger)
    {
        $this->boardId = $_ENV['BOARD_ID'];
    }

    public function getClosedSprints(): array
    {
        $reportData = $this->getVelocityReport();

        $result = [];
        foreach ($reportData['velocityStatEntries'] as $sprintId => $entry) {
            $sprintData = $this->getSprintDataBy($sprintId);
            $result[] = new Sprint(
                $sprintId,
                (int)$entry['completed']['value'],
                new DatePeriod(
                    new DateTimeImmutable($sprintData['startDate']),
                    new DateInterval('P1D'),
                    new DateTimeImmutable($sprintData['endDate']),
                )
            );
        }

        return $result;
    }

    public function getNextSprint(): Sprint
    {
        $sprintData = $this->makeGetRequest(self::BASE_URL . "/agile/1.0/board/$this->boardId/sprint?state=future&maxResults=1&orderBy=startDate");
        $sprint = $sprintData['values'][0];

        return new Sprint(
            $sprint['id'],
            0,
            new DatePeriod(
                new DateTimeImmutable($sprint['startDate']),
                new DateInterval('P1D'),
                new DateTimeImmutable($sprint['endDate']),
            )
        );
    }

    public function getSprintDataBy(int $sprintId): array
    {
        return $this->makeGetRequest(self::BASE_URL . "/agile/1.0/sprint/$sprintId");
    }

    private function makeGetRequest(string $url): array
    {
        $result = [];

        try {
            $response = $this->httpClient->request(
                'GET', $url,
                [
                    'auth_basic' => [$_ENV['USERNAME'], $_ENV['JIRA_API_TOKEN']],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]);

            if ($response->getStatusCode() === 200) {
                $result = $response->toArray();
            } else {
                $this->logger->warning('Unexpected response status code', [
                    'status_code' => $response->getStatusCode(),
                    'content' => $response->getContent(false)
                ]);
            }
        } catch (Throwable $ex) {
            echo $ex->getMessage();
        }

        return $result;
    }

    private function getVelocityReport(): array
    {
        return $this->makeGetRequest(self::BASE_URL . self::REPORT_URL . $this->boardId);
    }

    private function getBoard(): array
    {
        return $this->makeGetRequest(self::BASE_URL . "/agile/1.0/board/$this->boardId");
    }
}
