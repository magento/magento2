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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grid column block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Column extends Mage_Adminhtml_Block_Widget
{
    protected $_grid;
    protected $_renderer;
    protected $_filter;
    protected $_type;
    protected $_cssClass=null;

    public function __construct($data=array())
    {
        parent::__construct($data);
    }

    public function setGrid($grid)
    {
        $this->_grid = $grid;
        // Init filter object
        $this->getFilter();
        return $this;
    }

    public function getGrid()
    {
        return $this->_grid;
    }

    public function isLast()
    {
        return $this->getId() == $this->getGrid()->getLastColumnId();
    }

    public function getHtmlProperty()
    {
        return $this->getRenderer()->renderProperty();
    }

    public function getHeaderHtml()
    {
        return $this->getRenderer()->renderHeader();
    }

    public function getCssClass()
    {
        if (is_null($this->_cssClass)) {
            if ($this->getAlign()) {
                $this->_cssClass .= 'a-'.$this->getAlign();
            }
            // Add a custom css class for column
            if ($this->hasData('column_css_class')) {
                $this->_cssClass .= ' '. $this->getData('column_css_class');
            }
            if ($this->getEditable()) {
                $this->_cssClass .= ' editable';
            }
        }

        return $this->_cssClass;
    }

    public function getCssProperty()
    {
        return $this->getRenderer()->renderCss();
    }

    public function getHeaderCssClass()
    {
        $class = $this->getData('header_css_class');
        if (($this->getSortable()===false) || ($this->getGrid()->getSortable()===false)) {
            $class .= ' no-link';
        }
        if ($this->isLast()) {
            $class .= ' last';
        }
        return $class;
    }

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

    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }

    protected function _getRendererByType()
    {
        $type = strtolower($this->getType());
        $renderers = $this->getGrid()->getColumnRenderers();

        if (is_array($renderers) && isset($renderers[$type])) {
            return $renderers[$type];
        }

        switch ($type) {
            case 'date':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date';
                break;
            case 'datetime':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime';
                break;
            case 'number':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number';
                break;
            case 'currency':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency';
                break;
            case 'price':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price';
                break;
            case 'country':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Country';
                break;
            case 'concat':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Concat';
                break;
            case 'action':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action';
                break;
            case 'options':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options';
                break;
            case 'checkbox':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox';
                break;
            case 'massaction':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Massaction';
                break;
            case 'radio':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Radio';
                break;
            case 'input':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input';
                break;
            case 'select':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select';
                break;
            case 'text':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Longtext';
                break;
            case 'store':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store';
                break;
            case 'wrapline':
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Wrapline';
                break;
            default:
                $rendererClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text';
                break;
        }
        return $rendererClass;
    }

    /**
     * Retrieve column renderer
     *
     * @return Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
     */
    public function getRenderer()
    {
        if (!$this->_renderer) {
            $rendererClass = $this->getData('renderer');
            if (!$rendererClass) {
                $rendererClass = $this->_getRendererByType();
            }
            $this->_renderer = $this->getLayout()->createBlock($rendererClass)
                ->setColumn($this);
        }
        return $this->_renderer;
    }

    public function setFilter($filterClass)
    {
        $this->_filter = $this->getLayout()->createBlock($filterClass)
                ->setColumn($this);
    }

    protected function _getFilterByType()
    {
        $type = strtolower($this->getType());
        $filters = $this->getGrid()->getColumnFilters();
        if (is_array($filters) && isset($filters[$type])) {
            return $filters[$type];
        }

        switch ($type) {
            case 'datetime':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Datetime';
                break;
            case 'date':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Date';
                break;
            case 'range':
            case 'number':
            case 'currency':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range';
                break;
            case 'price':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Price';
                break;
            case 'country':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Country';
                break;
            case 'options':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select';
                break;

            case 'massaction':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Massaction';
                break;

            case 'checkbox':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Checkbox';
                break;

            case 'radio':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Radio';
                break;
            case 'store':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Store';
                break;
            case 'theme':
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Theme';
                break;
            default:
                $filterClass = 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text';
                break;
        }
        return $filterClass;
    }

    public function getFilter()
    {
        if (!$this->_filter) {
            $filterClass = $this->getData('filter');
            if ($filterClass === false) {
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

    public function getFilterHtml()
    {
        if ($this->getFilter()) {
            return $this->getFilter()->getHtml();
        } else {
            return '&nbsp;';
        }
        return null;
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
}
