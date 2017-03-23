<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Plugin\Model\Product;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;

class Action
{
    /**
     * @var ProductRuleProcessor
     */
    protected $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
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
