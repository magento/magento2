<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options;

use Magento\Mtf\Client\Element\OptgroupselectElement;
use Magento\Mtf\Client\ElementInterface;

/**
 * Option Type select on New Option dynamic rows in Customizable Options panel
 */
class Type extends OptgroupselectElement
{
    /**
     * Option group selector.
     *
     * @var string
     */
    protected $optGroup = './/*[@data-role="option-group" and *//*[contains(.,"%s")]]//span';

    /**
     * Option group locator.
     *
     * @var string
     */
    protected $optGroupValue = './/*[@data-role="option-group" and div[contains(.,"%s")]]//label[text()="%s"]';

    /**
     * Locator for Advanced Select element
     *
     * @var string
     */
    protected $advancedSelect = '[data-role="advanced-select"]';

    /**
     * Selector for selected option.
     *
     * @var string
     */
    protected $selectedOption = '[data-role="selected-option"]';

    /**
     * Get element data.
     *
     * @param ElementInterface $element
     * @return string
     */
    protected function getData(ElementInterface $element)
    {
        $selectedElement = $this->find($this->advancedSelect);
        $selectedElement->click();
        $text = trim($element->getText());
        $selectedElement->click();

        return $text;
    }

    /**
     * Select value in Option Type dropdown element.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $option = $this->prepareSetValue($value);
        if (!$option->isVisible()) {
            $this->find($this->advancedSelect)->click();
        }
        $option->click();
    }
}
