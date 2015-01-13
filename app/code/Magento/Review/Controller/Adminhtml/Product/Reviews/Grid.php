<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product\Reviews;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        parent::__construct($context);
    }

    /**
     * Get product reviews grid
     *
     * @return void
     */
    public function execute()
    {
        $product = $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('admin.product.reviews')
            ->setProductId($product->getId())
            ->setUseAjax(true);
        $this->_view->renderLayout();
    }
}
