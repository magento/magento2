<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\InventoryRequestBuilder\FromOrder;

use Magento\InventorySourceSelectionApi\Model\InventoryRequestFromOrderBuilderInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;

/**
 * @inheritdoc
 */
class DefaultBuilder implements InventoryRequestFromOrderBuilderInterface
{
    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * Default request builder constructor
     *
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        InventoryRequestInterfaceFactory $inventoryRequestFactory
    ) {
        $this->inventoryRequestFactory = $inventoryRequestFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(int $stockId, int $orderId, array $requestItems): InventoryRequestInterface
    {
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items'   => $requestItems
        ]);

        return $inventoryRequest;
    }
}
