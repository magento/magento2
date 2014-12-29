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
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!$this->checkoutSession->getLastSuccessQuoteId()) {
            return false;
        }

        if (!$this->checkoutSession->getLastQuoteId() || !$this->checkoutSession->getLastOrderId()) {
            return false;
        }
        return true;
    }
}
