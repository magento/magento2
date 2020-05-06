<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

/**
 * Signifyd management interface
 * Allows to performs operations with Signifyd cases.
 *
 * @api
 * @since 100.2.0
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
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
     * @since 100.2.0
     */
    public function create($orderId);

    /**
     * Gets Case entity associated with order id.
     *
     * @param int $orderId
     * @return \Magento\Signifyd\Api\Data\CaseInterface|null
     * @since 100.2.0
     */
    public function getByOrderId($orderId);
}
