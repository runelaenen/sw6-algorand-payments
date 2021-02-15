<?php declare(strict_types=1);

namespace AlgorandPayments\Service\AlgorandPriceApi;

use GuzzleHttp\Client;

class BinanceApi implements AlgorandPriceApiInterface
{
    private Client $client;

    public function __construct(
        Client $client
    )  {
        $this->client = $client;
    }

    public function getPrice(string $symbol): float
    {
        $response = $this->client->get('https://api.binance.com/api/v1/ticker/price?symbol=' . $symbol);
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('price', $data)) {
            return (float) $data['price'];
        }

        return 0.00;
    }

    public function getSymbol(string $currency): ?string
    {
        switch ($currency) {
            case 'USD': return 'ALGOUSDT';
        }
        return null;
    }
}
