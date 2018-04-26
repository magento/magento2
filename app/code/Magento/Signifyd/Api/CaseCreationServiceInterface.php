<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

/**
 * Signifyd case creation interface
 *
 * Interface of service for new Signifyd case creation and registering it on Magento side.
 * Implementation should send request to Signifyd API and create new entity in Magento.
 *
 * @api
 * @since 100.2.0
 */
interface CaseCreationServiceInterface
{
    /**
     * Create new case for order with specified id.
     *
     * @param int $orderId
     * @return bool
     * @throws \Magento\Framework\Exception\NotFoundException If order does not exists
     * @throws \Magento\Framework\Exception\AlreadyExistsException If case for $orderId already exists
     * @since 100.2.0
     */
    public function createForOrder($orderId);
}
