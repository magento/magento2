<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\InventoryRequestBuilder\FromOrder;

use Magento\InventoryDistanceBasedSourceSelection\Model\GetAddressFromOrder;
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
     * @var GetAddressFromOrder
     */
    private $getAddressFromOrder;

    /**
     * @var InventoryRequestExtensionInterfaceFactory
     */
    private $inventoryRequestExtensionInterfaceFactory;

    /**
     * DistanceBuilder constructor.
     *
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param GetAddressFromOrder $getAddressFromOrder
     * @param InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        GetAddressFromOrder $getAddressFromOrder,
        InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
    ) {
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->getAddressFromOrder = $getAddressFromOrder;
        $this->inventoryRequestExtensionInterfaceFactory = $inventoryRequestExtensionInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId, int $orderId, array $requestItems): InventoryRequestInterface
    {
        $address = $this->getAddressFromOrder->execute($orderId);

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
