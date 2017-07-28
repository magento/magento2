<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Helper\Product\Composite;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\ShowUpdateResult
 *
 * @since 2.0.0
 */
class ShowUpdateResult extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Helper\Product\Composite
     * @since 2.0.0
     */
    protected $productCompositeHelper;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Composite $productCompositeHelper
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Composite $productCompositeHelper
    ) {
        $this->productCompositeHelper = $productCompositeHelper;
        parent::__construct($context, $productBuilder);
    }

    /**
     * Show item update result from updateAction
     * in Wishlist and Cart controllers.
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        $layout = false;
        if ($this->_session->hasCompositeProductResult()
            && $this->_session->getCompositeProductResult() instanceof \Magento\Framework\DataObject
        ) {
            $layout = $this->productCompositeHelper->renderUpdateResult($this->_session->getCompositeProductResult());
        }
        $this->_session->unsCompositeProductResult();
        return $layout;
    }
}
