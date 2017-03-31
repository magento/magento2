<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Button;

/**
 * Split button widget
 *
 * @method array getOptions()
 * @method string getButtonClass()
 * @method string getClass()
 * @method string getLabel()
 * @method string getTitle()
 * @method bool getDisabled()
 * @method string getStyle()
 * @method array getDataAttribute()
 */
class SplitButton extends \Magento\Backend\Block\Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        if (!$this->hasTemplate()) {
            $this->setTemplate('Magento_Backend::widget/button/split.phtml');
        }
        parent::_construct();
    }

    /**
     * Retrieve <div> wrapper attributes html
     *
     * @return string
     */
    public function getAttributesHtml()
    {
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        if ($this->hasSplit()) {
            $classes[] = 'actions-split';
        }
        //@TODO Perhaps use $this->getClass() instead
        if ($this->getButtonClass()) {
            $classes[] = $this->getButtonClass();
        }

        $attributes = ['id' => $this->getId(), 'title' => $title, 'class' => join(' ', $classes)];

        $html = $this->_getAttributesString($attributes);

        return $html;
    }

    /**
     * Retrieve button attributes html
     *
     * @return string
     */
    public function getButtonAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        $classes[] = 'action-default';
        $classes[] = 'primary';
        // @TODO Perhaps use $this->getButtonClass() instead
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }
        $attributes = [
            'id' => $this->getId() . '-button',
            'title' => $title,
            'class' => join(' ', $classes),
            'disabled' => $disabled,
            'style' => $this->getStyle(),
        ];

        //TODO perhaps we need to skip data-mage-init when disabled="disabled"
        if ($this->getDataAttribute()) {
            $this->_getDataAttributes($this->getDataAttribute(), $attributes);
        }

        $html = $this->_getAttributesString($attributes);
        $html .= $this->getUiId();

        return $html;
    }

    /**
     * Retrieve toggle button attributes html
     *
     * @return string
     */
    public function getToggleAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        $classes[] = 'action-toggle';
        $classes[] = 'primary';
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        $attributes = ['title' => $title, 'class' => join(' ', $classes), 'disabled' => $disabled];
        $this->_getDataAttributes(['mage-init' => '{"dropdown": {}}', 'toggle' => 'dropdown'], $attributes);

        $html = $this->_getAttributesString($attributes);
        $html .= $this->getUiId('dropdown');

        return $html;
    }

    /**
     * Retrieve options attributes html
     *
     * @param string $key
     * @param array $option
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getOptionAttributesHtml($key, $option)
    {
        $disabled = isset($option['disabled']) && $option['disabled'] ? 'disabled' : '';
        if (isset($option['title'])) {
            $title = $option['title'];
        } else {
            $title = $option['label'];
        }
        $classes = [];
        $classes[] = 'item';
        if (!empty($option['default'])) {
            $classes[] = 'item-default';
        }
        if ($disabled) {
            $classes[] = $disabled;
        }
        $attributes = $this->_prepareOptionAttributes($option, $title, $classes, $disabled);
        $html = $this->_getAttributesString($attributes);
        $html .= $this->getUiId(isset($option['id']) ? $option['id'] : 'item' . '-' . $key);

        return $html;
    }

    /**
     * Checks if the button needs actions-split functionality
     *
     * If this function returns false then split button will be rendered as simple button
     *
     * @return bool
     */
    public function hasSplit()
    {
        return $this->hasData('has_split') ? (bool)$this->getData('has_split') : true;
    }

    /**
     * Add data attributes to $attributes array
     *
     * @param array $data
     * @param array &$attributes
     * @return void
     */
    protected function _getDataAttributes($data, &$attributes)
    {
        foreach ($data as $key => $attr) {
            $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
        }
    }

    /**
     * Prepare option attributes
     *
     * @param array $option
     * @param string $title
     * @param string $classes
     * @param string $disabled
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareOptionAttributes($option, $title, $classes, $disabled)
    {
        $attributes = [
            'id' => isset($option['id']) ? $this->getId() . '-' . $option['id'] : '',
            'title' => $title,
            'class' => join(' ', $classes),
            'onclick' => isset($option['onclick']) ? $option['onclick'] : '',
            'style' => isset($option['style']) ? $option['style'] : '',
            'disabled' => $disabled,
        ];

        if (isset($option['data_attribute'])) {
            $this->_getDataAttributes($option['data_attribute'], $attributes);
        }

        return $attributes;
    }

    /**
     * Render attributes array as attributes string
     *
     * @param array $attributes
     * @return string
     */
    protected function _getAttributesString($attributes)
    {
        $html = [];
        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === null || $attributeValue == '') {
                continue;
            }
            $html[] = $attributeKey . '="' . $this->escapeHtmlAttr($attributeValue, false) . '"';
        }
        return join(' ', $html);
    }
}
