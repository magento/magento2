<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Block\Adminhtml\Export;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class Filter
 * Filter for export grid
 */
class Filter extends Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'frontend_label' => [
            'selector' => 'input[name="frontend_label"]',
        ],
        'attribute_code' => [
            'selector' => 'input[name="attribute_code"]',
        ],
    ];

    /**
     * Locator for export attribute.
     *
     * @var string
     */
    private $attribute = '[name="export_filter[%s]"]';

    /**
     * Locator for "Continue" button.
     *
     * @var string
     */
    private $continueButton = 'button.action-.scalable';

    /**
     * Return row with given attribute label.
     *
     * @param string $attributeLabel
     * @return \Magento\Mtf\Client\Element\SimpleElement
     */
    public function getGridRow($attributeLabel)
    {
        return $this->search(['frontend_label' => $attributeLabel]);
    }

    /**
     * Click on "Continue" button.
     *
     * @return void
     */
    public function clickContinue()
    {
        $this->_rootElement->find($this->continueButton)->click();
    }

    /**
     * Set attribute entity value.
     *
     * @param string $attribute
     * @param string $value
     * @return void
     */
    public function setAttributeValue($attribute, $value)
    {
        $this->_rootElement->find(sprintf($this->attribute, $attribute))->setValue($value);
    }
}
