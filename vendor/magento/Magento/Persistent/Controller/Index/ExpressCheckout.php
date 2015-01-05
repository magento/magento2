<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
