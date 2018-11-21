<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventorySourceSelection\Exception\NoSuchGeoReferenceProviderException;
use Magento\InventorySourceSelectionApi\Api\GetGeoReferenceProviderCodeInterface;

/**
 * Get selected geo reference provider
 *
 * @api
 */
class GetGeoReferenceProvider
{
    /**
     * @var GeoReferenceProviderPool
     */
    private $geoReferenceProviderPool;

    /**
     * @var GetGeoReferenceProviderCodeInterface
     */
    private $getGeoReferenceProviderCode;

    /**
     * GetGeoReferenceProvider constructor.
     *
     * @param GeoReferenceProviderPool $geoReferenceProviderPool
     * @param GetGeoReferenceProviderCodeInterface $getGeoReferenceProviderCode
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GeoReferenceProviderPool $geoReferenceProviderPool,
        GetGeoReferenceProviderCodeInterface $getGeoReferenceProviderCode
    ) {
        $this->geoReferenceProviderPool = $geoReferenceProviderPool;
        $this->getGeoReferenceProviderCode = $getGeoReferenceProviderCode;
    }

    /**
     * Get currently selected distance provider
     *
     * @return GeoReferenceProviderInterface
     * @throws NoSuchGeoReferenceProviderException
     */
    public function execute(): GeoReferenceProviderInterface
    {
        return $this->geoReferenceProviderPool->getProvider($this->getGeoReferenceProviderCode->execute());
    }
}
