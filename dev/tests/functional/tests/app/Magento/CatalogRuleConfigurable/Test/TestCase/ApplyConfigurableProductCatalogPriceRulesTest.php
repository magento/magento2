<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRuleConfigurable\Test\TestCase;

use Magento\CatalogRule\Test\TestCase\ApplyCatalogPriceRulesTest;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Command\Cli\Cron;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleEdit;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Preconditions:
 * 1. Execute before each variation:
 *  - Delete all active catalog price rules
 *  - Create catalog price rule from dataset using Curl
 *
 * Steps:
 * 1. Apply all created rules.
 * 2. Create configurable product.
 * 3. Perform all assertions.
 *
 * @group Catalog_Rule_Configurable
 * @ZephyrId MAGETWO-24780
 */
class ApplyConfigurableProductCatalogPriceRulesTest extends ApplyCatalogPriceRulesTest
{
    /**
     * Add attribute_id to catalog price rule.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getAttribute(FixtureInterface $product)
    {
        $attributes = $product->getDataFieldConfig('configurable_attributes_data')['source']
            ->getAttributesData()['attribute_key_0'];
        $result['%attribute_id%'] = $attributes['attribute_code'];
        $result['%attribute_value%'] = $attributes['options']['option_key_' . $this->promo]['id'];
        return $result;
    }
}
