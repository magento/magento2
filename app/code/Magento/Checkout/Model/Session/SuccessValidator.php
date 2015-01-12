<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Session;

/**
 * Test is checkout session valid for success action
 */
class SuccessValidator
{
    /**
     * Is valid session?
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @return bool
     */
    public function isValid(\Magento\Checkout\Model\Session $checkoutSession)
    {
        if (!$checkoutSession->getLastSuccessQuoteId()) {
            return false;
        }

        if (!$checkoutSession->getLastQuoteId() || !$checkoutSession->getLastOrderId()) {
            return false;
        }
        return true;
    }
}
