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

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Data\Form\Element\Factory;

class File extends \Magento\Framework\Data\Form\Element\File
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($factoryElement, $factoryCollection, $this->escaper, $data);
    }

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
            $html .= '<div>' . $this->escaper->escapeHtml($this->getValue()) . ' ';
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
