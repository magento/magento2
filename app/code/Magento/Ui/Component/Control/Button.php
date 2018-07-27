<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;

/**
 * Class Button
 */
class Button extends Template implements ControlInterface
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate($this->getTemplatePath());

        parent::_construct();
    }

    /**
     * Retrieve template path
     *
     * @return string
     */
    protected function getTemplatePath()
    {
        return 'Magento_Ui::control/button/default.phtml';
    }

    /**
     * Retrieve button type
     *
     * @return string
     */
    public function getType()
    {
        if (in_array($this->getData('type'), ['reset', 'submit'])) {
            return $this->getData('type');
        }

        return 'button';
    }

    /**
     * Retrieve attributes html
     *
     * @return string
     */
    public function getAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (empty($title)) {
            $title = $this->getLabel();
        }
        $classes = ['action-', 'scalable'];
        if ($this->hasData('class')) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        return $this->attributesToHtml($this->prepareAttributes($title, $classes, $disabled));
    }

    /**
     * Retrieve onclick handler
     *
     * @return string|null
     */
    public function getOnClick()
    {
        if ($this->hasData('on_click')) {
            return $this->getData('on_click');
        } else {
            $url = $this->hasData('url') ? $this->getData('url') : $this->getUrl();
            if (!empty($url)) {
                return sprintf("location.href = '%s';", $url);
            }

            return null;
        }
    }

    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function prepareAttributes($title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => implode(' ', $classes),
            'onclick' => $this->getOnClick(),
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled,
        ];
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }

        return $attributes;
    }

    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function attributesToHtml($attributes)
    {
        $html = '';
        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === null || $attributeValue == '') {
                continue;
            }
            $html .= $attributeKey . '="' . $this->escapeHtml($attributeValue) . '" ';
        }

        return $html;
    }
}
