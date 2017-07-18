<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Action\Tab;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Tab on Product update attributes Form.
 */
class UpdateAttributeTab extends Tab
{
    /**
     * Change checkbox.
     *
     * @var string
     */
    private $changeCheckbox = [
        'selector' => './/./ancestor::div[contains(@class,"control")]'
            . '//input[@data-role="toggle-editability-all" or contains(@id, "toggle_")]',
        'strategy' => Locator::SELECTOR_XPATH,
        'input' => 'checkbox',
        'value' => 'Yes',
    ];

    /**
     * Fill data into fields in the container.
     *
     * @param array $fields
     * @param SimpleElement|null $contextElement
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $contextElement = null)
    {
        $context = ($contextElement === null) ? $this->_rootElement : $contextElement;
        $mapping = $this->dataMapping($fields);
        foreach ($mapping as $field) {
            $this->_fill([$this->changeCheckbox], $context->find($field['selector'], $field['strategy']));
            $this->_fill([$field], $context);
        }

        return $this;
    }
}
