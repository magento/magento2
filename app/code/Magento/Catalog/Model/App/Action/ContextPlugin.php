<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
