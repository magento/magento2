<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Range grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

class Range extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * @return string
     */
    public function getHtml()
    {
        $html = '<div class="range"><div class="range-line">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[from]" id="' .
            $this->_getHtmlId() .
            '_from" placeholder="' .
            __(
                'From'
            ) . '" value="' . $this->getEscapedValue(
                'from'
            ) . '" class="input-text admin__control-text no-changes" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'from'
            ) . '/></div>';
        $html .= '<div class="range-line">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[to]" id="' .
            $this->_getHtmlId() .
            '_to" placeholder="' .
            __(
                'To'
            ) . '" value="' . $this->getEscapedValue(
                'to'
            ) . '" class="input-text admin__control-text no-changes" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'to'
            ) . '/></div></div>';
        return $html;
    }

    /**
     * @param string|null $index
     * @return mixed
     */
    public function getValue($index = null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if (isset($value['from']) && strlen($value['from']) > 0 || isset($value['to']) && strlen($value['to']) > 0) {
            return $value;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        $value = $this->getValue();
        return $value;
    }
}
