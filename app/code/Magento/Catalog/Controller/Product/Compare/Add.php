<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product\Compare;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends \Magento\Catalog\Controller\Product\Compare implements HttpPostActionInterface
{
    /**
     * Add item to compare list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setRefererUrl();
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product) {
                $this->_catalogProductCompareList->addProduct($product);
                $productName = $this->_objectManager->get(
                    \Magento\Framework\Escaper::class
                )->escapeHtml($product->getName());
                $this->messageManager->addComplexSuccessMessage(
                    'addCompareSuccessMessage',
                    [
                        'product_name' => $productName,
                        'compare_list_url' => $this->_url->getUrl('catalog/product_compare')
                    ]
                );

                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);
            }

            $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class)->calculate();
        }
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
