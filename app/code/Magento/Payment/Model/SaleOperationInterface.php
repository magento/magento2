<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Model;

/**
 * Responsible for support of `sale` payment operation via Magento payment provider gateway.
 *
 * @api
 * @since 100.4.0
 */
interface SaleOperationInterface
{
    /**
     * Checks `sale` payment operation availability.
     *
     * @return bool
     * @since 100.4.0
     */
    public function canSale(): bool;

    /**
     * Executes `sale` payment operation.
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return void
     * @since 100.4.0
     */
    public function sale(InfoInterface $payment, float $amount);
}
