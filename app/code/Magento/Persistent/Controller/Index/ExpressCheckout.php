<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller\Index;

class ExpressCheckout extends \Magento\Persistent\Controller\Index
{
    /**
     * Add appropriate session message and redirect to shopping cart
     *
     * @return void
     */
    public function execute()
    {
        $this->messageManager->addNotice(__('Your shopping cart has been updated with new prices.'));
        $this->_redirect('checkout/cart');
    }
}
