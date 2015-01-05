<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Controller\Checkout;

class RemoveItem extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout remove item action
     *
     * @return void
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('id');
        $addressId = $this->getRequest()->getParam('address');
        if ($addressId && $itemId) {
            $this->_getCheckout()->setCollectRatesFlag(true);
            $this->_getCheckout()->removeAddressItem($addressId, $itemId);
        }
        $this->_redirect('*/*/addresses');
    }
}
