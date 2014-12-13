<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

class Form extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @param Action\Context $context
     * @param Product\Builder $productBuilder
     * @param Product\Initialization\Helper $iniitializationHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        Product\Initialization\Helper $iniitializationHelper
    ) {
        $this->initializationHelper = $iniitializationHelper;
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
                'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle',
                'admin.product.bundle.items'
            )->setProductId(
                $product->getId()
            )->toHtml()
        );
    }
}
