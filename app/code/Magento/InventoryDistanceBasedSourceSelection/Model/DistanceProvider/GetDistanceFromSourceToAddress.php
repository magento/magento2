<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetDistanceInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetLatLngFromAddressInterface;

/**
 * Class GetDistanceFromSourceToAddress
 */
class GetDistanceFromSourceToAddress
{
    /**
     * @var GetLatLngFromSource
     */
    private $getLatLngFromSource;

    /**
     * @var GetLatLngFromAddressInterface
     */
    private $getLatLngFromAddress;

    /**
     * @var GetDistanceInterface
     */
    private $getDistance;

    /**
     * GetDistanceFromSourceToAddress constructor.
     *
     * @param GetLatLngFromSource $getLatLngFromSource
     * @param GetLatLngFromAddressInterface $getLatLngFromAddress
     * @param GetDistanceInterface $getDistance
     */
    public function __construct(
        GetLatLngFromSource $getLatLngFromSource,
        GetLatLngFromAddressInterface $getLatLngFromAddress,
        GetDistanceInterface $getDistance
    ) {
        $this->getLatLngFromSource = $getLatLngFromSource;
        $this->getLatLngFromAddress = $getLatLngFromAddress;
        $this->getDistance = $getDistance;
    }

    /**
     * Get distance from source to address
     *
     * @param SourceInterface $source
     * @param AddressInterface $address
     * @return float
     */
    public function execute(SourceInterface $source, AddressInterface $address): float
    {
        return $this->getDistance->execute(
            $this->getLatLngFromSource->execute($source),
            $this->getLatLngFromAddress->execute($address)
        );
    }
}
