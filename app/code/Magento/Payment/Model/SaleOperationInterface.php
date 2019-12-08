<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

/**
 * Responsible for support of `sale` payment operation via Magento payment provider gateway.
 *
 * @api
 */
interface SaleOperationInterface
{
    /**
     * Checks `sale` payment operation availability.
     *
     * @return bool
     */
    public function canSale(): bool;

    /**
     * Executes `sale` payment operation.
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function sale(InfoInterface $payment, $amount);
}
