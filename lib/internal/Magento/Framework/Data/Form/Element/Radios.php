<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Radio buttons collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

class Radios extends AbstractElement
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
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('radios');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $value = $this->getValue();
        if ($values = $this->getValues()) {
            foreach ($values as $option) {
                $html .= $this->_optionToHtml($option, $value);
            }
        }
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @param array $option
     * @param array $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $html = '<div class="admin__field admin__field-option">' .
            '<input type="radio"' . $this->getRadioButtonAttributes($option);
        if (is_array($option)) {
            $html .= 'value="' . $this->_escape(
                $option['value']
            ) . '" class="admin__control-radio" id="' . $this->getHtmlId() . $option['value'] . '"';
            if ($option['value'] == $selected) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="admin__field-label" for="' .
                $this->getHtmlId() .
                $option['value'] .
                '"><span>' .
                $option['label'] .
                '</span></label>';
        } elseif ($option instanceof \Magento\Framework\DataObject) {
            $html .= 'id="' . $this->getHtmlId() . $option->getValue() . '"' . $option->serialize(
                ['label', 'title', 'value', 'class', 'style']
            );
            if (in_array($option->getValue(), $selected)) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="inline" for="' .
                $this->getHtmlId() .
                $option->getValue() .
                '">' .
                $option->getLabel() .
                '</label>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * @return array
     */
    public function getHtmlAttributes()
    {
        return array_merge(parent::getHtmlAttributes(), ['name']);
    }

    /**
     * @param array $option
     * @return string
     */
    protected function getRadioButtonAttributes($option)
    {
        $html = '';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                $html .= ' ' . $attribute . '="' . $value . '" ';
            }
        }
        return $html;
    }
}
