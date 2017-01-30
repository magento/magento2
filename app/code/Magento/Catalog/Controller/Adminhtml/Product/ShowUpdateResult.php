<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Helper\Product\Composite;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;

class ShowUpdateResult extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /** @var Composite */
    protected $productCompositeHelper;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Composite $productCompositeHelper
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
