<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

class Plugin
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var string
     */
    protected $checkoutStateBegin;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Map STEP_SELECT_ADDRESSES to Cart::CHECKOUT_STATE_BEGIN
     * @param \Magento\Checkout\Model\Cart $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(\Magento\Checkout\Model\Cart $subject)
    {
        if ($this->checkoutSession->getCheckoutState() === State::STEP_SELECT_ADDRESSES) {
            $this->checkoutSession->setCheckoutState(\Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN);
        }
    }
}
