<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

/**
 * Class \Magento\Multishipping\Controller\Checkout\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Index action of Multishipping checkout
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_getCheckoutSession()->setCartWasUpdated(false);
        $this->_redirect('*/*/addresses');
    }
}
