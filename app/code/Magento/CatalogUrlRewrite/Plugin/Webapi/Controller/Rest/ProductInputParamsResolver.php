<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Webapi\Controller\Rest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\SetSaveRewriteHistory;

/**
 * Plugin for InputParamsResolver
 *
 * Used to modify product data with save_rewrites_history flag
 */
class ProductInputParamsResolver
{
    /**
     * @var SetSaveRewriteHistory
     */
    private $rewriteHistory;

    /**
     * @param SetSaveRewriteHistory $rewriteHistory
     */
    public function __construct(SetSaveRewriteHistory $rewriteHistory)
    {
        $this->rewriteHistory = $rewriteHistory;
    }

    /**
     * Add 'save_rewrites_history' param to the product data
     *
     * @see \Magento\CatalogUrlRewrite\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $subject
     * @param array $result
     * @return array
     */
    public function afterResolve(\Magento\Webapi\Controller\Rest\InputParamsResolver $subject, array $result): array
    {
        $route = $subject->getRoute();

        if ($route->getServiceClass() === ProductRepositoryInterface::class && $route->getServiceMethod() === 'save') {
            $result = $this->rewriteHistory->execute(
                $result,
                'product',
                Product::class
            );
        }

        return $result;
    }
}
