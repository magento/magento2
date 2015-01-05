<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
