<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Form select element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Select extends AbstractElement
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
        $this->setType('select');
        $this->setExtType('combobox');
        $this->_prepareOptions();
    }

    /**
     * Get the element Html.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('select admin__control-select');

        $html = '';
        if ($this->getBeforeElementHtml()) {
            $html .= '<label class="addbefore" for="' .
                $this->getHtmlId() .
                '">' .
                $this->getBeforeElementHtml() .
                '</label>';
        }

        $html .= '<select id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId() . '>' . "\n";

        $value = $this->getValue();
        if (!is_array($value)) {
            $value = [$value];
        }

        if ($values = $this->getValues()) {
            foreach ($values as $key => $option) {
                if (!is_array($option)) {
                    $html .= $this->_optionToHtml(['value' => $key, 'label' => $option], $value);
                } elseif (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $groupItem) {
                        $html .= $this->_optionToHtml($groupItem, $value);
                    }
                    $html .= '</optgroup>' . "\n";
                } else {
                    $html .= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html .= '</select>' . "\n";
        if ($this->getAfterElementHtml()) {
            $html .= '<label class="addafter" for="' .
                $this->getHtmlId() .
                '">' .
                "\n{$this->getAfterElementHtml()}\n" .
                '</label>' .
                "\n";
        }
        return $html;
    }

    /**
     * Format an option as Html
     *
     * @param array $option
     * @param array $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option['value'])) {
            $html = '<optgroup label="' . $option['label'] . '">' . "\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }
            $html .= '</optgroup>' . "\n";
        } else {
            $html = '<option value="' . $this->_escape($option['value']) . '"';
            $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
            $html .= isset($option['style']) ? 'style="' . $option['style'] . '"' : '';
            if (in_array($option['value'], $selected)) {
                $html .= ' selected="selected"';
            }
            $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        }
        return $html;
    }

    /**
     * Prepare options.
     *
     * @return void
     */
    protected function _prepareOptions()
    {
        $values = $this->getValues();
        if (empty($values)) {
            $options = $this->getOptions();
            if (is_array($options)) {
                $values = [];
                foreach ($options as $value => $label) {
                    $values[] = ['value' => $value, 'label' => $label];
                }
            } elseif (is_string($options)) {
                $values = [['value' => $options, 'label' => $options]];
            }
            $this->setValues($values);
        }
    }

    /**
     * Get the Html attributes.
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return [
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'readonly',
            'tabindex',
            'data-form-part',
            'data-role',
            'data-action'
        ];
    }
}
