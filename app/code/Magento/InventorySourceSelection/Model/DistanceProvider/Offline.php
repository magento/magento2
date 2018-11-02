<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\DistanceProvider;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelection\Model\DistanceProvider\Offline\GetDistance;
use Magento\InventorySourceSelection\Model\DistanceProvider\Offline\GetLatLngRequestFromAddress;
use Magento\InventorySourceSelection\Model\DistanceProvider\Offline\GetLatLngRequestFromSource;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;
use Magento\InventorySourceSelectionApi\Model\DistanceProviderInterface;

/**
 * @inheritdoc
 */
class Offline implements DistanceProviderInterface
{
    /**
     * @var GetLatLngRequestFromSource
     */
    private $getLatLngRequestFromSource;

    /**
     * @var GetLatLngRequestFromAddress
     */
    private $getLatLngRequestFromAddress;

    /**
     * @var GetDistance
     */
    private $getDistance;

    /**
     * Offline constructor.
     *
     * @param GetLatLngRequestFromSource $getLatLngRequestFromSource
     * @param GetLatLngRequestFromAddress $getLatLngRequestFromAddress
     * @param GetDistance $getDistance
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetLatLngRequestFromSource $getLatLngRequestFromSource,
        GetLatLngRequestFromAddress $getLatLngRequestFromAddress,
        GetDistance $getDistance
    ) {

        $this->getLatLngRequestFromSource = $getLatLngRequestFromSource;
        $this->getLatLngRequestFromAddress = $getLatLngRequestFromAddress;
        $this->getDistance = $getDistance;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(SourceInterface $source, AddressRequestInterface $destination): float
    {
        $sourceLatLng = $this->getLatLngRequestFromSource->execute($source);
        $destinationLatLng = $this->getLatLngRequestFromAddress->execute($destination);

        return $this->getDistance->execute($sourceLatLng, $destinationLatLng);
    }
}
