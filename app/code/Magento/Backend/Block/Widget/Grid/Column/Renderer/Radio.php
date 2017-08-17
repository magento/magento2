<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Grid radiogroup column renderer
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 */
class Radio extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var int
     */
    protected $_defaultWidth = 55;

    /**
     * @var array
     */
    protected $_values;

    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $_converter;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_converter = $converter;
    }

    /**
     * Prepare data for renderer
     *
     * @return array
     */
    protected function _getValues()
    {
        $values = $this->getColumn()->getValues();
        return $this->_converter->toFlatArray($values);
    }

    /**
     * Returns all values for the column
     *
     * @return array
     */
    public function getValues()
    {
        if ($this->_values === null) {
            $this->_values = $this->getColumn()->getData('values') ? $this->getColumn()->getData('values') : [];
        }
        return $this->_values;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $values = $this->_getValues();
        $value = $row->getData($this->getColumn()->getIndex());
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        } else {
            $checked = $value === $this->getColumn()->getValue() ? ' checked="checked"' : '';
        }
        $html = '<input type="radio" name="' . $this->getColumn()->getHtmlName() . '" ';
        $html .= 'value="' . $row->getId() . '" class="radio"' . $checked . '/>';
        return $html;
    }
}
