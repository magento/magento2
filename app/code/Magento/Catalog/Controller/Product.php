<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;

/**
 * Product controller.
 */
abstract class Product extends Action implements ViewInterface
{
    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @param Context $context
     * @param ProductHelper $productHelper
     */
    public function __construct(
        Context $context,
        ProductHelper $productHelper
    ) {
        parent::__construct($context);
        $this->productHelper = $productHelper;
    }

    /**
     * Initialize requested product object
     *
     * @return ModelProduct
     */
    protected function _initProduct()
    {
        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');

        $params = new DataObject();
        $params->setCategoryId($categoryId);

        return $this->productHelper->initProduct($productId, $this, $params);
    }
}
