<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\DistanceProvider\GetDistanceInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\DistanceProvider\GetLatLngFromAddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\LatLngInterfaceFactory;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\GeoReferenceProviderInterface;

/**
 * @inheritdoc
 */
class GeoReferenceProvider implements GeoReferenceProviderInterface
{
    /**
     * @var GetLatLngFromAddressInterface
     */
    private $getLatLngFromAddress;

    /**
     * @var GetDistanceInterface
     */
    private $getDistance;

    /**
     * @var LatLngInterfaceFactory
     */
    private $latLngInterfaceFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * GeoReferenceProvider constructor.
     *
     * @param LatLngInterfaceFactory $latLngInterfaceFactory
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param GetLatLngFromAddressInterface $getLatLngFromAddress
     * @param GetDistanceInterface $getDistance
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        LatLngInterfaceFactory $latLngInterfaceFactory,
        AddressInterfaceFactory $addressInterfaceFactory,
        GetLatLngFromAddressInterface $getLatLngFromAddress,
        GetDistanceInterface $getDistance
    ) {

        $this->getLatLngFromAddress = $getLatLngFromAddress;
        $this->getDistance = $getDistance;
        $this->latLngInterfaceFactory = $latLngInterfaceFactory;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function getDistance(SourceInterface $source, AddressInterface $destination): float
    {
        $sourceLatLng = $this->getSourceLatLng($source);
        $destinationLatLng = $this->getLatLngFromAddress->execute($destination);

        return $this->getDistance->execute($sourceLatLng, $destinationLatLng);
    }

    /**
     * @inheritdoc
     */
    public function getAddressLatLng(AddressInterface $destination): LatLngInterface
    {
        return $this->getLatLngFromAddress->execute($destination);
    }

    /**
     * @inheritdoc
     */
    public function getSourceLatLng(SourceInterface $source): LatLngInterface
    {
        if (!$source->getLatitude() || !$source->getLongitude()) {
            $sourceAddress = $this->addressInterfaceFactory->create([
                'country' => $source->getCountryId() ?? '',
                'postcode' => $source->getPostcode() ?? '',
                'streetAddress' => $source->getStreet() ?? '',
                'region' => $source->getRegion() ?? '',
                'city' => $source->getCity() ?? ''
            ]);

            return $this->getAddressLatLng($sourceAddress);
        }

        return $this->latLngInterfaceFactory->create([
            'lat' => (float) $source->getLatitude(),
            'lng' => (float) $source->getLongitude()
        ]);
    }
}
