<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Product;

use Magento\Review\Controller\Product as ProductController;
use Magento\Review\Model\Review;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Controller\ResultFactory;

class ListAction extends ProductController
{
    /**
     * Load specific layout handles by product type id
     *
     * @param CatalogProduct $product
     * @return \Magento\Framework\View\Result\Page
     */
    protected function getProductPage($product)
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($product->getPageLayout()) {
            $resultPage->getConfig()->setPageLayout($product->getPageLayout());
        }
        $urlSafeSku = rawurlencode($product->getSku());
        $resultPage->addPageLayoutHandles(['id' => $product->getId(), 'sku' => $urlSafeSku]);
        $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], null, false);
        $resultPage->addUpdate($product->getCustomLayoutUpdate());
        return $resultPage;
    }

    /**
     * Show list of product's reviews
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $product = $this->initProduct();
        if ($product) {
            $this->coreRegistry->register('productId', $product->getId());

            $settings = $this->catalogDesign->getDesignSettings($product);
            if ($settings->getCustomDesign()) {
                $this->catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }
            $resultPage = $this->getProductPage($product);
            // update breadcrumbs
            $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'product',
                    ['label' => $product->getName(), 'link' => $product->getProductUrl(), 'readonly' => true]
                );
                $breadcrumbsBlock->addCrumb('reviews', ['label' => __('Product Reviews')]);
            }
            return $resultPage;
        }
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        return $resultForward;
    }
}
