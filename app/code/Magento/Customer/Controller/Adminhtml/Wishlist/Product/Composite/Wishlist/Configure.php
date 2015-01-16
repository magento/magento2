<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;

use Exception;

class Configure extends \Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist
{
    /**
     * Ajax handler to response configuration fieldset of composite product in customer's wishlist.
     *
     * @return void
     */
    public function execute()
    {
        $configureResult = new \Magento\Framework\Object();
        try {
            $this->_initData();

            $configureResult->setProductId($this->_wishlistItem->getProductId());
            $configureResult->setBuyRequest($this->_wishlistItem->getBuyRequest());
            $configureResult->setCurrentStoreId($this->_wishlistItem->getStoreId());
            $configureResult->setCurrentCustomerId($this->_wishlist->getCustomerId());

            $configureResult->setOk(true);
        } catch (Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        $this->_objectManager->get(
            'Magento\Catalog\Helper\Product\Composite'
        )->renderConfigureResult(
            $configureResult
        );
    }
}
