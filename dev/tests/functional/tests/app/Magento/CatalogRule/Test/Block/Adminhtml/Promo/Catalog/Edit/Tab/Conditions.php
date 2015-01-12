<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Client\Element;
use Mtf\Factory\Factory;

/**
 * Class Conditions
 * Form Tab for specifying catalog price rule conditions
 *
 */
class Conditions extends Tab
{
    /**
     * Rule conditions block selector
     *
     * @var string
     */
    protected $ruleConditions = '#rule_conditions_fieldset';

    /**
     * Fill condition options
     *
     * @param array $fields
     * @param Element|null $element
     * @return void
     */
    public function fillFormTab(array $fields, Element $element = null)
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
}
