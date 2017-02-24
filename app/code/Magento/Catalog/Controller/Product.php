<?php
/**
 * Product controller.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Catalog\Model\Product as ModelProduct;

abstract class Product extends \Magento\Framework\App\Action\Action implements ViewInterface
{
    /**
     * Initialize requested product object
     *
     * @return ModelProduct
     */
    protected function _initProduct()
    {
        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');

        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);

        /** @var \Magento\Catalog\Helper\Product $product */
        $product = $this->_objectManager->get(\Magento\Catalog\Helper\Product::class);
        return $product->initProduct($productId, $this, $params);
    }
}
