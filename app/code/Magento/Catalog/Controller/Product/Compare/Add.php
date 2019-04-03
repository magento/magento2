<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product\Compare;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Add item to compare list action.
 */
class Add extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * Add item to compare list.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->isActionAllowed()) {
            return $resultRedirect->setRefererUrl();
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product && $product->isSalable()) {
                $this->_catalogProductCompareList->addProduct($product);
                $productName = $this->_objectManager->get(\Magento\Framework\Escaper::class)
                    ->escapeHtml($product->getName());
                $this->messageManager->addSuccessMessage(
                    __('You added product %1 to the comparison list.', $productName)
                );
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);
            }

            $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class)->calculate();
        }

        return $resultRedirect->setRefererOrBaseUrl();
    }

    /**
     * @return bool
     */
    private function isActionAllowed()
    {
        return $this->getRequest()->isPost() && $this->_formKeyValidator->validate($this->getRequest());
    }
}
