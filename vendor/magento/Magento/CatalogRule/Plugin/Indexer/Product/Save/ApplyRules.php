<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogRule\Plugin\Indexer\Product\Save;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;

class ApplyRules
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
     * Apply catalog rules after product save
     *
     * @param Product $subject
     * @param Product $result
     * @return Product
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        Product $subject,
        Product $result
    ) {
        if (!$result->getIsMassupdate()) {
            $this->productRuleProcessor->reindexRow($result->getId());
        }
        return $result;
    }
}
