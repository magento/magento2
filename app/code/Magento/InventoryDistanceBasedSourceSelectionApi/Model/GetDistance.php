<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Model;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetDistanceInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetDistanceProviderCodeInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Exception\NoSuchDistanceProviderException;

/**
 * Get distance between two points
 *
 * @api
 */
class GetDistance implements GetDistanceInterface
{
    /**
     * @var GetDistanceInterface[]
     */
    private $providers;

    /**
     * @var GetDistanceProviderCodeInterface
     */
    private $getDistanceProviderCode;

    /**
     * GetLatLngFromSource constructor.
     *
     * @param GetDistanceProviderCodeInterface $getDistanceProviderCode
     * @param GetDistanceInterface[] $providers
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetDistanceProviderCodeInterface $getDistanceProviderCode,
        array $providers
    ) {
        foreach ($providers as $providerCode => $provider) {
            if (!($provider instanceof GetDistanceInterface)) {
                throw new \InvalidArgumentException(
                    'LatLng provider ' . $providerCode . ' must implement ' . GetDistanceInterface::class
                );
            }
        }

        $this->providers = $providers;
        $this->getDistanceProviderCode = $getDistanceProviderCode;
    }

    /**
     * @inheritdoc
     * @throws NoSuchDistanceProviderException
     */
    public function execute(LatLngInterface $source, LatLngInterface $destination): float
    {
        $code = $this->getDistanceProviderCode->execute();
        if (!isset($this->providers[$code])) {
            throw new NoSuchDistanceProviderException(
                __('No such distance provider: %1', $code)
            );
        }

        return $this->providers[$code]->execute($source, $destination);
    }
}
