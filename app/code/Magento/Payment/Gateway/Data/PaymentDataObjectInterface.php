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
 * @since 100.0.2
 */
interface PaymentDataObjectInterface
{
    /**
     * Returns order
     *
     * @return OrderAdapterInterface
     */
    public function getOrder();

    /**
     * Returns payment
     *
     * @return InfoInterface
     */
    public function getPayment();
}
