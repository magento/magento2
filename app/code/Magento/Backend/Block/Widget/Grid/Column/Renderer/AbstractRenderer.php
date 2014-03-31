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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Backend grid item abstract renderer
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractRenderer extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Backend\Block\Widget\Grid\Column\Renderer\RendererInterface
{
    /**
     * @var int
     */
    protected $_defaultWidth;

    /**
     * @var Column
     */
    protected $_column;

    /**
     * @param Column $column
     * @return $this
     */
    public function setColumn($column)
    {
        $this->_column = $column;
        return $this;
    }

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->_column;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Object $row
     * @return  string
     */
    public function render(\Magento\Object $row)
    {
        if ($this->getColumn()->getEditable()) {
            $value = $this->_getValue($row);
            return $value . ($this->getColumn()->getEditOnly() ? '' : ($value !=
                '' ? '' : '&nbsp;')) . $this->_getInputValueElement(
                    $row
                );
        }
        return $this->_getValue($row);
    }

    /**
     * Render column for export
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function renderExport(\Magento\Object $row)
    {
        return $this->render($row);
    }

    /**
     * @param \Magento\Object $row
     * @return mixed
     */
    protected function _getValue(\Magento\Object $row)
    {
        if ($getter = $this->getColumn()->getGetter()) {
            if (is_string($getter)) {
                return $row->{$getter}();
            } elseif (is_callable($getter)) {
                return call_user_func($getter, $row);
            }
            return '';
        }
        return $row->getData($this->getColumn()->getIndex());
    }

    /**
     * @param \Magento\Object $row
     * @return string
     */
    public function _getInputValueElement(\Magento\Object $row)
    {
        return '<input type="text" class="input-text ' .
            $this->getColumn()->getValidateClass() .
            '" name="' .
            $this->getColumn()->getId() .
            '" value="' .
            $this->_getInputValue(
                $row
            ) . '"/>';
    }

    /**
     * @param \Magento\Object $row
     * @return mixed
     */
    protected function _getInputValue(\Magento\Object $row)
    {
        return $this->_getValue($row);
    }

    /**
     * @return string
     */
    public function renderHeader()
    {
        if (false !== $this->getColumn()->getSortable()) {
            $className = 'not-sort';
            $dir = strtolower($this->getColumn()->getDir());
            $nDir = $dir == 'asc' ? 'desc' : 'asc';
            if ($this->getColumn()->getDir()) {
                $className = 'sort-arrow-' . $dir;
            }
            $out = '<a href="#" name="' .
                $this->getColumn()->getId() .
                '" title="' .
                $nDir .
                '" class="' .
                $className .
                '">' .
                '<label class="sort-title" for=' .
                $this->getColumn()->getHtmlId() .
                '>' .
                $this->getColumn()->getHeader() .
                '</label></a>';
        } else {
            $out = '<label for=' .
                $this->getColumn()->getHtmlId() .
                '>' .
                $this->getColumn()->getHeader() .
                '</label>';
        }
        return $out;
    }

    /**
     * @return string
     */
    public function renderProperty()
    {
        $out = '';
        $width = $this->_defaultWidth;

        if ($this->getColumn()->hasData('width')) {
            $customWidth = $this->getColumn()->getData('width');
            if (null === $customWidth || preg_match('/^[0-9]+%?$/', $customWidth)) {
                $width = $customWidth;
            } elseif (preg_match('/^([0-9]+)px$/', $customWidth, $matches)) {
                $width = (int)$matches[1];
            }
        }

        if (null !== $width) {
            $out .= ' width="' . $width . '"';
        }

        return $out;
    }

    /**
     * @return string
     */
    public function renderCss()
    {
        return $this->getColumn()->getCssClass();
    }
}
