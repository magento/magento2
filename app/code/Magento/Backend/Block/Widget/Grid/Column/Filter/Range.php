<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Range grid column filter
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * @api
 * @since 100.0.2
 */
class Range extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * Return formatted HTML
     *
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
     * Return the value at the specified index
     *
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
     * @inheritDoc
     */
    public function getCondition()
    {
        $value = $this->getValue();
        return $value;
    }
}
