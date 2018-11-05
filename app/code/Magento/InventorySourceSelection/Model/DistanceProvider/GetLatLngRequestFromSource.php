<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\DistanceProvider;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelection\Model\Request\LatLngRequest;
use Magento\InventorySourceSelection\Model\Request\LatLngRequestFactory;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterfaceFactory;

/**
 * Get latitude and longitude from source
 *
 * TODO: Need to refactor with a virtual type to avoid code duplication with other providers
 */
class GetLatLngRequestFromSource
{
    /**
     * @var LatLngRequestFactory
     */
    private $latLngRequestFactory;

    /**
     * @var GetLatLngRequestFromAddress
     */
    private $getLatLngRequestFromAddress;

    /**
     * @var AddressRequestInterfaceFactory
     */
    private $addressRequestInterfaceFactory;

    /**
     * GetLatLngRequestFromAddress constructor.
     *
     * @param GetLatLngRequestFromAddress $getLatLngRequestFromAddress
     * @param LatLngRequestFactory $latLngRequestFactory
     * @param AddressRequestInterfaceFactory $addressRequestInterfaceFactory
     */
    public function __construct(
        GetLatLngRequestFromAddress $getLatLngRequestFromAddress,
        LatLngRequestFactory $latLngRequestFactory,
        AddressRequestInterfaceFactory $addressRequestInterfaceFactory
    ) {
        $this->latLngRequestFactory = $latLngRequestFactory;
        $this->getLatLngRequestFromAddress = $getLatLngRequestFromAddress;
        $this->addressRequestInterfaceFactory = $addressRequestInterfaceFactory;
    }

    /**
     * Get latitude and longitude from address
     *
     * @param SourceInterface $source
     * @return LatLngRequest
     * @throws LocalizedException
     */
    public function execute(SourceInterface $source): LatLngRequest
    {
        if (!$source->getLatitude() || !$source->getLongitude()) {
            $sourceAddress = $this->addressRequestInterfaceFactory->create([
                'country' => $source->getCountryId() ?? '',
                'postcode' => $source->getPostcode() ?? '',
                'streetAddress' => $source->getStreet() ?? '',
                'region' => $source->getRegion() ?? '',
                'city' => $source->getCity() ?? ''
            ]);

            return $this->getLatLngRequestFromAddress->execute($sourceAddress);
        } else {
            return $this->latLngRequestFactory->create([
                'lat' => (float)$source->getLatitude(),
                'lng' => (float)$source->getLongitude()
            ]);
        }
    }
}
