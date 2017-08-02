<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

use Magento\Payment\Model\InfoInterface;

/**
 * Interface PaymentDataObjectInterface
 * @package Magento\Payment\Gateway\Data
 * @api
 * @since 2.0.0
 */
interface PaymentDataObjectInterface
{
    /**
     * Returns order
     *
     * @return OrderAdapterInterface
     * @since 2.0.0
     */
    public function getOrder();

    /**
     * Returns payment
     *
     * @return InfoInterface
     * @since 2.0.0
     */
    public function getPayment();
}
