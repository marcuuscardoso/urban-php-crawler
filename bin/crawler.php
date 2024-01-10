<?php

declare(strict_types=1);

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Crawler\CrawlProfiles\CrawlProfile;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

require_once __DIR__ . '/../vendor/autoload.php';

$dataArray = [];

Crawler::create()
    ->setCrawlProfile(new class extends CrawlProfile {
        public function shouldCrawl(UriInterface $url): bool
        {
            $path = $url->getPath();
            return $url->getHost() === 'amleiloeiro.com.br' && (str_starts_with($path, '/lote/') || $path === '/');
        }
    })
    ->setCrawlObserver(new class extends CrawlObserver {
        public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
        {
            if ($response->getStatusCode() !== 200) {
                return;
            }

            $this->processPage($url, $response);
        }

        public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
        {
            echo $requestException->getMessage() . PHP_EOL;
        }

        protected function processPage(UriInterface $url, ResponseInterface $response): void
        {
            $domCrawler = new DomCrawler((string)$response->getBody());

            $tableCrawler = $domCrawler->filter('table');

            if ($tableCrawler->count() > 0) {
                $this->processTable($url, $tableCrawler);
            }
        }

        protected function processTable(UriInterface $url, DomCrawler $tableCrawler): void
        {
            $rowsCrawler = $tableCrawler->filter('tbody tr');

            $rowsCrawler->each(function (DomCrawler $rowCrawler) use ($url) {
                $thCrawler = $rowCrawler->filter('th');

                if ($thCrawler->count() > 0 && $thCrawler->text() === 'Valores em leilão:') {
                    $tdCrawler = $rowCrawler->filter('td');

                    if ($tdCrawler->count() > 0) {
                        echo 'Getting data from: ' . $url->getPath() . PHP_EOL;

                        global $dataArray;
                        $dataArray[] = [
                            'URL do lote' => $url->getPath(),
                            'Primeiro leilao' => $this->extractDatePrice($tdCrawler->text(), 0),
                            'Segundo leilao' => $this->extractDatePrice($tdCrawler->text(), 1),
                        ];
                    }
                }
            });
        }

        protected function extractDatePrice(string $text, int $index): string
        {
            preg_match_all('/[A-Za-z]+, \d{2}\/\d{2}\/\d{4} - \d{2}:\d{2}h - R\$ [\d.,]+/', $text, $matches);

            return $matches[0][$index] ?? '';
        }
    })
    ->setDelayBetweenRequests(500)
    ->startCrawling('https://amleiloeiro.com.br/');

$csvFileName = 'lotes_data.csv';
$csvFile = fopen($csvFileName, 'w');

fputcsv($csvFile, ['URL do lote', 'Primeiro leilao', 'Segundo leilao']);

if (!empty($dataArray)) {
    foreach ($dataArray as $line) {
        fputcsv($csvFile, $line);
    }
} else {
    echo "The data array is empty" . PHP_EOL;
}

fclose($csvFile);

echo "CSV generated successfully: $csvFileName" . PHP_EOL;
