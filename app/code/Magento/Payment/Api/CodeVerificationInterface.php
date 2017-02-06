<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Api;

/**
 * AVS, CVV codes verification interface.
 *
 * @api
 */
interface CodeVerificationInterface
{
    /**
     * Gets payment AVS verification code.
     * Returns null if payment method does not support AVS verification.
     *
     * @return string|null
     */
    public function getAvsCode();

    /**
     * Gets payment CVV verification code.
     * Returns null if payment method does not support CVV verification.
     *
     * @return string|null
     */
    public function getCvvCode();
}
