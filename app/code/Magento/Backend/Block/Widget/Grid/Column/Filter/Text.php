<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Text grid column filter
 *
 * @api
 * @since 100.0.2
 */
class Text extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        $html = '<input type="text" name="' .
            $this->_getHtmlName() .
            '" id="' .
            $this->_getHtmlId() .
            '" value="' .
            $this->getEscapedValue() .
            '" class="input-text admin__control-text no-changes"' .
            $this->getUiId(
                'filter',
                $this->_getHtmlName()
            ) . ' />';
        return $html;
    }
}
