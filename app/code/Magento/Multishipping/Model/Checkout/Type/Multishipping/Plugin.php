<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

/**
 * Class \Magento\Multishipping\Model\Checkout\Type\Multishipping\Plugin
 *
 * @since 2.0.0
 */
class Plugin
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $checkoutSession;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $checkoutStateBegin;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function beforeSave(\Magento\Checkout\Model\Cart $subject)
    {
        if ($this->checkoutSession->getCheckoutState() === State::STEP_SELECT_ADDRESSES) {
            $this->checkoutSession->setCheckoutState(\Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN);
        }
    }
}
