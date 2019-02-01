<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetLatLngFromAddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterfaceFactory;

/**
 * Class GetLatLngFromSource
 */
class GetLatLngFromSource
{
    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var GetLatLngFromAddressInterface
     */
    private $getLatLngFromAddress;

    /**
     * @var LatLngInterfaceFactory
     */
    private $latLngInterfaceFactory;

    /**
     * GetAddressFromSource constructor.
     *
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param LatLngInterfaceFactory $latLngInterfaceFactory
     * @param GetLatLngFromAddressInterface $getLatLngFromAddress
     */
    public function __construct(
        AddressInterfaceFactory $addressInterfaceFactory,
        LatLngInterfaceFactory $latLngInterfaceFactory,
        GetLatLngFromAddressInterface $getLatLngFromAddress
    ) {
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->getLatLngFromAddress = $getLatLngFromAddress;
        $this->latLngInterfaceFactory = $latLngInterfaceFactory;
    }

    /**
     * Get latitude and longitude from source
     *
     * @param SourceInterface $source
     * @return LatLngInterface
     */
    public function execute(SourceInterface $source): LatLngInterface
    {
        if (!$source->getLatitude() || !$source->getLongitude()) {
            $sourceAddress = $this->addressInterfaceFactory->create([
                'country' => $source->getCountryId() ?? '',
                'postcode' => $source->getPostcode() ?? '',
                'street' => $source->getStreet() ?? '',
                'region' => $source->getRegion() ?? '',
                'city' => $source->getCity() ?? ''
            ]);

            return $this->getLatLngFromAddress->execute($sourceAddress);
        }

        return $this->latLngInterfaceFactory->create([
            'lat' => (float) $source->getLatitude(),
            'lng' => (float) $source->getLongitude()
        ]);
    }
}
