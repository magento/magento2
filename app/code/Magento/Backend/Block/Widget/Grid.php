<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget;

/**
 * Backend grid widget block
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @method string getRowClickCallback() getRowClickCallback()
 * @method \Magento\Backend\Block\Widget\Grid setRowClickCallback() setRowClickCallback(string $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 2.0.0
 */
class Grid extends \Magento\Backend\Block\Widget
{
    /**
     * Page and sorting var names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_varNameLimit = 'limit';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_varNamePage = 'page';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_varNameSort = 'sort';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_varNameDir = 'dir';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_varNameFilter = 'filter';

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_defaultLimit = 20;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_defaultPage = 1;

    /**
     * @var bool|string
     * @since 2.0.0
     */
    protected $_defaultSort = false;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_defaultDir = 'desc';

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_defaultFilter = [];

    /**
     * Empty grid text
     *
     * @var string|null
     * @since 2.0.0
     */
    protected $_emptyText;

    /**
     * Empty grid text CSS class
     *
     * @var string|null
     * @since 2.0.0
     */
    protected $_emptyTextCss = 'empty-text';

    /**
     * Pager visibility
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_pagerVisibility = true;

    /**
     * Massage block visibility
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_messageBlockVisibility = false;

    /**
     * Should parameters be saved in session
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_saveParametersInSession = false;

    /**
     * Count totals
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_countTotals = false;

    /**
     * Totals
     *
     * @var \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected $_varTotals;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/grid.phtml';

    /**
     * @var \Magento\Backend\Model\Session
     * @since 2.0.0
     */
    protected $_backendSession;

