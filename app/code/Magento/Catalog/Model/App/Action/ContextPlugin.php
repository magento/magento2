<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\App\Action;

use Magento\Catalog\Helper\Data;

/**
 * Class ContextPlugin
 */
class ContextPlugin
{
    /**
     * @var \Magento\Catalog\Model\Product\ProductList\Toolbar
     */
    protected $toolbarModel;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Catalog\Helper\Product\ProductList
     */
    protected $productListHelper;

    /**
     * @param \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Product\ProductList $productListHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Product\ProductList $productListHelper
    ) {
        $this->toolbarModel = $toolbarModel;
        $this->httpContext = $httpContext;
        $this->productListHelper = $productListHelper;
    }

    /**
     * @param \Magento\Framework\App\Action\Action $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\Action\Action $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->httpContext->setValue(
            Data::CONTEXT_CATALOG_SORT_DIRECTION,
            $this->toolbarModel->getDirection(),
            \Magento\Catalog\Helper\Product\ProductList::DEFAULT_SORT_DIRECTION
        );
        $this->httpContext->setValue(
            Data::CONTEXT_CATALOG_SORT_ORDER,
            $this->toolbarModel->getOrder(),
            $this->productListHelper->getDefaultSortField()
        );
        $this->httpContext->setValue(
            Data::CONTEXT_CATALOG_DISPLAY_MODE,
            $this->toolbarModel->getMode(),
            $this->productListHelper->getDefaultViewMode()
        );
        $this->httpContext->setValue(
            Data::CONTEXT_CATALOG_LIMIT,
            $this->toolbarModel->getLimit(),
            $this->productListHelper->getDefaultLimitPerPageValue($this->productListHelper->getDefaultViewMode())
        );
        return $proceed($request);
    }
}
