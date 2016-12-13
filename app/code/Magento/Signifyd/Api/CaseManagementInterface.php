<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Signifyd management interface
 * Allows to performs operations with Signifyd cases
 *
 * @api
 */
interface CaseManagementInterface
{
    /**
     * Creates new Case entity
     * @param string $orderId
     * @return CaseInterface
     */
    public function create($orderId);

    /**
     * Gets Case entity
     * @param string $orderId
     * @return CaseInterface
     */
    public function getByOrderId($orderId);
}
