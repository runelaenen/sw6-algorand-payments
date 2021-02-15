<?php declare(strict_types=1);

namespace AlgorandPayments\Service\AlgorandPriceApi;

interface AlgorandPriceApiInterface
{
    public function getPrice(string $symbol): float;

    /**
     * Returns null if currency is not supported
     */
    public function getSymbol(string $currency): ?string;
}
