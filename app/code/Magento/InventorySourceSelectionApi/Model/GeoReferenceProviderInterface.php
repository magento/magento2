<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelection\Model\Request\LatLngRequest;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;

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
     * @param AddressRequestInterface $destination
     * @return float
     */
    public function getDistance(SourceInterface $source, AddressRequestInterface $destination): float;

    /**
     * Get latitude and longitude for one address
     *
     * @param AddressRequestInterface $destination
     * @return LatLngRequest
     */
    public function getAddressLatLng(AddressRequestInterface $destination): LatLngRequest;

    /**
     * Get latitude and longitude for one source
     *
     * @param SourceInterface $source
     * @return LatLngRequest
     */
    public function getSourceLatLng(SourceInterface $source): LatLngRequest;
}
