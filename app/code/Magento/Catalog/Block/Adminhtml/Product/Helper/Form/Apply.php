<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Attribute form apply element
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Apply extends \Magento\Framework\Data\Form\Element\Multiselect
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * Return html of the element.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $elementAttributeHtml = '';

        if ($this->getReadonly()) {
            $elementAttributeHtml = $elementAttributeHtml . ' readonly="readonly"';
        }

        if ($this->getDisabled()) {
            $elementAttributeHtml = $elementAttributeHtml . ' disabled="disabled"';
        }

        $html = '<select id="' . $this->getHtmlId() . '"' . $elementAttributeHtml . '>'
            . '<option value="0">' . $this->getModeLabels('all') . '</option>'
            . '<option value="1" ' . ($this->getValue() == null ? '' : 'selected') . '>'
            . $this->getModeLabels('custom') . '</option>' . '</select><br /><br />';

        $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
            'onchange',
            "toggleApplyVisibility(this)",
            'select#' . $this->getHtmlId()
        );

        $html .= parent::getElementHtml();

        return $html;
    }

    /**
     * Dublicate interface of \Magento\Framework\Data\Form\Element\AbstractElement::setReadonly
     *
     * @param bool $readonly
     * @param bool $useDisabled
     * @return $this
     */
    public function setReadonly($readonly, $useDisabled = false)
    {
        $this->setData('readonly', $readonly);
        $this->setData('disabled', $useDisabled);
        return $this;
    }
}
