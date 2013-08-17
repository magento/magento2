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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grid column block
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Widget_Grid_Column extends Mage_Backend_Block_Widget
{
    /**
     * Parent grid
     *
     * @var Mage_Backend_Block_Widget_Grid
     */
    protected $_grid;

    /**
     * Column renderer
     *
     * @var Mage_Backend_Block_Widget_Grid_Column_Renderer_Abstract
     */
    protected $_renderer;

    /**
     * Column filter
     *
     * @var Mage_Backend_Block_Widget_Grid_Column_Filter_Abstract
     */
    protected $_filter;

    /**
     * Column css classes
     *
     * @var string|null
     */
    protected $_cssClass=null;

    /**
     * Renderer types
     *
     * @var array
     */
    protected $_rendererTypes = array(
        'action'           => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Action',
        'button'           => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Button',
        'checkbox'         => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Checkbox',
        'concat'           => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Concat',
        'country'          => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Country',
        'currency'         => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Currency',
        'date'             => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Date',
        'datetime'         => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Datetime',
        'default'          => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Text',
        'draggable-handle' => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_DraggableHandle',
        'input'            => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Input',
        'massaction'       => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Massaction',
        'number'           => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Number',
        'options'          => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Options',
        'price'            => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Price',
        'radio'            => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Radio',
        'select'           => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Select',
        'store'            => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Store',
        'text'             => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Longtext',
        'wrapline'         => 'Mage_Backend_Block_Widget_Grid_Column_Renderer_Wrapline',
    );

    /**
     * Filter types
     *
     * @var array
     */
    protected $_filterTypes = array(
        'datetime' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Datetime',
        'date' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Date',
        'range' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Range',
        'number' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Range',
        'currency' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Range',
        'price' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Price',
        'country' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Country',
        'options' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Select',
        'massaction' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Massaction',
        'checkbox' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Checkbox',
        'radio' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Radio',
        'skip-list' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_SkipList',
        'store' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Store',
        'theme' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Theme',
        'default' => 'Mage_Backend_Block_Widget_Grid_Column_Filter_Text',
    );

    /**
     * Column is grouped
     * @var bool
     */
    protected $_isGrouped = false;

    public function _construct()
    {
        if ($this->hasData('grouped')) {
            $this->_isGrouped = (bool) $this->getData('grouped');
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
     * @param Mage_Backend_Block_Widget_Grid $grid
     * @return Mage_Backend_Block_Widget_Grid_Column
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
     * @return Mage_Backend_Block_Widget_Grid
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
        return $this->getGrid()->getId() . '_'
            . $this->getGrid()->getVarNameFilter() . '_'
            . $this->getId();
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
     * @param boolean $value
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
     */
    public function getSortable()
    {
        return $this->hasData('sortable') ? (bool) $this->getData('sortable') : true;
    }

    /**
     * Add css class to column header
     * @param $className
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
            $str.= ' class="'.$class.'"';
        }

        return $str;
    }

    /**
     * Retrieve row column field value for display
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function getRowField(Varien_Object $row)
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
            $renderedValue = call_user_func($frameCallback, $renderedValue, $row, $this, false);
        }

        return $renderedValue;
    }

    /**
     * Retrieve row column field value for export
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function getRowFieldExport(Varien_Object $row)
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
        if ((!is_array($decorators)) || empty($decorators)) {
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
     * @param Mage_Backend_Block_Widget_Grid_Column_Renderer_Abstract $renderer
     * @return Mage_Backend_Block_Widget_Grid_Column
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
        $rendererClass = (isset($this->_rendererTypes[$type])) ?
            $this->_rendererTypes[$type] :
            $this->_rendererTypes['default'];

        return $rendererClass;
    }

    /**
     * Retrieve column renderer
     *
     * @return Mage_Backend_Block_Widget_Grid_Column_Renderer_Abstract
     */
    public function getRenderer()
    {
        if (is_null($this->_renderer)) {
            $rendererClass = $this->getData('renderer');
            if (empty($rendererClass)) {
                $rendererClass = $this->_getRendererByType();
            }
            $this->_renderer = $this->getLayout()->createBlock($rendererClass)
                ->setColumn($this);
        }
        return $this->_renderer;
    }

    /**
     * Set column filter
     *
     * @param string $filterClass filter class name
     */
    public function setFilter($filterClass)
    {
        $filterBlock = $this->getLayout()->createBlock($filterClass);
        $filterBlock->setColumn($this);
        $this->_filter = $filterBlock;
    }

    /**
     * Set filter type class name
     * @param string $type type of filter
     * @param string $className filter class name
     */
    public function setFilterType($type, $className)
    {
        $this->_filterTypes[$type] = $className;
    }

    /**
     * Get column filter class name by filter type
     *
     * @return mixed
     */
    protected function _getFilterByType()
    {
        $type = strtolower($this->getType());
        $filterClass = (isset($this->_filterTypes[$type])) ?
            $this->_filterTypes[$type] :
            $this->_filterTypes['default'];

        return $filterClass;
    }

    /**
     * Get filter block
     *
     * @return Mage_Backend_Block_Widget_Grid_Column_Filter_Abstract|bool
     */
    public function getFilter()
    {
        if (is_null($this->_filter)) {
            $filterClass = $this->getData('filter');
            if (false === (bool) $filterClass && false === is_null($filterClass)) {
                return false;
            }
            if (!$filterClass) {
                $filterClass = $this->_getFilterByType();
                if ($filterClass === false) {
                    return false;
                }
            }
            $this->_filter = $this->getLayout()->createBlock($filterClass)
                ->setColumn($this);
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
