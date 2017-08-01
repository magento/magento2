<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;

use Exception;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist\Configure
 *
 * @since 2.0.0
 */
class Configure extends \Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist
{
    /**
     * Ajax handler to response configuration fieldset of composite product in customer's wishlist.
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        $configureResult = new \Magento\Framework\DataObject();
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

        return $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class)
            ->renderConfigureResult($configureResult);
    }
}
