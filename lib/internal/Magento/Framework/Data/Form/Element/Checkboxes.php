<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Form select element
 */
class Checkboxes extends AbstractElement
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
        $this->setType('checkbox');
        $this->setExtType('checkboxes');
    }

    /**
     * Retrieve allow attributes
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return [
            'type',
            'name',
            'class',
            'style',
            'checked',
            'onclick',
            'onchange',
            'disabled',
            'data-role',
            'data-action'
        ];
    }

    /**
     * Prepare value list
     *
     * @return array
     */
    protected function _prepareValues()
    {
        $options = [];
        $values = [];

        if ($this->getValues()) {
            if (!is_array($this->getValues())) {
                $options = [$this->getValues()];
            } else {
                $options = $this->getValues();
            }
        } elseif ($this->getOptions() && is_array($this->getOptions())) {
            $options = $this->getOptions();
        }
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                if (isset($v['value'])) {
                    if (!isset($v['label'])) {
                        $v['label'] = $v['value'];
                    }
                    $values[] = ['label' => $v['label'], 'value' => $v['value']];
                }
            } else {
                $values[] = ['label' => $v, 'value' => $k];
            }
        }

        return $values;
    }

    /**
     * Retrieve HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $values = $this->_prepareValues();

        if (!$values) {
            return '';
        }

        $html = '<div class=nested>';
        foreach ($values as $value) {
            $html .= $this->_optionToHtml($value);
        }
        $html .= '</div>' . $this->getAfterElementHtml();

        return $html;
    }

    /**
     * Was given value selected?
     *
     * @param string $value
     * @return string|null
     */
    public function getChecked($value)
    {
        $checked = $this->getValue() ?? $this->getData('checked');
        if (!$checked) {
            return null;
        }
        if (!is_array($checked)) {
            $checked = [(string)$checked];
        } else {
            foreach ($checked as $k => $v) {
                $checked[$k] = (string)$v;
            }
        }
        if (in_array((string)$value, $checked)) {
            return 'checked';
        }
        return null;
    }

    /**
     * Was value disabled for selection?
     *
     * @param string $value
     * @return string|null
     */
    public function getDisabled($value)
    {
        if ($disabled = $this->getData('disabled')) {
            if (!is_array($disabled)) {
                $disabled = [(string)$disabled];
            } else {
                foreach ($disabled as $k => $v) {
                    $disabled[$k] = (string)$v;
                }
            }
            if (in_array((string)$value, $disabled)) {
                return 'disabled';
            }
        }
        return null;
    }

    /**
     * Get onclick event handler.
     *
     * @param string $value
     * @return string|null
     */
    public function getOnclick($value = '$value')
    {
        if ($onclick = $this->getData('onclick')) {
            return str_replace('$value', $value, $onclick);
        }
        return null;
    }

    /**
     * Get onchange event handler.
     *
     * @param string $value
     * @return string|null
     */
    public function getOnchange($value = '$value')
    {
        if ($onchange = $this->getData('onchange')) {
            return str_replace('$value', $value, $onchange);
        }
        return null;
    }

    /**
     * Render a checkbox.
     *
     * @param array $option
     * @return string
     */
    protected function _optionToHtml($option)
    {
        $id = $this->getHtmlId() . '_' . $this->_escape($option['value']);

        $html = '<div class="field choice admin__field admin__field-option"><input id="' . $id . '"';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                $html .= ' ' . $attribute . '="' . $value . '" class="admin__control-checkbox"';
            }
        }
        $html .= ' value="' .
            $option['value'] .
            '" />' .
            ' <label for="' .
            $id .
            '" class="admin__field-label"><span>' .
            $option['label'] .
            '</span></label></div>' .
            "\n";
        return $html;
    }
}
