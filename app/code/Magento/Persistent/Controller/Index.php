<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Persistent\Model\QuoteManager;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Session as SessionHelper;

/**
 * Persistent front controller
 * @codeCoverageIgnore
 */
abstract class Index extends Action
{
    /**
     * Persistent observer
     *
     * @var \Magento\Persistent\Model\Observer
     */
    protected $quoteManager;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $sessionHelper;

    /**
     * Whether clear checkout session when logout
     *
     * @var bool
     */
    protected $clearCheckoutSession = true;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Helper\Session $sessionHelper
     */
    public function __construct(
        Context $context,
        QuoteManager $quoteManager,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        SessionHelper $sessionHelper
    ) {
        $this->quoteManager = $quoteManager;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->sessionHelper = $sessionHelper;
        parent::__construct($context);
    }

    /**
     * Set whether clear checkout session when logout
     *
     * @param bool $clear
     * @return $this
     */
    public function setClearCheckoutSession($clear = true)
    {
        $this->clearCheckoutSession = $clear;
        return $this;
    }
}
