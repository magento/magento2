<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Api\Data;

use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Provides interface to create instance of AVS, CVV verification services.
 *
 * @api
 */
interface CodeVerificationInterfaceFactory
{
    /**
     * Creates instance of code verification.
     * If payment does not support AVS, CVV verifications default implementation of
     * verification interface will be created.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $orderPayment
     * @return \Magento\Payment\Api\CodeVerificationInterface
     */
    public function create(OrderPaymentInterface $orderPayment);
}
