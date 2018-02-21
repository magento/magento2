<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\Type;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\AbstractOptions;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Form "Option dropdown" on tab product "Customizable Options".
 */
class DropDown extends AbstractOptions
{
    /**
     * "Add Value" button css selector.
     *
     * @var string
     */
    protected $addValueButton = '[data-action="add_new_row"]';

    /**
     * Fill the form.
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return $this
     */
    public function fillOptions(array $fields, SimpleElement $element = null)
    {
        $actionType = isset($fields['action_type']) ? $fields['action_type'] : Options::ACTION_ADD;
        unset($fields['action_type']);

        if ($actionType == Options::ACTION_ADD) {
            $this->_rootElement->find($this->addValueButton)->click();
        }

        return parent::fillOptions($fields, $element);
    }
}
