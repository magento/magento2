<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Plugin\Model\Product;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;

/**
 * Class \Magento\CatalogRule\Plugin\Model\Product\Action
 *
 * @since 2.0.0
 */
class Action
{
    /**
     * @var ProductRuleProcessor
     * @since 2.0.0
     */
    protected $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
     * @since 2.0.0
     */
    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * @param ProductAction $object
     * @param ProductAction $result
     * @return ProductAction
     *
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterUpdateAttributes(ProductAction $object, ProductAction $result)
    {
        $data = $result->getAttributesData();
        if (!empty($data['price'])) {
            $this->productRuleProcessor->reindexList($result->getProductIds());
        }

        return $result;
    }
}
