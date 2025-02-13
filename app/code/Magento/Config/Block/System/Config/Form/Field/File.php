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
declare(strict_types=1);

namespace Magento\Config\Block\System\Config\Form\Field;

class File extends \Magento\Framework\Data\Form\Element\File
{
    /**
     * Get element html
     *
     * @return string
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
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ((string)$this->getValue()) {
            $label = __('Delete File');
            $html .= '<div>' . $this->_escaper->escapeHtml($this->getValue()) . ' ';
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
