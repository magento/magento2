<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\Offline;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\DistanceProvider\GetLatLngRequestFromAddressInterface;
use Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert\AddressRequestToString;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\Request\LatLngRequestInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\Request\LatLngRequestInterfaceFactory;
use Magento\InventoryDistanceBasedSourceSelection\Model\ResourceModel\GetGeoNameDataByAddressRequest;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressRequestInterface;

/**
 * @inheritdoc
 */
class GetLatLngRequestFromAddress implements GetLatLngRequestFromAddressInterface
{
    private $latLngCache = [];

    /**
     * @var LatLngRequestInterfaceFactory
     */
    private $latLngRequestInterfaceFactory;

    /**
     * @var GetGeoNameDataByAddressRequest
     */
    private $getGeoNameDataByAddressRequest;

    /**
     * @var AddressRequestToString
     */
    private $addressRequestToString;

    /**
     * GetLatLngRequestFromAddress constructor.
     *
     * @param GetGeoNameDataByAddressRequest $getGeoNameDataByAddressRequest
     * @param LatLngRequestInterfaceFactory $latLngRequestInterfaceFactory
     * @param AddressRequestToString $addressRequestToString
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetGeoNameDataByAddressRequest $getGeoNameDataByAddressRequest,
        LatLngRequestInterfaceFactory $latLngRequestInterfaceFactory,
        AddressRequestToString $addressRequestToString
    ) {
        $this->getGeoNameDataByAddressRequest = $getGeoNameDataByAddressRequest;
        $this->latLngRequestInterfaceFactory = $latLngRequestInterfaceFactory;
        $this->addressRequestToString = $addressRequestToString;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(AddressRequestInterface $addressRequest): LatLngRequestInterface
    {
        $cacheKey = $this->addressRequestToString->execute($addressRequest);
        if (!isset($this->latLngCache[$cacheKey])) {
            $geoNameData = $this->getGeoNameDataByAddressRequest->execute($addressRequest);

            $this->latLngCache[$cacheKey] = $this->latLngRequestInterfaceFactory->create([
                'lat' => (float)$geoNameData['latitude'],
                'lng' => (float)$geoNameData['longitude'],
            ]);
        }

        return $this->latLngCache[$cacheKey];
    }
}
