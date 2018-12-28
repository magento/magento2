<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\Offline;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\DistanceProvider\GetLatLngFromAddressInterface;
use Magento\InventoryDistanceBasedSourceSelection\Model\Convert\AddressToString;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\LatLngInterfaceFactory;
use Magento\InventoryDistanceBasedSourceSelection\Model\ResourceModel\GetGeoNameDataByAddress;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressInterface;

/**
 * @inheritdoc
 */
class GetLatLngFromAddress implements GetLatLngFromAddressInterface
{
    private $latLngCache = [];

    /**
     * @var LatLngInterfaceFactory
     */
    private $latLngInterfaceFactory;

    /**
     * @var GetGeoNameDataByAddress
     */
    private $getGeoNameDataByAddress;

    /**
     * @var AddressToString
     */
    private $addressToString;

    /**
     * GetLatLngFromAddress constructor.
     *
     * @param GetGeoNameDataByAddress $getGeoNameDataByAddress
     * @param LatLngInterfaceFactory $latLngInterfaceFactory
     * @param AddressToString $addressToString
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetGeoNameDataByAddress $getGeoNameDataByAddress,
        LatLngInterfaceFactory $latLngInterfaceFactory,
        AddressToString $addressToString
    ) {
        $this->getGeoNameDataByAddress = $getGeoNameDataByAddress;
        $this->latLngInterfaceFactory = $latLngInterfaceFactory;
        $this->addressToString = $addressToString;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(AddressInterface $address): LatLngInterface
    {
        $cacheKey = $this->addressToString->execute($address);
        if (!isset($this->latLngCache[$cacheKey])) {
            $geoNameData = $this->getGeoNameDataByAddress->execute($address);

            $this->latLngCache[$cacheKey] = $this->latLngInterfaceFactory->create([
                'lat' => (float)$geoNameData['latitude'],
                'lng' => (float)$geoNameData['longitude'],
            ]);
        }

        return $this->latLngCache[$cacheKey];
    }
}
