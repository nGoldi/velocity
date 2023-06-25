<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Model\ClosedSprint;
use App\Domain\Model\FutureSprint;
use App\Domain\Ports\Outbound\ISprintProvider;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class JiraAdapter implements ISprintProvider
{
    private const BASE_URL = 'https://sevdesk.atlassian.net/rest';
    private const REPORT_URL = '/greenhopper/1.0/rapid/charts/velocity.json?rapidViewId=';
    private string $boardId;

    public function __construct(public HttpClientInterface $httpClient)
    {
        $this->boardId = $_ENV['BOARD_ID'];
    }

    public function getClosedSprints(): array
    {
        $reportData = $this->getVelocityReport();
        $sprintsById = [];
        foreach ($reportData['sprints'] as $sprint) {
            $sprintsById[$sprint['id']] = $sprint;
        }

        $result = [];
        foreach ($reportData['velocityStatEntries'] as $sprintId => $entry) {
            $result[] = new ClosedSprint(
                $sprintId,
                $sprintsById[$sprintId]['name'],
                (int)$entry['completed']['value'],
            );
        }

        return $result;
    }

    public function getNextSprint(): FutureSprint
    {
        $sprintData = $this->makeGetRequest(self::BASE_URL . "/agile/1.0/board/$this->boardId/sprint?state=future&maxResults=1&orderBy=startDate");
        $sprint = $sprintData['values'][0];

        return new FutureSprint(
            $sprint['id'],
            $sprint['name'],
            new DatePeriod(
                new DateTimeImmutable($sprint['startDate']),
                new DateInterval('P1D'),
                new DateTimeImmutable($sprint['endDate']),
            )
        );
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

    private function getVelocityReport(): array
    {
        return $this->makeGetRequest(self::BASE_URL . self::REPORT_URL . $this->boardId);
    }

    private function getBoard(): array
    {
        return $this->makeGetRequest(self::BASE_URL . "/agile/1.0/board/$this->boardId");
    }
}
