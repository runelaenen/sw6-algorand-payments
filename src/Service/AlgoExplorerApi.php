<?php declare(strict_types=1);

namespace AlgorandPayments\Service;

use GuzzleHttp\Client;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class AlgoExplorerApi
{
    private Client $client;
    private SystemConfigService $systemConfigService;
    private string $endpoint;

    public function __construct(
        Client $client,
        SystemConfigService $systemConfigService
    ) {
        $this->client = $client;
        $this->systemConfigService = $systemConfigService;
        $this->endpoint = $this->getEndpoint();
    }

    public function getTransactions(): array
    {
        $response = $this->client->get(
            $this->endpoint . 'idx2/v2/accounts/' . $this->systemConfigService->get('AlgorandPayments.config.walletAddress') . '/transactions'
        );
        return json_decode((string) $response->getBody(), true)['transactions'];
    }

    public function getTransactionsWithNotePrefix(string $notePrefix)
    {
        $response = $this->client->get(
            $this->endpoint . 'idx2/v2/accounts/' . $this->systemConfigService->get('AlgorandPayments.config.walletAddress') . '/transactions?note-prefix=' . base64_encode($notePrefix)
        );
        return json_decode((string) $response->getBody(), true)['transactions'];
    }

    public function getTransactionsWithNotePrefixWithAlgos(string $notePrefix, float $algos)
    {
        $response = $this->client->get(
            $this->endpoint . 'idx2/v2/accounts/' . $this->systemConfigService->get('AlgorandPayments.config.walletAddress') . '/transactions?currency-greater-than=' . ((int)($algos*1000000)) . '&note-prefix=' . base64_encode($notePrefix)
        );
        return json_decode((string) $response->getBody(), true)['transactions'];
    }

    public function getEndpoint(): string
    {
        if ($this->systemConfigService->get('AlgorandPayments.config.nodeSettings') === 'main') {
            return 'https://api.algoexplorer.io/';
        }

        return 'https://api.testnet.algoexplorer.io/';
    }
}
