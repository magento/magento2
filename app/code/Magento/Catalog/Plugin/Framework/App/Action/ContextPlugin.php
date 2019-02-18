<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin\Framework\App\Action;

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
            $params = [
                ToolbarModel::ORDER_PARAM_NAME,
                ToolbarModel::DIRECTION_PARAM_NAME,
                ToolbarModel::MODE_PARAM_NAME,
                ToolbarModel::LIMIT_PARAM_NAME,
            ];

            foreach ($params as $param) {
                $paramValue = $this->catalogSession->getData($param);
                if ($paramValue) {
                    $this->httpContext->setValue($param, $paramValue, false);
                }
            }
        }
    }
}
