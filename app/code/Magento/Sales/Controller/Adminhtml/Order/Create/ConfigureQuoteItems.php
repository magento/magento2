<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

class ConfigureQuoteItems extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Ajax handler to response configuration fieldset of composite product in quote items
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        // Prepare data
        $configureResult = new \Magento\Framework\DataObject();
        try {
            $quoteItemId = (int)$this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Quote item id is not received.'));
            }

            $quoteItem = $this->_objectManager->create(\Magento\Quote\Model\Quote\Item::class)->load($quoteItemId);
            if (!$quoteItem->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Quote item is not loaded.'));
            }

            $configureResult->setOk(true);
            $optionCollection = $this->_objectManager->create(\Magento\Quote\Model\Quote\Item\Option::class)
                ->getCollection()
                ->addItemFilter([$quoteItemId]);
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            $sessionQuote = $this->_objectManager->get(\Magento\Backend\Model\Session\Quote::class);
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());
        } catch (\Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        // Render page
        /** @var \Magento\Catalog\Helper\Product\Composite $helper */
        $helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class);
        return $helper->renderConfigureResult($configureResult);
    }
}
