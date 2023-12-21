<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Form\Renderer\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Catalog Custom Options Config Renderer
 */
class YearRange extends Field
{
    /**
     * Return the HTML for this element
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setStyle('width:70px;')->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = [];
        }

        $from = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $to = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        return '<label class="label"><span>' . __('from') . '</span></label>'
        . $from .
        '<label class="label"><span>' . __('to') . '</span></label>'
        . $to;
    }
}
