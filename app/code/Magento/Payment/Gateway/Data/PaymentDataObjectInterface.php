<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

use Magento\Payment\Model\InfoInterface;

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
