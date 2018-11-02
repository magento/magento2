<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\DistanceProvider;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelection\Model\DistanceProvider\GoogleMap\GetDistance;
use Magento\InventorySourceSelection\Model\DistanceProvider\GoogleMap\GetLatLngRequestFromAddress;
use Magento\InventorySourceSelection\Model\DistanceProvider\GoogleMap\GetLatLngRequestFromSource;
use Magento\InventorySourceSelection\Model\Request\LatLngRequestFactory;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;
use Magento\InventorySourceSelectionApi\Model\DistanceProviderInterface;

/**
 * @inheritdoc
 */
class GoogleMap implements DistanceProviderInterface
{
    /**
     * @var GetDistance
     */
    private $getDistance;

    /**
     * @var LatLngRequestFactory
     */
    private $latLngRequestFactory;

    /**
     * @var GetLatLngRequestFromAddress
     */
    private $getLatLngRequestFromAddress;

    /**
     * @var GetLatLngRequestFromSource
     */
    private $getLatLngRequestFromSource;

    /**
     * GoogleMap constructor.
     *
     * @param GetLatLngRequestFromSource $getLatLngRequestFromSource
     * @param GetLatLngRequestFromAddress $getLatLngRequestFromAddress
     * @param LatLngRequestFactory $latLngRequestFactory
     * @param GetDistance $getDistance
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetLatLngRequestFromSource $getLatLngRequestFromSource,
        GetLatLngRequestFromAddress $getLatLngRequestFromAddress,
        LatLngRequestFactory $latLngRequestFactory,
        GetDistance $getDistance
    ) {
        $this->getDistance = $getDistance;
        $this->latLngRequestFactory = $latLngRequestFactory;
        $this->getLatLngRequestFromSource = $getLatLngRequestFromSource;
        $this->getLatLngRequestFromAddress = $getLatLngRequestFromAddress;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(SourceInterface $source, AddressRequestInterface $destination): float
    {
        $sourceLatLng = $this->getLatLngRequestFromSource->execute($source);
        $destinationLatLng = $this->getLatLngRequestFromAddress->execute($destination);

        return $this->getDistance->execute($sourceLatLng, $destinationLatLng);
    }
}
