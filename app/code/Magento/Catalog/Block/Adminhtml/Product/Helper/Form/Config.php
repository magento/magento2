<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml additional helper block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

class Config extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Retrieve element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $value = $this->getValue();
        if ($value == '') {
            $this->setValue($this->_getValueFromConfig());
        }
        $html = parent::getElementHtml();

        $htmlId = 'use_config_' . $this->getHtmlId();
        $checked = $value == '' ? ' checked="checked"' : '';
        $disabled = $this->getReadonly() ? ' disabled="disabled"' : '';

        $html .= '<input id="' . $htmlId . '" name="product[' . $htmlId . ']" ' . $disabled . ' value="1" ' . $checked;
        $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';
        $html .= ' <label for="' . $htmlId . '">' . __('Use Config Settings') . '</label>';
        $html .= '<script>require(["prototype"], function(){toggleValueElements($(\'' .
            $htmlId .
            '\'), $(\'' .
            $htmlId .
            '\').parentNode);});</script>';

        return $html;
    }

    /**
     * Get config value data
     *
     * @return mixed
     */
    protected function _getValueFromConfig()
    {
        return '';
    }
}