    /**
     * @var \Magento\Backend\Helper\Data
     * @since 2.0.0
     */
    protected $_backendHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->_backendHelper = $backendHelper;
        $this->_backendSession = $context->getBackendSession();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->getRowClickCallback()) {
            $this->setRowClickCallback('openGridRow');
        }

        if ($this->hasData('id')) {
            $this->setId($this->getData('id'));
        }

        if ($this->hasData('default_sort')) {
            $this->setDefaultSort($this->getData('default_sort'));
        }

        if ($this->hasData('default_dir')) {
            $this->setDefaultDir($this->getData('default_dir'));
        }

        if ($this->hasData('save_parameters_in_session')) {
            $this->setSaveParametersInSession($this->getData('save_parameters_in_session'));
        }

        $this->setPagerVisibility(
            $this->hasData('pager_visibility') ? (bool)$this->getData('pager_visibility') : true
        );

        $this->setData('use_ajax', $this->hasData('use_ajax') ? (bool)$this->getData('use_ajax') : false);
    }

    /**
     * Set collection object
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     * @since 2.0.0
     */
    public function setCollection($collection)
    {
        $this->setData('dataSource', $collection);
    }

    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    public function getCollection()
    {
        return $this->getData('dataSource');
    }

    /**
     * Retrieve column set block
     *
     * @return \Magento\Backend\Block\Widget\Grid\ColumnSet
     * @since 2.0.0
     */
    public function getColumnSet()
    {
        return $this->getChildBlock('grid.columnSet');
    }

    /**
     * Retrieve export block
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     * @since 2.0.0
     */
    public function getExportBlock()
    {
        if (!$this->getChildBlock('grid.export')) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Export block for grid %1 is not defined', $this->getNameInLayout())
            );
        }
        return $this->getChildBlock('grid.export');
    }

    /**
     * Retrieve list of grid columns
     *
     * @return array
     * @since 2.0.0
     */
    public function getColumns()
    {
        return $this->getColumnSet()->getColumns();
    }

    /**
     * Count grid columns
     *
     * @return int
     * @since 2.0.0
     */
    public function getColumnCount()
    {
        return count($this->getColumns());
    }

    /**
     * Retrieve column by id
     *
     * @param string $columnId
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     * @since 2.0.0
     */
    public function getColumn($columnId)
    {
        return $this->getColumnSet()->getChildBlock($columnId);
    }

    /**
     * Process column filtration values
     *
     * @param mixed $data
     * @return $this
     * @since 2.0.0
     */
    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset(
                $data[$columnId]
            ) && (is_array(
                $data[$columnId]
            ) && !empty($data[$columnId]) || strlen(
                $data[$columnId]
            ) > 0) && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);
                $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    /**
     * Add column filtering conditions to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     * @since 2.0.0
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $condition = $column->getFilter()->getCondition();
                if ($field && isset($condition)) {
                    $this->getCollection()->addFieldToFilter($field, $condition);
                }
            }
        }
        return $this;
    }

    /**
     * Sets sorting order by some column
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     * @since 2.0.0
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }

    /**
     * Get prepared collection
     *
     * @return \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    public function getPreparedCollection()
    {
        $this->_prepareCollection();
        return $this->getCollection();
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _prepareCollection()
    {
        if ($this->getCollection()) {
            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);

            if ($filter === null) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->_backendHelper->prepareFilterString($filter);
                $data = array_merge($data, (array)$this->getRequest()->getPost($this->getVarNameFilter()));
                $this->_setFilterValues($data);
            } elseif ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            } elseif (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if ($this->getColumn($columnId) && $this->getColumn($columnId)->getIndex()) {
                $dir = strtolower($dir) == 'desc' ? 'desc' : 'asc';
                $this->getColumn($columnId)->setDir($dir);
                $this->_setCollectionOrder($this->getColumn($columnId));
            }
        }

        return $this;
    }

    /**
     * Apply pagination to collection
     *
     * @return void
     * @since 2.0.0
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize((int)$this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        $this->getCollection()->setCurPage((int)$this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    /**
     * Initialize grid
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareGrid()
    {
        $this->_eventManager->dispatch(
            'backend_block_widget_grid_prepare_grid_before',
            ['grid' => $this, 'collection' => $this->getCollection()]
        );
        if ($this->getChildBlock('grid.massaction') && $this->getChildBlock('grid.massaction')->isAvailable()) {
            $this->getChildBlock('grid.massaction')->prepareMassactionColumn();
        }

        $this->_prepareCollection();
        if ($this->hasColumnRenderers()) {
            foreach ($this->getColumnRenderers() as $renderer => $rendererClass) {
                $this->getColumnSet()->setRendererType($renderer, $rendererClass);
            }
        }
        if ($this->hasColumnFilters()) {
            foreach ($this->getColumnFilters() as $filter => $filterClass) {
                $this->getColumnSet()->setFilterType($filter, $filterClass);
            }
        }
        $this->getColumnSet()->setSortable($this->getSortable());
        $this->_prepareFilterButtons();
    }

    /**
     * Get massaction block
     *
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    public function getMassactionBlock()
    {
        return $this->getChildBlock('grid.massaction');
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

    /**
     * Initialize grid before rendering
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->_prepareGrid();
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve limit request key
     *
     * @return string
     * @since 2.0.0
     */
    public function getVarNameLimit()
    {
        return $this->_varNameLimit;
    }

    /**
     * Retrieve page request key
     *
     * @return string
     * @since 2.0.0
     */
    public function getVarNamePage()
    {
        return $this->_varNamePage;
    }

    /**
     * Retrieve sort request key
     *
     * @return string
     * @since 2.0.0
     */
    public function getVarNameSort()
    {
        return $this->_varNameSort;
    }

    /**
     * Retrieve sort direction request key
     *
     * @return string
     * @since 2.0.0
     */
    public function getVarNameDir()
    {
        return $this->_varNameDir;
    }

    /**
     * Retrieve filter request key
     *
     * @return string
     * @since 2.0.0
     */
    public function getVarNameFilter()
    {
        return $this->_varNameFilter;
    }

    /**
     * Set Limit request key
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setVarNameLimit($name)
    {
        $this->_varNameLimit = $name;
        return $this;
    }

    /**
     * Set Page request key
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setVarNamePage($name)
    {
        $this->_varNamePage = $name;
        return $this;
    }

    /**
     * Set Sort request key
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setVarNameSort($name)
    {
        $this->_varNameSort = $name;
        return $this;
    }

    /**
     * Set Sort Direction request key
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setVarNameDir($name)
    {
        $this->_varNameDir = $name;
        return $this;
    }

    /**
     * Set Filter request key
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setVarNameFilter($name)
    {
        $this->_varNameFilter = $name;
        return $this;
    }

    /**
     * Set visibility of pager
     *
     * @param bool $visible
     * @return $this
     * @since 2.0.0
     */
    public function setPagerVisibility($visible = true)
    {
        $this->_pagerVisibility = $visible;
        return $this;
    }

    /**
     * Return visibility of pager
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getPagerVisibility()
    {
        return $this->_pagerVisibility;
    }

    /**
     * Set visibility of message blocks
     *
     * @param bool $visible
     * @return void
     * @since 2.0.0
     */
    public function setMessageBlockVisibility($visible = true)
    {
        $this->_messageBlockVisibility = $visible;
    }

    /**
     * Return visibility of message blocks
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getMessageBlockVisibility()
    {
        return $this->_messageBlockVisibility;
    }

    /**
     * Set default limit
     *
     * @param int $limit
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultLimit($limit)
    {
        $this->_defaultLimit = $limit;
        return $this;
    }

    /**
     * Set default page
     *
     * @param int $page
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultPage($page)
    {
        $this->_defaultPage = $page;
        return $this;
    }

    /**
     * Set default sort
     *
     * @param string $sort
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultSort($sort)
    {
        $this->_defaultSort = $sort;
        return $this;
    }

    /**
     * Set default direction
     *
     * @param string $dir
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultDir($dir)
    {
        $this->_defaultDir = $dir;
        return $this;
    }

    /**
     * Set default filter
     *
     * @param string $filter
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultFilter($filter)
    {
        $this->_defaultFilter = $filter;
        return $this;
    }

    /**
     * Check whether grid container should be displayed
     *
     * @return bool
     * @since 2.0.0
     */
    public function canDisplayContainer()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve grid reload url
     *
     * @return string;
     * @since 2.0.0
     */
    public function getGridUrl()
    {
        return $this->hasData('grid_url') ? $this->getData('grid_url') : $this->getAbsoluteGridUrl();
    }

    /**
     * Grid url getter
     * Version of getGridUrl() but with parameters
     *
     * @param array $params url parameters
     * @return string current grid url
     * @since 2.0.0
     */
    public function getAbsoluteGridUrl($params = [])
    {
        return $this->getCurrentUrl($params);
    }

    /**
     * Retrieve grid
     *
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     * @since 2.0.0
     */
    public function getParam($paramName, $default = null)
    {
        $sessionParamName = $this->getId() . $paramName;
        if ($this->getRequest()->has($paramName)) {
            $param = $this->getRequest()->getParam($paramName);
            if ($this->_saveParametersInSession) {
                $this->_backendSession->setData($sessionParamName, $param);
            }
            return $param;
        } elseif ($this->_saveParametersInSession && ($param = $this->_backendSession->getData($sessionParamName))) {
            return $param;
        }

        return $default;
    }

    /**
     * Set whether grid parameters should be saved in session
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setSaveParametersInSession($flag)
    {
        $this->_saveParametersInSession = $flag;
        return $this;
    }

    /**
     * Retrieve grid javascript object name
     *
     * @return string
     * @since 2.0.0
     */
    public function getJsObjectName()
    {
        return preg_replace("~[^a-z0-9_]*~i", '', $this->getId()) . 'JsObject';
    }

    /**
     * Set count totals
     *
     * @param bool $count
     * @return $this
     * @since 2.0.0
     */
    public function setCountTotals($count = true)
    {
        $this->_countTotals = $count;
        return $this;
    }

    /**
     * Return count totals
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getCountTotals()
    {
        return $this->_countTotals;
    }

    /**
     * Set totals
     *
     * @param \Magento\Framework\DataObject $totals
     * @return void
     * @since 2.0.0
     */
    public function setTotals(\Magento\Framework\DataObject $totals)
    {
        $this->_varTotals = $totals;
    }

    /**
     * Retrieve totals
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getTotals()
    {
        return $this->_varTotals;
    }

    /**
     * Generate list of grid buttons
     *
     * @return string
     * @since 2.0.0
     */
    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getColumnSet()->isFilterVisible()) {
            $html .= $this->getSearchButtonHtml();
            $html .= $this->getResetFilterButtonHtml();
        }
        return $html;
    }

    /**
     * Generate reset button
     *
     * @return string
     * @since 2.0.0
     */
    public function getResetFilterButtonHtml()
    {
        return $this->getChildHtml('reset_filter_button');
    }

    /**
     * Generate search button
     *
     * @return string
     * @since 2.0.0
     */
    public function getSearchButtonHtml()
    {
        return $this->getChildHtml('search_button');
    }
}
