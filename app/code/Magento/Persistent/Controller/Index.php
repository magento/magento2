<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller;

/**
 * Persistent front controller
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Whether clear checkout session when logout
     *
     * @var bool
     */
    protected $_clearCheckoutSession = true;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Persistent observer
     *
     * @var \Magento\Persistent\Model\Observer
     */
    protected $quoteManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Persistent\Model\QuoteManager $quoteManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->quoteManager = $quoteManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
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
        $this->_clearCheckoutSession = $clear;
        return $this;
    }

    /**
     * Retrieve 'persistent session' helper instance
     *
     * @return \Magento\Persistent\Helper\Session
     */
    protected function _getHelper()
    {
        return $this->_objectManager->get('Magento\Persistent\Helper\Session');
    }
}
