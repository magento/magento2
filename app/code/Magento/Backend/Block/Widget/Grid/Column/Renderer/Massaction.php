<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Grid widget column renderer massaction
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Massaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox
{
    /**
     * @var int
     */
    protected $_defaultWidth = 20;

    /**
     * Render header of the row
     *
     * @return string
     */
    public function renderHeader()
    {
        return '&nbsp;';
    }

    /**
     * Render HTML properties
     *
     * @return string
     */
    public function renderProperty()
    {
        $out = parent::renderProperty();
        $out = preg_replace('/class=".*?"/i', '', $out);
        $out .= ' class="a-center"';
        return $out;
    }

    /**
     * Returns HTML of the object
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        if ($this->getColumn()->getGrid()->getMassactionIdFieldOnlyIndexValue()) {
            $this->setNoObjectId(true);
        }
        return parent::render($row);
    }

    /**
     * Returns HTML of the checkbox
     *
     * @param string $value
     * @param bool   $checked
     * @return string
     */
    protected function _getCheckboxHtml($value, $checked)
    {
        $html = '<input type="checkbox" name="' . $this->getColumn()->getName() . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" class="massaction-checkbox"' . $checked . '/>';
        return $html;
    }
}
