<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\InventoryRequestBuilder\FromOrder;

use Magento\InventorySourceSelection\Model\InventoryRequestFromOrderBuilderInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * @inheritdoc
 */
class DefaultBuilder implements InventoryRequestFromOrderBuilderInterface
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
     * Default request builder constructor
     *
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        ItemRequestInterfaceFactory $itemRequestFactory
    ) {
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(int $stockId, OrderInterface $order, array $requestItems): InventoryRequestInterface
    {
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items'   => $requestItems
        ]);

        return $inventoryRequest;
    }
}
