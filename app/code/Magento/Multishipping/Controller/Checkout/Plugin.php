<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

/**
 * Class \Magento\Multishipping\Controller\Checkout\Plugin
 *
 * @since 2.0.0
 */
class Plugin
{
    /**
     * @var \Magento\Checkout\Model\Cart
     * @since 2.0.0
     */
    protected $cart;

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @since 2.0.0
     */
    public function __construct(\Magento\Checkout\Model\Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Disable multishipping
     *
     * @param \Magento\Framework\App\Action\Action $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function beforeExecute(\Magento\Framework\App\Action\Action $subject)
    {
        $this->cart->getQuote()->setIsMultiShipping(0);
    }
}
