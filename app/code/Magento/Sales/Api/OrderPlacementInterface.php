<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Api;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Introduce an interface to place order via REST API
 * @api
 */
interface OrderPlacementInterface
{
    /**
     * Perform place order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function save(OrderInterface $entity): OrderInterface;
}
