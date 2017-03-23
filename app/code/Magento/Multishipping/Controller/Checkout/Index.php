<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

class Index extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Index action of Multishipping checkout
     *
     * @return void
     */
    public function execute()
    {
        $this->_getCheckoutSession()->setCartWasUpdated(false);
        $this->_redirect('*/*/addresses');
    }
}
