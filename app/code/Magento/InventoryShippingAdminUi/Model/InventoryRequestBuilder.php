<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Model;

use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;

/**
 * Build and inventory request
 */
class InventoryRequestBuilder
{
    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestInterfaceFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestInterfaceFactory;

    /**
     * InventoryRequestBuilder constructor.
     *
     * @param ItemRequestInterfaceFactory $itemRequestInterfaceFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ItemRequestInterfaceFactory $itemRequestInterfaceFactory,
        InventoryRequestInterfaceFactory $inventoryRequestInterfaceFactory
    ) {
        $this->itemRequestInterfaceFactory = $itemRequestInterfaceFactory;
        $this->inventoryRequestInterfaceFactory = $inventoryRequestInterfaceFactory;
    }

    /**
     * Build an inventory request
     *
     * @param int $stockId
     * @param array $requestData
     * @return InventoryRequestInterface
     */
    public function execute(int $stockId, array $requestData): InventoryRequestInterface
    {
        $requestItems = [];
        foreach ($requestData as $data) {
            $requestItems[] = $this->itemRequestInterfaceFactory->create([
                'sku' => $data['sku'],
                'qty' => $data['qty']
            ]);
        }

        return $this->inventoryRequestInterfaceFactory->create([
            'stockId' => $stockId,
            'items'   => $requestItems
        ]);
    }
}
