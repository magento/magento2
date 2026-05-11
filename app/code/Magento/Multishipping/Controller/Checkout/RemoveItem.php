<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class RemoveItem
 *
 * Removes multishipping items
 */
class RemoveItem extends \Magento\Multishipping\Controller\Checkout implements HttpPostActionInterface
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
