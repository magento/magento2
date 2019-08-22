<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Multishipping\Controller\Checkout implements HttpGetActionInterface
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
