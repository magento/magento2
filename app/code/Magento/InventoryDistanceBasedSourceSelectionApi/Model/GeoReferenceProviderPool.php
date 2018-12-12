<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Model;

use Magento\InventoryDistanceBasedSourceSelectionApi\Exception\NoSuchGeoReferenceProviderException;

/**
 * Geo reference provider pool for distance based source selection
 *
 * @api
 */
class GeoReferenceProviderPool
{
    /**
     * @var array
     */
    private $providers;

    /**
     * GeoReferenceProviderPool constructor.
     *
     * @param array $providers
     */
    public function __construct(
        array $providers
    ) {
        $this->providers = $providers;

        foreach ($this->providers as $providerCode => $provider) {
            if (!($provider instanceof GeoReferenceProviderInterface)) {
                throw new \InvalidArgumentException(
                    'Distance provider ' . $providerCode . ' must implement GeoReferenceProviderInterface'
                );
            }
        }
    }

    /**
     * Get list of geo reference providers
     *
     * @return array
     */
    public function getList(): array
    {
        return $this->providers;
    }

    /**
     * Get GEO reference provider
     *
     * @param string $code
     * @return GeoReferenceProviderInterface
     * @throws NoSuchGeoReferenceProviderException
     */
    public function getProvider(string $code): GeoReferenceProviderInterface
    {
        if (!isset($this->providers[$code])) {
            throw new NoSuchGeoReferenceProviderException(__('Unknown geo reference provider %1', $code));
        }

        return $this->providers[$code];
    }
}
