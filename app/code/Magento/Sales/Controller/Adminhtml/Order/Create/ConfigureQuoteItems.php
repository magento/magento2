<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;


class ConfigureQuoteItems extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Ajax handler to response configuration fieldset of composite product in quote items
     *
     * @return void
     */
    public function execute()
    {
        // Prepare data
        $configureResult = new \Magento\Framework\Object();
        try {
            $quoteItemId = (int)$this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                throw new \Magento\Framework\Model\Exception(__('Quote item id is not received.'));
            }

            $quoteItem = $this->_objectManager->create('Magento\Sales\Model\Quote\Item')->load($quoteItemId);
            if (!$quoteItem->getId()) {
                throw new \Magento\Framework\Model\Exception(__('Quote item is not loaded.'));
            }

            $configureResult->setOk(true);
            $optionCollection = $this->_objectManager->create(
                'Magento\Sales\Model\Quote\Item\Option'
            )->getCollection()->addItemFilter(
                [$quoteItemId]
            );
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            $sessionQuote = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());
        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        // Render page
        $this->_objectManager->get(
            'Magento\Catalog\Helper\Product\Composite'
        )->renderConfigureResult(
            $configureResult
        );
    }
}
