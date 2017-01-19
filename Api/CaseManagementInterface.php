<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

/**
 * Signifyd management interface
 * Allows to performs operations with Signifyd cases.
 *
 * @api
 */
interface CaseManagementInterface
{
    /**
     * Creates new Case entity linked to order id.
     *
     * @param int $orderId
     * @return \Magento\Signifyd\Api\Data\CaseInterface
     * @throws \Magento\Framework\Exception\NotFoundException If order does not exists
     * @throws \Magento\Framework\Exception\AlreadyExistsException If case for $orderId already exists
     */
    public function create($orderId);

    /**
     * Gets Case entity associated with order id.
     *
     * @param int $orderId
     * @return \Magento\Signifyd\Api\Data\CaseInterface|null
     */
    public function getByOrderId($orderId);
}
