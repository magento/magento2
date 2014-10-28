<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
