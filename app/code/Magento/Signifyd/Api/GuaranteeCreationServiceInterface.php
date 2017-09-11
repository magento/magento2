<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

/**
 * Signifyd guarantee creation interface
 *
 * Interface allows submit previously created Signifyd case for a guaranty.
 * Implementation should send request to Signifyd API and update existing case entity with guarantee infromation.
 *
 * @api
 * @since 100.2.0
 */
interface GuaranteeCreationServiceInterface
{
    /**
     * Request Signifyd guarantee for order
     *
     * @param int $orderId
     * @return bool
     * @since 100.2.0
     */
    public function createForOrder($orderId);
}
