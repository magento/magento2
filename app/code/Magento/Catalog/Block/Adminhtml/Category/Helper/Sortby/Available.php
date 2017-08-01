<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml additional helper block for sort by
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\Available
 *
 * @since 2.0.0
 */
class Available extends \Magento\Framework\Data\Form\Element\Multiselect
{
    /**
     * Returns js code that is used instead of default toggle code for "Use default config" checkbox
     *
     * @return string
     * @since 2.0.0
     */
    public function getToggleCode()
    {
        $htmlId = 'use_config_' . $this->getHtmlId();
        return "toggleValueElements(this, this.parentNode.parentNode);" .
            "if (!this.checked) toggleValueElements(\$('{$htmlId}'), \$('{$htmlId}').parentNode);";
    }

    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        $elementDisabled = $this->getDisabled() == 'disabled';
        $disabled = false;

        if (!$this->getValue() || $elementDisabled) {
            $this->setData('disabled', 'disabled');
            $disabled = true;
        }

        $html = parent::getElementHtml();
        $htmlId = 'use_config_' . $this->getHtmlId();
        $html .= '<input id="' . $htmlId . '" name="use_config[]" value="' . $this->getId() . '"';
        $html .= $disabled ? ' checked="checked"' : '';

        if ($this->getReadonly() || $elementDisabled) {
            $html .= ' disabled="disabled"';
        }

        $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';
        $html .= ' <label for="' . $htmlId . '" class="normal">' . __('Use All Available Attributes') . '</label>';
        $html .= '<script>require(["prototype"], function(){toggleValueElements($(\'' .
            $htmlId .
            '\'), $(\'' .
            $htmlId .
            '\').parentNode);});</script>';

        return $html;
    }
}
