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
class DateFieldsOrder extends Field
{
    /**
     * Return the HTML for this element
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $_options = ['d' => __('Day'), 'm' => __('Month'), 'y' => __('Year')];

        $element->setValues($_options)->setClass('select-date')->setName($element->getName() . '[]');
        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = [];
        }

        $_parts = [];
        $_parts[] = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $_parts[] = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        $_parts[] = $element->setValue(isset($values[2]) ? $values[2] : null)->getElementHtml();

        return implode(' <span>/</span> ', $_parts);
    }
}
