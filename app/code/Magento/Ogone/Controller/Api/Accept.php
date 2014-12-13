<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ogone\Controller\Api;

class Accept extends \Magento\Ogone\Controller\Api
{
    /**
     * When payment gateway accept the payment, it will land to here
     * need to change order status as processed Ogone
     * update transaction id
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_validateOgoneData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_ogoneProcess();
    }
}
