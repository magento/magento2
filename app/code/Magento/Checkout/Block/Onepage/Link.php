<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Block\Onepage;

/**
 * One page checkout cart link
 *
 * @api
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        array $data = []
    ) {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Return URL for checkout
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout');
    }

    /**
     * Return true if Onepage Checkout is disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->_checkoutSession->getQuote()->validateMinimumAmount();
    }

    /**
     * Return true if it is possible to use the Onepage Checkout
     *
     * @return bool
     */
    public function isPossibleOnepageCheckout()
    {
        return $this->_checkoutHelper->canOnepageCheckout();
    }
}
