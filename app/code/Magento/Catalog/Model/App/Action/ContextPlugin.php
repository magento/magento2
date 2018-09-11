<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\App\Action;

use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Before dispatch plugin for all frontend controllers to update http context.
 */
class ContextPlugin
{
    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var CatalogSession
     */
    private $catalogSession;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param ToolbarMemorizer $toolbarMemorizer
     * @param CatalogSession $catalogSession
     * @param HttpContext $httpContext
     */
    public function __construct(
        ToolbarMemorizer $toolbarMemorizer,
        CatalogSession $catalogSession,
        HttpContext $httpContext
    ) {
        $this->toolbarMemorizer = $toolbarMemorizer;
        $this->catalogSession = $catalogSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Update http context with catalog sensitive information.
     *
     * @return void
     */
    public function beforeDispatch()
    {
        if ($this->toolbarMemorizer->isMemorizingAllowed()) {
            $order = $this->catalogSession->getData(ToolbarModel::ORDER_PARAM_NAME);
            if ($order) {
                $this->httpContext->setValue(ToolbarModel::ORDER_PARAM_NAME, $order, false);
            }
            $direction = $this->catalogSession->getData(ToolbarModel::DIRECTION_PARAM_NAME);
            if ($direction) {
                $this->httpContext->setValue(ToolbarModel::DIRECTION_PARAM_NAME, $direction, false);
            }
            $mode = $this->catalogSession->getData(ToolbarModel::MODE_PARAM_NAME);
            if ($mode) {
                $this->httpContext->setValue(ToolbarModel::MODE_PARAM_NAME, $mode, false);
            }
            $limit = $this->catalogSession->getData(ToolbarModel::LIMIT_PARAM_NAME);
            if ($limit) {
                $this->httpContext->setValue(ToolbarModel::LIMIT_PARAM_NAME, $limit, false);
            }
        }
    }
}
