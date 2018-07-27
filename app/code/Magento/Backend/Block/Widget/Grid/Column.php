<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter;

/**
 * Grid column block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Column extends Widget
{
    /**
     * Parent grid
     *
     * @var \Magento\Backend\Block\Widget\Grid
     */
    protected $_grid;

    /**
     * Column renderer
     *
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
     */
    protected $_renderer;

    /**
     * Column filter
     *
     * @var AbstractFilter
     */
    protected $_filter;

    /**
     * Column css classes
     *
     * @var string|null
     */
    protected $_cssClass = null;

    /**
     * Renderer types
     *
     * @var array
     */
    protected $_rendererTypes = [
        'action' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Action',
        'button' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Button',
        'checkbox' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox',
        'concat' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Concat',
        'country' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Country',
        'currency' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency',
        'date' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Date',
        'datetime' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime',
        'default' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Text',
        'draggable-handle' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\DraggableHandle',
        'input' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Input',
        'massaction' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Massaction',
        'number' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Number',
        'options' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Options',
        'price' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Price',
        'radio' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio',
        'select' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Select',
        'store' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Store',
        'text' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Longtext',
        'wrapline' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Wrapline',
    ];

    /**
     * Filter types
     *
     * @var array
     */
    protected $_filterTypes = [
        'datetime' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Datetime',
        'date' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Date',
        'range' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Range',
        'number' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Range',
        'currency' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Range',
        'price' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Price',
        'country' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Country',
        'options' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Select',
        'massaction' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Massaction',
        'checkbox' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Checkbox',
        'radio' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Radio',
        'skip-list' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\SkipList',
        'store' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Store',
        'theme' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Theme',
        'default' => 'Magento\Backend\Block\Widget\Grid\Column\Filter\Text',
    ];

    /**
     * Column is grouped
     * @var bool
     */
    protected $_isGrouped = false;

    /**
     * @return void
     */
    public function _construct()
    {
        if ($this->hasData('grouped')) {
            $this->_isGrouped = (bool)$this->getData('grouped');
        }

        parent::_construct();
    }

    /**
     * Should column be displayed in grid
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return true;
    }

    /**
     * Set grid block to column
     *
     * @param \Magento\Backend\Block\Widget\Grid $grid
     * @return $this
     */
    public function setGrid($grid)
    {
        $this->_grid = $grid;
        // Init filter object
        $this->getFilter();
        return $this;
    }

    /**
     * Get grid block
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * Retrieve html id of filter
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getGrid()->getId() . '_' . $this->getGrid()->getVarNameFilter() . '_' . $this->getId();
    }

    /**
     * Get html code for column properties
     *
     * @return string
     */
    public function getHtmlProperty()
    {
        return $this->getRenderer()->renderProperty();
    }

    /**
     * Get Header html
     * @return string
     */
    public function getHeaderHtml()
    {
        return $this->getRenderer()->renderHeader();
    }

    /**
     * Get column css classes
     *
     * @return string
     */
    public function getCssClass()
    {
        if ($this->_cssClass === null) {
            if ($this->getAlign()) {
                $this->_cssClass .= 'a-' . $this->getAlign();
            }
            // Add a custom css class for column
            if ($this->hasData('column_css_class')) {
                $this->_cssClass .= ' ' . $this->getData('column_css_class');
            }
            if ($this->getEditable()) {
                $this->_cssClass .= ' editable';
            }
            $this->_cssClass .= ' col-' . $this->getId();
        }
        return $this->_cssClass;
    }

    /**
     * Get column css property
     *
     * @return string
     */
    public function getCssProperty()
    {
        return $this->getRenderer()->renderCss();
    }

    /**
     * Set is column sortable
     *
     * @param bool $value
     * @return void
     */
    public function setSortable($value)
    {
        $this->setData('sortable', $value);
    }

    /**
     * Get header css class name
     * @return string
     */
    public function getHeaderCssClass()
    {
        $class = $this->getData('header_css_class');
        $class .= false === $this->getSortable() ? ' no-link' : '';
        $class .= ' col-' . $this->getId();
        return $class;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getSortable()
    {
        return $this->hasData('sortable') ? (bool)$this->getData('sortable') : true;
    }

    /**
     * Add css class to column header
     *
     * @param string $className
     * @return void
     */
    public function addHeaderCssClass($className)
    {
        $classes = $this->getData('header_css_class') ? $this->getData('header_css_class') . ' ' : '';
        $this->setData('header_css_class', $classes . $className);
    }

    /**
     * Get header class names
     * @return string
     */
    public function getHeaderHtmlProperty()
    {
        $str = '';
        if ($class = $this->getHeaderCssClass()) {
            $str .= ' class="' . $class . '"';
        }

        return $str;
    }

    /**
     * Retrieve row column field value for display
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function getRowField(\Magento\Framework\DataObject $row)
    {
        $renderedValue = $this->getRenderer()->render($row);
        if ($this->getHtmlDecorators()) {
            $renderedValue = $this->_applyDecorators($renderedValue, $this->getHtmlDecorators());
        }

        /*
         * if column has determined callback for framing call
         * it before give away rendered value
         *
         * callback_function($renderedValue, $row, $column, $isExport)
         * should return new version of rendered value
         */
        $frameCallback = $this->getFrameCallback();
        if (is_array($frameCallback)) {
            $this->validateFrameCallback($frameCallback);
            $renderedValue = call_user_func($frameCallback, $renderedValue, $row, $this, false);
        }

        return $renderedValue;
    }

    /**
     * Validate frame callback.
     *
     * @param array $callback
     * @throws \InvalidArgumentException
     * @return void
     */
    private function validateFrameCallback(array $callback)
    {
        if (!is_object($callback[0]) || !$callback[0] instanceof Widget) {
            throw new \InvalidArgumentException(
                "Frame callback host must be instance of " . \Magento\Backend\Block\Widget::class
            );
        }
    }

    /**
     * Retrieve row column field value for export
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function getRowFieldExport(\Magento\Framework\DataObject $row)
    {
        $renderedValue = $this->getRenderer()->renderExport($row);

        /*
         * if column has determined callback for framing call
         * it before give away rendered value
         *
         * callback_function($renderedValue, $row, $column, $isExport)
         * should return new version of rendered value
         */
        $frameCallback = $this->getFrameCallback();
        if (is_array($frameCallback)) {
            $this->validateFrameCallback($frameCallback);
            $renderedValue = call_user_func($frameCallback, $renderedValue, $row, $this, true);
        }

        return $renderedValue;
    }

    /**
     * Retrieve Header Name for Export
     *
     * @return string
     */
    public function getExportHeader()
    {
        if ($this->getHeaderExport()) {
            return $this->getHeaderExport();
        }
        return $this->getHeader();
    }

    /**
     * Decorate rendered cell value
     *
     * @param string $value
     * @param array|string $decorators
     * @return string
     */
    protected function &_applyDecorators($value, $decorators)
    {
        if (!is_array($decorators)) {
            if (is_string($decorators)) {
                $decorators = explode(' ', $decorators);
            }
        }
        if (!is_array($decorators) || empty($decorators)) {
            return $value;
        }
        switch (array_shift($decorators)) {
            case 'nobr':
                $value = '<span class="nobr">' . $value . '</span>';
                break;
        }
        if (!empty($decorators)) {
            return $this->_applyDecorators($value, $decorators);
        }
        return $value;
    }

    /**
     * Set column renderer
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer $renderer
     * @return $this
     */
    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }

    /**
     * Set renderer type class name
     *
     * @param string $type type of renderer
     * @param string $className renderer class name
     * @return void
     */
    public function setRendererType($type, $className)
    {
        $this->_rendererTypes[$type] = $className;
    }

    /**
     * Get renderer class name by renderer type
     *
     * @return string
     */
    protected function _getRendererByType()
    {
        $type = strtolower($this->getType());
        $rendererClass = isset(
            $this->_rendererTypes[$type]
        ) ? $this->_rendererTypes[$type] : $this->_rendererTypes['default'];

        return $rendererClass;
    }

    /**
     * Retrieve column renderer
     *
     * @return \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
     */
    public function getRenderer()
    {
        if ($this->_renderer === null) {
            $rendererClass = $this->getData('renderer');
            if (empty($rendererClass)) {
                $rendererClass = $this->_getRendererByType();
            }
            $this->_renderer = $this->getLayout()->createBlock($rendererClass)->setColumn($this);
        }
        return $this->_renderer;
    }

    /**
     * Set column filter
     *
     * @param string $filterClass filter class name
     * @return void
     */
    public function setFilter($filterClass)
    {
        $filterBlock = $this->getLayout()->createBlock($filterClass);
        $filterBlock->setColumn($this);
        $this->_filter = $filterBlock;
    }

    /**
     * Set filter type class name
     *
     * @param string $type type of filter
     * @param string $className filter class name
     * @return void
     */
    public function setFilterType($type, $className)
    {
        $this->_filterTypes[$type] = $className;
    }

    /**
     * Get column filter class name by filter type
     *
     * @return string
     */
    protected function _getFilterByType()
    {
        $type = $this->getFilterType() ? strtolower($this->getFilterType()) : strtolower($this->getType());
        $filterClass = isset($this->_filterTypes[$type]) ? $this->_filterTypes[$type] : $this->_filterTypes['default'];

        return $filterClass;
    }

    /**
     * Get filter block
     *
     * @return AbstractFilter|false
     */
    public function getFilter()
    {
        if ($this->_filter === null) {
            $filterClass = $this->getData('filter');
            if (false === (bool)$filterClass && false === ($filterClass === null)) {
                return false;
            }
            if (!$filterClass) {
                $filterClass = $this->_getFilterByType();
                if ($filterClass === false) {
                    return false;
                }
            }
            $this->_filter = $this->getLayout()->createBlock($filterClass)->setColumn($this);
        }

        return $this->_filter;
    }

    /**
     * Get filter html code
     *
     * @return null|string
     */
    public function getFilterHtml()
    {
        $filter = $this->getFilter();
        $output = $filter ? $filter->getHtml() : '&nbsp;';
        return $output;
    }

    /**
     * Check if column is grouped
     *
     * @return bool
     */
    public function isGrouped()
    {
        return $this->_isGrouped;
    }
}
