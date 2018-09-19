<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart;

class Configure extends \Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart
{
    /**
     * Ajax handler to response configuration fieldset of composite product in customer's cart
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $configureResult = new \Magento\Framework\DataObject();
        try {
            $this->_initData();

            $quoteItem = $this->_quoteItem;

            $optionCollection = $this->_objectManager->create(
                \Magento\Quote\Model\Quote\Item\Option::class
            )->getCollection()->addItemFilter(
                $quoteItem
            );
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setOk(true);
            $configureResult->setProductId($quoteItem->getProductId());
            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setCurrentCustomerId($this->_customerId);
        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        return $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class)
            ->renderConfigureResult($configureResult);
    }
}
