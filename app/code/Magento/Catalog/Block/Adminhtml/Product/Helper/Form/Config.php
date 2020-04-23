<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml additional helper block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Config extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

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
        $html .= ' class="checkbox" type="checkbox" />';
        $html .= ' <label for="' . $htmlId . '">' . __('Use Config Settings') . '</label>';
        $scriptString = 'require(["prototype"], function(){toggleValueElements($(\'' .
            $htmlId .
            '\'), $(\'' .
            $htmlId .
            '\').parentNode);});';
        $html .= /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptString, false);
        $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            "toggleValueElements($('#' . $htmlId), $('#' . $htmlId).parentNode);",
            '#' . $htmlId
        );

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
