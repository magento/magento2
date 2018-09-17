<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule;

use \Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class Validation. Call validate method for configurable product instead simple product
 */
class Validation
{
    /** @var Configurable */
    private $configurable;

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     */
    public function __construct(Configurable $configurableType)
    {
        $this->configurable = $configurableType;
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject|\Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function aroundValidate(
        \Magento\CatalogRule\Model\Rule $rule,
        \Closure $proceed,
        \Magento\Framework\DataObject $product
    ) {
        $validateResult = $proceed($product);
        if (!$validateResult && ($configurableProducts = $this->configurable->getParentIdsByChild($product->getId()))) {
            foreach ($configurableProducts as $configurableProductId) {
                $validateResult = $rule->getConditions()->validateByEntityId($configurableProductId);
                // If any of configurable product is valid for current rule, then their sub-product must be valid too
                if ($validateResult) {
                    break;
                }
            }
        }
        return $validateResult;
    }
}
