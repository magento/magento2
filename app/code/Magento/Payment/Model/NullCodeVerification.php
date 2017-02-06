<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Payment\Api\CodeVerificationInterface;

/**
 * Default implementation of code verification interface.
 * Provides AVS, CVV codes matching for payment methods which are not support AVS, CVV verification.
 */
class NullCodeVerification implements CodeVerificationInterface
{
    /**
     * @inheritdoc
     */
    public function getAvsCode()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getCvvCode()
    {
        return null;
    }
}
