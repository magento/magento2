<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    protected $optGroup = './/*/*/*/ul/li/div/label/span/ancestor::li/ul/li/div/label[text() = "%s"]';

    /**
     * Option group locator.
     *
     * @var string
     */
    protected $optGroupValue = './/*/*/*/ul/li/div/label/span[text()="%s"]/ancestor::li/ul/li/div/label[text() = "%s"]';

    /**
     * Locator for Advanced Select element
     *
     * @var string
     */
    protected $advancedSelect = '[data-role="advanced-select"]';

    /**
     * Get element data.
     *
     * @param ElementInterface $element
     * @return string
     */
    protected function getData(ElementInterface $element)
    {
        return trim($element->getValue(), chr(0xC2) . chr(0xA0));
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
