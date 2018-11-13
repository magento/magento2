<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Model;

use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\Sales\Api\Data\OrderInterface;

interface InventoryRequestBuilderFromOrderInterface
{
    /**
     * Source selection results provider
     *
     * @param OrderInterface $order
     * @return InventoryRequestInterface
     */
    public function execute(OrderInterface $order): InventoryRequestInterface;
}
