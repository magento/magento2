<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Onepage;

/**
 * @api
 * @since 2.0.0
 */
class Failure extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getRealOrderId()
    {
        return $this->_checkoutSession->getLastRealOrderId();
    }

    /**
     *  Payment custom error message
     *
     * @return string
     * @since 2.0.0
     */
    public function getErrorMessage()
    {
        $error = $this->_checkoutSession->getErrorMessage();
        return $error;
    }

    /**
     * Continue shopping URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getContinueShoppingUrl()
    {
        return $this->getUrl('checkout/cart');
    }
}
