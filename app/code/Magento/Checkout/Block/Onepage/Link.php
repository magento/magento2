<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Onepage;

/**
 * One page checkout cart link
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     * @since 2.0.0
     */
    protected $_checkoutHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     * @since 2.0.0
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
     * @return string
     * @since 2.0.0
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout');
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isDisabled()
    {
        return !$this->_checkoutSession->getQuote()->validateMinimumAmount();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isPossibleOnepageCheckout()
    {
        return $this->_checkoutHelper->canOnepageCheckout();
    }
}
