<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Fixed Product Tax.
 */
class Fpt extends SimpleElement
{
    /**
     * 'Add Fixed Product Tax' button selector.
     *
     * @var string
     */
    private $buttonFormLocator = '[data-action="add_new_row"]';

    /**
     * Fields mapping.
     *
     * @var array
     */
    private $fields = [
        'country' => [
            'type' => 'select',
            'selector' => '[name$="[country]"]',
        ],
        'website' => [
            'type' => 'select',
            'selector' => '[name$="[website_id]"]',
        ],
        'tax' => [
            'type' => 'input',
            'selector' => '[name$="[value]"]',
        ],
        'state' => [
            'type' => 'select',
            'selector' => '[name$="[state]"]',
        ],
    ];

    /**
     * Fill Fixed Product Tax form.
     *
     * @param string|array $value
     * @return void
     */
    public function setValue($value)
    {
        if ($this->find($this->buttonFormLocator)->isVisible()) {
            $this->find($this->buttonFormLocator)->click();
        }
        foreach ((array)$value as $name => $data) {
            $element = $this->find(
                $this->fields[$name]['selector'],
                Locator::SELECTOR_CSS,
                $this->fields[$name]['type']
            );

            if ($element->isVisible()) {
                $element->setValue($data);
            }
        }
    }
}
