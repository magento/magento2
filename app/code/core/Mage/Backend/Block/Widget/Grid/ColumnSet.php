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
 *
 * @category    Mage
 * @package     Mage_Core
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_Widget_Grid_ColumnSet extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Core_Helper_Abstract
     */
    protected $_helper;

    /**
     * @var Mage_Backend_Model_Widget_Grid_Row_UrlGenerator
     */
    protected $_rowUrlGenerator;

    /**
     * Column headers visibility
     *
     * @var boolean
     */
    protected $_headersVisibility = true;

    /**
     * Filter visibility
     *
     * @var boolean
     */
    protected $_filterVisibility = true;

    /**
     * Empty grid text
     *
     * @var string|null
     */
    protected $_emptyText;

    /****
     * Empty grid text CSS class
     *
     * @var string|null
     */
    protected $_emptyTextCss    = 'a-center';

    /**
     * Label for empty cell
     *
     * @var string
     */
    protected $_emptyCellLabel = '';

    /**
     * Count subtotals
     *
     * @var boolean
     */
    protected $_countSubTotals = false;

    /**
     * Count totals
     *
     * @var boolean
     */
    protected $_countTotals = false;

    /**
     * Columns to group by
     *
     * @var array
     */
    protected $_groupedColumn = array();

    /*
     * @var boolean
     */
    protected $_isCollapsed;

    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Mage_Backend::widget/grid/column_set.phtml';

    /**
     * @var Mage_Backend_Model_Widget_Grid_SubTotals
     */
    protected $_subTotals = null;

    /**
     * @var Mage_Backend_Model_Widget_Grid_Totals
     */
    protected $_totals = null;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Magento_Filesystem $filesystem
     * @param Mage_Backend_Helper_Data $helper
     * @param Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory $generatorFactory
     * @param Mage_Backend_Model_Widget_Grid_SubTotals $subtotals
     * @param Mage_Backend_Model_Widget_Grid_Totals $totals
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Magento_Filesystem $filesystem,
        Mage_Backend_Helper_Data $helper,
        Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory $generatorFactory,
        Mage_Backend_Model_Widget_Grid_SubTotals $subtotals,
        Mage_Backend_Model_Widget_Grid_Totals $totals,
        array $data = array()
    ) {
        $this->_helper = $helper;

        $generatorClassName = 'Mage_Backend_Model_Widget_Grid_Row_UrlGenerator';
        if (isset($data['rowUrl'])) {
            $rowUrlParams = $data['rowUrl'];
            if (isset($rowUrlParams['generatorClass'])) {
                $generatorClassName = $rowUrlParams['generatorClass'];
            }
            $this->_rowUrlGenerator
                = $generatorFactory->createUrlGenerator($generatorClassName, array('args' => $rowUrlParams));
        }

        $this->setFilterVisibility(
            array_key_exists('filter_visibility', $data) ? (bool) $data['filter_visibility'] : true
        );

        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $filesystem, $data);

        $this->setEmptyText($this->_helper->__(
            isset($data['empty_text'])? $data['empty_text'] : 'No records found.'
        ));

        $this->setEmptyCellLabel($this->_helper->__(
            isset($data['empty_cell_label'])? $data['empty_cell_label'] : 'No records found.'
        ));

        $this->setCountSubTotals(isset($data['count_subtotals'])? (bool) $data['count_subtotals'] : false);
        $this->_subTotals = $subtotals;

        $this->setCountTotals(isset($data['count_totals'])? (bool) $data['count_totals'] : false);
        $this->_totals = $totals;
    }

    /**
     * Retrieve the list of columns
     *
     * @return array
     */
    public function getColumns()
    {
        $columns = $this->getLayout()->getChildBlocks($this->getNameInLayout());
        foreach ($columns as $key => $column) {
            if (!$column->isDisplayed()) {
                unset($columns[$key]);
            }
        }
        return $columns;
    }

    /**
     * Count columns
     *
     * @return int
     */
    public function getColumnCount()
    {
        return count($this->getColumns());
    }

    /**
     * Set sortability flag for columns
     *
     * @param bool $value
     * @return Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    public function setSortable($value)
    {
        if ($value === false) {
            foreach ($this->getColumns() as $column) {
                $column->setSortable(false);
            }
        }
        return $this;
    }

    /**
     * Set custom renderer type for columns
     *
     * @param string $type
     * @param string $className
     * @return Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    public function setRendererType($type, $className)
    {
        foreach ($this->getColumns() as $column) {
            $column->setRendererType($type, $className);
        }
        return $this;
    }

    /**
     * Set custom filter type for columns
     *
     * @param string $type
     * @param string $className
     * @return Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    public function setFilterType($type, $className)
    {
        foreach ($this->getColumns() as $column) {
            $column->setFilterType($type, $className);
        }
        return $this;
    }

    /**
     * Prepare block for rendering
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $columns = $this->getColumns();
        foreach ($columns as $columnId => $column) {
            $column->setId($columnId);
            $column->setGrid($this->getGrid());
            if ($column->isGrouped()) {
                $this->isColumnGrouped($column->getIndex(), true);
            }
        }
        $last = array_pop($columns);
        if ($last) {
            $last->addHeaderCssClass('last');
        }
    }

    /**
     * Return row url for js event handlers
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getRowUrl($item)
    {
        $url = '#';
        if (null !== $this->_rowUrlGenerator) {
            $url = $this->_rowUrlGenerator->getUrl($item);
        }
        return $url;
    }

    /**
     * Get children of specified item
     *
     * @param Varien_Object $item
     * @return array
     */
    public function getMultipleRows($item)
    {
        return $item->getChildren();
    }

    /**
     * Has children of specified item
     *
     * @param Varien_Object $item
     * @return bool
     */
    public function hasMultipleRows($item)
    {
        return $item->hasChildren() && count($item->getChildren()) > 0;
    }

    /**
     * Retrieve columns for multiple rows
     * @return array
     */
    public function getMultipleRowColumns()
    {
        $columns = $this->getColumns();
        foreach ($this->_groupedColumn as $column) {
            unset($columns[$column]);
        }
        return $columns;
    }

    /**
     * Check whether subtotal should be rendered
     *
     * @param Varien_Object $item
     * @return boolean
     */
    public function shouldRenderSubTotal($item)
    {
        return ($this->getCountSubTotals() &&
            count($this->getMultipleRows($item)) > 0
        );
    }

    /**
     * Check whether total should be rendered
     *
     * @return boolean
     */
    public function shouldRenderTotal()
    {
        return ($this->getCountTotals() &&
            count($this->getCollection()) > 0
        );
    }

    /**
     * Retrieve rowspan number
     *
     * @param Varien_Object $item
     * @param Mage_Backend_Block_Widget_Grid_Column $column
     * @return integer|boolean
     */
    public function getRowspan($item, $column)
    {
        if ($this->isColumnGrouped($column)) {
            return count($this->getMultipleRows($item)) + count($this->_groupedColumn) - 1
                + (int)$this->shouldRenderSubTotal($item);
        }
        return false;
    }

    /**
     * Check whether given column is grouped
     *
     * @param string|object $column
     * @param string $value
     * @return boolean|Mage_Backend_Block_Widget_Grid
     */
    public function isColumnGrouped($column, $value = null)
    {
        if (null === $value) {
            if (is_object($column)) {
                return in_array($column->getIndex(), $this->_groupedColumn);
            }
            return in_array($column, $this->_groupedColumn);
        }
        $this->_groupedColumn[] = $column;
        return $this;
    }

    /**
     * Check whether should render empty cell
     *
     * @param Varien_Object $item
     * @param Mage_Backend_Block_Widget_Grid_Column $column
     * @return boolean
     */
    public function shouldRenderEmptyCell($item, $column)
    {
        return ($item->getIsEmpty() && in_array($column['index'], $this->_groupedColumn));
    }

    /**
     * Retrieve colspan for empty cell
     *
     * @return int
     */
    public function getEmptyCellColspan()
    {
        return $this->getColumnCount() - count($this->_groupedColumn);
    }

    /**
     * Check whether should render cell
     *
     * @param Varien_Object $item
     * @param Mage_Backend_Block_Widget_Grid_Column $column
     * @return boolean
     */
    public function shouldRenderCell($item, $column)
    {
        if ($this->isColumnGrouped($column) && $item->getIsEmpty()) {
            return true;
        }
        if (!$item->getIsEmpty()) {
            return true;
        }
        return false;
    }

    /**
     * Set visibility of column headers
     *
     * @param boolean $visible
     */
    public function setHeadersVisibility($visible = true)
    {
        $this->_headersVisibility = $visible;
    }

    /**
     * Return visibility of column headers
     *
     * @return boolean
     */
    public function isHeaderVisible()
    {
        return $this->_headersVisibility;
    }

    /**
     * Set visibility of filter
     *
     * @param boolean $visible
     */
    public function setFilterVisibility($visible = true)
    {
        $this->_filterVisibility = $visible;
    }

    /**
     * Return visibility of filter
     *
     * @return boolean
     */
    public function isFilterVisible()
    {
        return $this->_filterVisibility;
    }

    /**
     * Set empty text CSS class
     *
     * @param string $cssClass
     * @return Mage_Backend_Block_Widget_Grid
     */
    public function setEmptyTextClass($cssClass)
    {
        $this->_emptyTextCss = $cssClass;
        return $this;
    }

    /**
     * Return empty text CSS class
     *
     * @return string
     */
    public function getEmptyTextClass()
    {
        return $this->_emptyTextCss;
    }

    /**
     * Retrieve label for empty cell
     *
     * @return string
     */
    public function getEmptyCellLabel()
    {
        return $this->_emptyCellLabel;
    }

    /**
     * Set label for empty cell
     *
     * @param string $label
     * @return Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    public function setEmptyCellLabel($label)
    {
        $this->_emptyCellLabel = $label;
        return $this;
    }

    /**
     * Set flag whether is collapsed
     * @param $isCollapsed
     * @return Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    public function setIsCollapsed($isCollapsed)
    {
        $this->_isCollapsed = $isCollapsed;
        return $this;
    }

    /**
     * Retrieve flag is collapsed
     * @return mixed
     */
    public function getIsCollapsed()
    {
        return $this->_isCollapsed;
    }

    /**
     * Return grid of current column set
     * @return Mage_Backend_Block_Widget_Grid
     */
    public function getGrid()
    {
        return $this->getParentBlock();
    }

    /**
     * Return collection of current grid
     * @return Varien_Data_Collection
     */
    public function getCollection()
    {
        return $this->getGrid()->getCollection();
    }

    /**
     * Set subtotals
     *
     * @param boolean $flag
     * @return Mage_Backend_Block_Widget_Grid
     */
    public function setCountSubTotals($flag = true)
    {
        $this->_countSubTotals = $flag;
        return $this;
    }

    /**
     * Return count subtotals
     *
     * @return mixed
     */
    public function getCountSubTotals()
    {
        return $this->_countSubTotals;
    }

    /**
     * Set totals
     *
     * @param boolean $flag
     * @return Mage_Backend_Block_Widget_Grid
     */
    public function setCountTotals($flag = true)
    {
        $this->_countTotals = $flag;
        return $this;
    }

    /**
     * Return count totals
     *
     * @return mixed
     */
    public function getCountTotals()
    {
        return $this->_countTotals;
    }

    /**
     * Retrieve subtotal for item
     *
     * @param $item Varien_Object
     * @return Varien_Object
     */
    public function getSubTotals($item)
    {
        $this->_prepareSubTotals();
        $this->_subTotals->reset();
        return $this->_subTotals->countTotals($item->getChildren());
    }

    /**
     * Retrieve subtotal items
     *
     * @return Varien_Object
     */
    public function getTotals()
    {
        $this->_prepareTotals();
        $this->_totals->reset();
        return $this->_totals->countTotals($this->getCollection());
    }

    /**
     * Update item with first sub-item data
     *
     * @param $item Varien_Object
     */
    public function updateItemByFirstMultiRow(Varien_Object $item)
    {
        $multiRows = $this->getMultipleRows($item);
        if (is_object($multiRows) && $multiRows instanceof Varien_Data_Collection) {
            /** @var $multiRows Varien_Data_Collection */
            $item->addData($multiRows->getFirstItem()->getData());
        } elseif (is_array($multiRows)) {
            $firstItem = $multiRows[0];
            $item->addData($firstItem);
        }
    }

    /**
     * Prepare sub-total object for counting sub-totals
     */
    public function _prepareSubTotals()
    {
        $columns = $this->_subTotals->getColumns();
        if (empty($columns)) {
            foreach ($this->getMultipleRowColumns() as $column) {
                if ($column->getTotal()) {
                    $this->_subTotals->setColumn($column->getIndex(), $column->getTotal());
                }
            }
        }
    }

    /**
     * Prepare total object for counting totals
     */
    public function _prepareTotals()
    {
        $columns = $this->_totals->getColumns();
        if (empty($columns)) {
            foreach ($this->getColumns() as $column) {
                if ($column->getTotal()) {
                    $this->_totals->setColumn($column->getIndex(), $column->getTotal());
                }
            }
        }
    }

}
