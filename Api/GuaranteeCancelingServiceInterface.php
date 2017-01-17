<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

/**
 * Signifyd guarantee canceling interface.
 *
 * Interface allows to submit request to cancel previously created guarantee.
 * Implementation should send request to Signifyd API and update existing case entity with guarantee information.
 *
 * @api
 */
interface GuaranteeCancelingServiceInterface
{
    /**
     * Cancels Signifyd guarantee for an order.
     *
     * @param int $orderId
     * @return bool
     */
    public function cancelForOrder($orderId);
}
