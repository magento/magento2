<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Conditions
 * Catalog price rule conditions
 *
 */
class Conditions extends Block
{
    /**
     * Condition type selector
     *
     * @var string
     */
    protected $conditionType = '#conditions__1__new_child';

    /**
     * Condition value selector
     *
     * @var string
     */
    protected $conditionValue = '#conditions__1--1__value';

    /**
     * Add image click
     */
    public function clickAddNew()
    {
        $this->_rootElement->find('img.rule-param-add.v-middle')->click();
    }

    /**
     * Ellipsis image click
     */
    public function clickEllipsis()
    {
        $this->_rootElement->find('//a[contains(text(),"...")]', Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Select Condition type
     * @param  string $type
     */
    public function selectCondition($type)
    {
        $this->_rootElement->find($this->conditionType, Locator::SELECTOR_CSS, 'select')->setValue($type);
    }

    /**
     * Select Condition value
     * @param  string $value
     */
    public function selectConditionValue($value)
    {
        $this->_rootElement->find($this->conditionValue, Locator::SELECTOR_CSS, 'input')->setValue($value);
    }

    /**
     * Click save and continue button on form
     */
    public function clickSaveAndContinue()
    {
        $this->_rootElement->find('#save_and_continue_edit')->click();
    }
}
