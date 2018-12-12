<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\InventoryRequestBuilder\FromOrder;

use Magento\InventoryDistanceBasedSourceSelection\Model\GetAddressRequestFromOrder;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\InventoryRequestFromOrderBuilderInterface;

/**
 * @inheritdoc
 */
class DistanceBuilder implements InventoryRequestFromOrderBuilderInterface
{
    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var GetAddressRequestFromOrder
     */
    private $getAddressRequestFromOrder;

    /**
     * @var InventoryRequestExtensionInterfaceFactory
     */
    private $inventoryRequestExtensionInterfaceFactory;

    /**
     * DistanceBuilder constructor.
     *
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param GetAddressRequestFromOrder $getAddressRequestFromOrder
     * @param InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        GetAddressRequestFromOrder $getAddressRequestFromOrder,
        InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
    ) {
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->getAddressRequestFromOrder = $getAddressRequestFromOrder;
        $this->inventoryRequestExtensionInterfaceFactory = $inventoryRequestExtensionInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId, int $orderId, array $requestItems): InventoryRequestInterface
    {
        $address = $this->getAddressRequestFromOrder->execute($orderId);

        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items'   => $requestItems
        ]);

        $extensionAttributes = $this->inventoryRequestExtensionInterfaceFactory->create();
        $extensionAttributes->setDestinationAddress($address);
        $inventoryRequest->setExtensionAttributes($extensionAttributes);

        return $inventoryRequest;
    }
}
