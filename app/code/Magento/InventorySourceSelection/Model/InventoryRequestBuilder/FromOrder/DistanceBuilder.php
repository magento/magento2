<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\InventoryRequestBuilder\FromOrder;

use Magento\InventorySourceSelection\Model\GetAddressRequestFromOrder;
use Magento\InventorySourceSelection\Model\InventoryRequestFromOrderBuilderInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * @inheritdoc
 */
class DistanceBuilder implements InventoryRequestFromOrderBuilderInterface
{
    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var GetAddressRequestFromOrder
     */
    private $getAddressRequestFromOrder;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var InventoryRequestExtensionInterfaceFactory
     */
    private $inventoryRequestExtensionInterfaceFactory;

    /**
     * DistanceBuilder constructor.
     *
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param GetAddressRequestFromOrder $getAddressRequestFromOrder
     * @param InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        ItemRequestInterfaceFactory $itemRequestFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        GetAddressRequestFromOrder $getAddressRequestFromOrder,
        InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
    ) {
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->getAddressRequestFromOrder = $getAddressRequestFromOrder;
        $this->orderItemRepository = $orderItemRepository;
        $this->inventoryRequestExtensionInterfaceFactory = $inventoryRequestExtensionInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId, OrderInterface $order, array $requestItems): InventoryRequestInterface
    {
        $address = $this->getAddressRequestFromOrder->execute((int) $order->getEntityId());

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
