<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Model\Session;

/**
 * Test if checkout session valid for success action
 *
 * @api
 * @since 100.0.2
 */
class SuccessValidator
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
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
