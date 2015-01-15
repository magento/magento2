<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Object;

/**
 * Backend grid item abstract renderer
 */
abstract class AbstractRenderer extends \Magento\Backend\Block\AbstractBlock implements RendererInterface
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
     * @param   Object $row
     * @return  string
     */
    public function render(Object $row)
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
     * @param Object $row
     * @return string
     */
    public function renderExport(Object $row)
    {
        return $this->render($row);
    }

    /**
     * @param Object $row
     * @return mixed
     */
    protected function _getValue(Object $row)
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
     * @param Object $row
     * @return string
     */
    public function _getInputValueElement(Object $row)
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
     * @param Object $row
     * @return mixed
     */
    protected function _getInputValue(Object $row)
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
