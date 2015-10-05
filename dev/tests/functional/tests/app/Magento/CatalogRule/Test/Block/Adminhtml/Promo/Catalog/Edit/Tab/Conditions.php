<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit\Tab;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Form Tab for specifying catalog price rule conditions.
 */
class Conditions extends Tab
{
    /**
     * Rule conditions block selector.
     *
     * @var string
     */
    protected $ruleConditions = '#rule_conditions_fieldset';

    /**
     * Add button.
     *
     * @var string
     */
    protected $addButton = '.rule-param-new-child a';

    /**
     * Locator for specific conditions.
     *
     * @var string
     */
    protected $conditionFormat = '//*[@id="conditions__1__new_child"]//option[contains(.,"%s")]';

    /**
     * Fill condition options.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return void
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);

        $conditionsBlock = Factory::getBlockFactory()->getMagentoCatalogRuleConditions(
            $element->find($this->ruleConditions)
        );
        $conditionsBlock->clickAddNew();

        $conditionsBlock->selectCondition($data['condition_type']['value']);
        $conditionsBlock->clickEllipsis();
        $conditionsBlock->selectConditionValue($data['condition_value']['value']);
    }

    /**
     * Check if attribute is available in conditions.
     *
     * @param CatalogProductAttribute $attribute
     * @return bool
     */
    public function isAttributeInConditions(CatalogProductAttribute $attribute)
    {
        $this->_rootElement->find($this->addButton)->click();
        return $this->_rootElement->find(
            sprintf($this->conditionFormat, $attribute->getFrontendLabel()),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }
}
