<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

/**
 * Multishipping checkout address manipulation controller
 * @since 2.0.0
 */
abstract class Address extends \Magento\Framework\App\Action\Action
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->_getCheckout()->getCustomer()->getId()) {
            return $this->_redirect('customer/account/login');
        }
        return parent::dispatch($request);
    }

    /**
     * Retrieve multishipping checkout model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @since 2.0.0
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class);
    }

    /**
     * Retrieve checkout state model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping\State
     * @since 2.0.0
     */
    protected function _getState()
    {
        return $this->_objectManager->get(\Magento\Multishipping\Model\Checkout\Type\Multishipping\State::class);
    }
}
