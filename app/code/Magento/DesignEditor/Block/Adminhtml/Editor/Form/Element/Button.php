<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

/**
 * Form element button
 */
class Button extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Additional html attributes
     *
     * @var string[]
     */
    protected $_htmlAttributes = ['data-mage-init'];

    /**
     * Generate button html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        if ($this->getBeforeElementHtml()) {
            $html .= sprintf(
                '<label class="addbefore" for="%s">%s</label>',
                $this->getHtmlId(),
                $this->getBeforeElementHtml()
            );
        }
        $html .= sprintf(
            '<button id="%s" %s %s><span>%s</span></button>',
            $this->getHtmlId(),
            $this->_getUiId(),
            $this->serialize($this->getHtmlAttributes()),
            $this->getEscapedValue()
        );

        if ($this->getAfterElementHtml()) {
            $html .= sprintf(
                '<label class="addafter" for="%s">%s</label>',
                $this->getHtmlId(),
                $this->getBeforeElementHtml()
            );
        }
        return $html;
    }

    /**
     * Html attributes
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        $attributes = parent::getHtmlAttributes();
        return array_merge($attributes, $this->_htmlAttributes);
    }
}
