<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressInterface;

/**
 * Geo reference provider
 *
 * @api
 */
interface GeoReferenceProviderInterface
{
    /**
     * Return distance in kilometers between a source and a destination address
     *
     * @param SourceInterface $source
     * @param AddressInterface $destination
     * @return float
     */
    public function getDistance(SourceInterface $source, AddressInterface $destination): float;

    /**
     * Get latitude and longitude for one address
     *
     * @param AddressInterface $destination
     * @return LatLngInterface
     */
    public function getAddressLatLng(AddressInterface $destination): LatLngInterface;

    /**
     * Get latitude and longitude for one source
     *
     * @param SourceInterface $source
     * @return LatLngInterface
     */
    public function getSourceLatLng(SourceInterface $source): LatLngInterface;
}
