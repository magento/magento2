<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\DistanceProvider;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelection\Model\Request\LatLngRequest;
use Magento\InventorySourceSelection\Model\Request\LatLngRequestFactory;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\GeoReferenceProviderInterface;

/**
 * @inheritdoc
 */
class GeoReferenceProvider implements GeoReferenceProviderInterface
{
    /**
     * @var GetLatLngRequestFromAddressInterface
     */
    private $getLatLngRequestFromAddress;

    /**
     * @var GetDistanceInterface
     */
    private $getDistance;

    /**
     * @var LatLngRequestFactory
     */
    private $latLngRequestFactory;

    /**
     * @var AddressRequestInterfaceFactory
     */
    private $addressRequestInterfaceFactory;

    /**
     * Offline constructor.
     *
     * @param LatLngRequestFactory $latLngRequestFactory
     * @param AddressRequestInterfaceFactory $addressRequestInterfaceFactory
     * @param GetLatLngRequestFromAddressInterface $getLatLngRequestFromAddress
     * @param GetDistanceInterface $getDistance
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        LatLngRequestFactory $latLngRequestFactory,
        AddressRequestInterfaceFactory $addressRequestInterfaceFactory,
        GetLatLngRequestFromAddressInterface $getLatLngRequestFromAddress,
        GetDistanceInterface $getDistance
    ) {

        $this->getLatLngRequestFromAddress = $getLatLngRequestFromAddress;
        $this->getDistance = $getDistance;
        $this->latLngRequestFactory = $latLngRequestFactory;
        $this->addressRequestInterfaceFactory = $addressRequestInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function getDistance(SourceInterface $source, AddressRequestInterface $destination): float
    {
        $sourceLatLng = $this->getSourceLatLng($source);
        $destinationLatLng = $this->getLatLngRequestFromAddress->execute($destination);

        return $this->getDistance->execute($sourceLatLng, $destinationLatLng);
    }

    /**
     * @inheritdoc
     */
    public function getAddressLatLng(AddressRequestInterface $destination): LatLngRequest
    {
        return $this->getLatLngRequestFromAddress->execute($destination);
    }

    /**
     * @inheritdoc
     */
    public function getSourceLatLng(SourceInterface $source): LatLngRequest
    {
        if (!$source->getLatitude() || !$source->getLongitude()) {
            $sourceAddress = $this->addressRequestInterfaceFactory->create([
                'country' => $source->getCountryId() ?? '',
                'postcode' => $source->getPostcode() ?? '',
                'streetAddress' => $source->getStreet() ?? '',
                'region' => $source->getRegion() ?? '',
                'city' => $source->getCity() ?? ''
            ]);

            return $this->getAddressLatLng($sourceAddress);
        }

        return $this->latLngRequestFactory->create([
            'lat' => (float) $source->getLatitude(),
            'lng' => (float) $source->getLongitude()
        ]);
    }
}
