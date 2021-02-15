<?php declare(strict_types=1);

namespace AlgorandPayments\Service;

use AlgorandPayments\Service\AlgorandPriceApi\AlgorandPriceApiInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ServiceLocator;

class AlgorandPriceApi
{
    private ServiceLocator $serviceLocator;
    private SystemConfigService $configService;

    public function __construct(
        ServiceLocator $serviceLocator,
        SystemConfigService $configService
    )
    {
        $this->serviceLocator = $serviceLocator;
        $this->configService = $configService;
    }

    public function getPrice(string $currency): float
    {
        $serviceAlias = $this->configService->get('AlgorandPayments.config.rateConvertApi');

        /** @var AlgorandPriceApiInterface $service */
        $service = $this->serviceLocator->get($serviceAlias);
        $symbol = $service->getSymbol($currency);
        return $service->getPrice($symbol);
    }
}
