<?php
/**
 * Product controller.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Controller;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Catalog\Model\Product as ModelProduct;

class Product extends \Magento\Framework\App\Action\Action implements ViewInterface
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

        $params = new \Magento\Framework\Object();
        $params->setCategoryId($categoryId);

        /** @var \Magento\Catalog\Helper\Product $product */
        $product = $this->_objectManager->get('Magento\Catalog\Helper\Product');
        return $product->initProduct($productId, $this, $params);
    }
}
