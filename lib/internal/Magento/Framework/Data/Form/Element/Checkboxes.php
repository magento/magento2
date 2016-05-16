<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Form select element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * @param mixed $value
     * @return string|void
     */
    public function getChecked($value)
    {
        if ($checked = $this->getValue()) {
        } elseif ($checked = $this->getData('checked')) {
        } else {
            return;
        }
        if (!is_array($checked)) {
            $checked = [strval($checked)];
        } else {
            foreach ($checked as $k => $v) {
                $checked[$k] = strval($v);
            }
        }
        if (in_array(strval($value), $checked)) {
            return 'checked';
        }
        return;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getDisabled($value)
    {
        if ($disabled = $this->getData('disabled')) {
            if (!is_array($disabled)) {
                $disabled = [strval($disabled)];
            } else {
                foreach ($disabled as $k => $v) {
                    $disabled[$k] = strval($v);
                }
            }
            if (in_array(strval($value), $disabled)) {
                return 'disabled';
            }
        }
        return;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function getOnclick($value)
    {
        if ($onclick = $this->getData('onclick')) {
            return str_replace('$value', $value, $onclick);
        }
        return;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function getOnchange($value)
    {
        if ($onchange = $this->getData('onchange')) {
            return str_replace('$value', $value, $onchange);
        }
        return;
    }

    //    public function getName($value)
    //    {
    //        if ($name = $this->getData('name')) {
    //            return str_replace('$value', $value, $name);
    //        }
    //        return ;
    //    }

    /**
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
