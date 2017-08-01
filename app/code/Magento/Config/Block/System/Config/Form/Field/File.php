<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * File config field renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Block\System\Config\Form\Field;

/**
 * Class \Magento\Config\Block\System\Config\Form\Field\File
 *
 * @since 2.0.0
 */
class File extends \Magento\Framework\Data\Form\Element\File
{
    /**
     * Get element html
     *
     * @return string
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $html .= $this->_getDeleteCheckbox();
        return $html;
    }

    /**
     * Get html for additional delete checkbox field
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ((string)$this->getValue()) {
            $label = __('Delete File');
            $html .= '<div>' . $this->getValue() . ' ';
            $html .= '<input type="checkbox" name="' .
                parent::getName() .
                '[delete]" value="1" class="checkbox" id="' .
                $this->getHtmlId() .
                '_delete"' .
                ($this->getDisabled() ? ' disabled="disabled"' : '') .
                '/>';
            $html .= '<label for="' .
                $this->getHtmlId() .
                '_delete"' .
                ($this->getDisabled() ? ' class="disabled"' : '') .
                '> ' .
                $label .
                '</label>';
            $html .= '<input type="hidden" name="' .
                parent::getName() .
                '[value]" value="' .
                $this->getValue() .
                '" />';
            $html .= '</div>';
        }
        return $html;
    }
}
