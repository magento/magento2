<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class \Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit\Form
 *
 */
class Form extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @param Action\Context $context
     * @param Product\Builder $productBuilder
     * @param Product\Initialization\Helper $initializationHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        Product\Initialization\Helper $initializationHelper
    ) {
        $this->initializationHelper = $initializationHelper;
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $product = $this->initializationHelper->initialize($this->productBuilder->build($this->getRequest()));
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle::class,
                'admin.product.bundle.items'
            )->setProductId(
                $product->getId()
            )->toHtml()
        );
    }
}
