<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\SuperConfig;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

class Index extends Action
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

    /**
     * @param Action\Context $context
     * @param Product\Builder $productBuilder
     */
    public function __construct(Action\Context $context, Product\Builder $productBuilder)
    {
        $this->productBuilder = $productBuilder;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
