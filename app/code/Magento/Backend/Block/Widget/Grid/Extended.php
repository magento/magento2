<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Extended extends \Magento\Backend\Block\Widget\Grid implements \Magento\Backend\Block\Widget\Grid\ExportInterface
{
    /**
     * Columns array
     *
     * array(
     *      'header'    => string,
     *      'width'     => int,
     *      'sortable'  => bool,
     *      'index'     => string,
     *      //'renderer'  => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Interface,
     *      'format'    => string
     *      'total'     => string (sum, avg)
     * )
     * @var array
     */
    protected $_columns = [];

    /**
     * Collection object
     *
     * @var \Magento\Framework\Data\Collection
     */
    protected $_collection;

    /**
     * Export flag
     *
     * @var bool
     */
    protected $_isExport = false;

    /**
     * Grid export types
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected $_exportTypes = [];

    /**
     * Rows per page for import
     *
     * @var int
     */
    protected $_exportPageSize = 1000;

    /**
     * Identifier of last grid column
     *
     * @var string
     */
    protected $_lastColumnId;

    /**
     * Massaction row id field
     *
     * @var string
     */
    protected $_massactionIdField;

    /**
     * Massaction row id filter
     *
     * @var string
     */
    protected $_massactionIdFilter;

    /**
     * Massaction block name
     *
     * @var string
     */
    protected $_massactionBlockName = \Magento\Backend\Block\Widget\Grid\Massaction\Extended::class;

    /**
     * Columns view order
     *
     * @var array
     */
    protected $_columnsOrder = [];

    /**
     * Label for empty cell
     *
     * @var string
     */
    protected $_emptyCellLabel = '';

    /**
     * Columns to group by
     *
     * @var string[]
     */
    protected $_groupedColumn = [];

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

    /**
     * Empty grid text CSS class
     *
     * @var string|null
     */
    protected $_emptyTextCss = 'empty-text';

    /**
     * @var bool
     */
    protected $_isCollapsed;

    /**
     * Count subtotals
     *
     * @var boolean
     */
    protected $_countSubTotals = false;

    /**
     * SubTotals
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected $_subtotals = [];

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/extended.phtml';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_directory;

    /**
     * Additional path to folder
     *
     * @var string
     */
    protected $_path = 'export';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_emptyText = __('We couldn\'t find any records.');

        $this->_directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Initialize child blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'export_button',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(
                [
                    'label' => __('Export'),
                    'onclick' => $this->getJsObjectName() . '.doExport()',
                    'class' => 'task',
                ]
            )
        );
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary'
                ]
            )->setDataAttribute(
                [
                    'action' => 'grid-filter-reset'
                ]
            )
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'task action-secondary',
                ]
            )->setDataAttribute(
                [
                    'action' => 'grid-filter-apply'
                ]
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieve column set block
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function getColumnSet()
    {
        if (!$this->getChildBlock('grid.columnSet')) {
            $this->setChild(
                'grid.columnSet',
                $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Grid\ColumnSet::class)
            );
        }
        return parent::getColumnSet();
    }

    /**
     * Generate export button
     *
     * @return string
     */
    public function getExportButtonHtml()
    {
        return $this->getChildHtml('export_button');
    }

    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  $this
     */
    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url, ['_current' => true]), 'label' => $label]
        );
        return $this;
    }

    /**
     * Add column to grid
     *
     * @param   string $columnId
     * @param   array|\Magento\Framework\DataObject $column
     * @return  $this
     * @throws  \Exception
     */
    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                    ->createBlock(\Magento\Backend\Block\Widget\Grid\Column\Extended::class)
                    ->setData($column)
                    ->setId($columnId)
                    ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new \Exception(__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;
        return $this;
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return $this
     */
    public function removeColumn($columnId)
    {
        if ($this->getColumnSet()->getChildBlock($columnId)) {
            $this->getColumnSet()->unsetChild($columnId);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = array_pop($this->getColumnSet()->getChildNames());
            }
        }
        return $this;
    }

    /**
     * Add column to grid after specified column.
     *
     * @param   string $columnId
     * @param   array|\Magento\Framework\DataObject $column
     * @param   string $after
     * @return  $this
     */
    public function addColumnAfter($columnId, $column, $after)
    {
        $this->addColumn($columnId, $column);
        $this->addColumnsOrder($columnId, $after);
        return $this;
    }

    /**
     * Add column view order
     *
     * @param string $columnId
     * @param string $after
     * @return $this
     */
    public function addColumnsOrder($columnId, $after)
    {
        $this->_columnsOrder[$columnId] = $after;
        return $this;
    }

    /**
     * Retrieve columns order
     *
     * @return array
     */
    public function getColumnsOrder()
    {
        return $this->_columnsOrder;
    }

    /**
     * Sort columns by predefined order
     *
     * @return $this
     */
    public function sortColumnsByOrder()
    {
        foreach ($this->getColumnsOrder() as $columnId => $after) {
            $this->getLayout()->reorderChild(
                $this->getColumnSet()->getNameInLayout(),
                $this->getColumn($columnId)->getNameInLayout(),
                $this->getColumn($after)->getNameInLayout()
            );
        }

        $columns = $this->getColumnSet()->getChildNames();
        $this->_lastColumnId = array_pop($columns);
        return $this;
    }

    /**
     * Retrieve identifier of last column
     *
     * @return string
     */
    public function getLastColumnId()
    {
        return $this->_lastColumnId;
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->sortColumnsByOrder();
        return $this;
    }

    /**
     * Prepare grid massaction block
     *
     * @return $this
     */
    protected function _prepareMassactionBlock()
    {
        $this->setChild('massaction', $this->getLayout()->createBlock($this->getMassactionBlockName()));
        $this->_prepareMassaction();
        if ($this->getMassactionBlock()->isAvailable()) {
            $this->_prepareMassactionColumn();
        }
        return $this;
    }

    /**
     * Prepare grid massaction actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Prepare grid massaction column
     *
     * @return $this
     */
    protected function _prepareMassactionColumn()
    {
        $columnId = 'massaction';
        $massactionColumn = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->setData(
                [
                    'index' => $this->getMassactionIdField(),
                    'filter_index' => $this->getMassactionIdFilter(),
                    'type' => 'massaction',
                    'name' => $this->getMassactionBlock()->getFormFieldName(),
                    'is_system' => true,
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select',
                ]
            );

        if ($this->getNoFilterMassactionColumn()) {
            $massactionColumn->setData('filter', false);
        }

        $massactionColumn->setSelected($this->getMassactionBlock()->getSelected())->setGrid($this)->setId($columnId);

        $this->getColumnSet()->insert(
            $massactionColumn,
            count($this->getColumnSet()->getColumns()) + 1,
            false,
            $columnId
        );
        return $this;
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        if ($this->getCollection()) {
            if ($this->getCollection()->isLoaded()) {
                $this->getCollection()->clear();
            }

            parent::_prepareCollection();

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

    /**
     * Process collection after loading
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        return $this;
    }

    /**
     * Initialize grid before rendering
     *
     * @return $this
     */
    protected function _prepareGrid()
    {
        $this->_prepareColumns();
        $this->_prepareMassactionBlock();
        parent::_prepareGrid();
        return $this;
    }

    /**
     * Retrieve grid HTML
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }

    /**
     * Retrieve massaction row identifier field
     *
     * @return string
     */
    public function getMassactionIdField()
    {
        return $this->_massactionIdField;
    }

    /**
     * Set massaction row identifier field
     *
     * @param  string    $idField
     * @return $this
     */
    public function setMassactionIdField($idField)
    {
        $this->_massactionIdField = $idField;
        return $this;
    }

    /**
     * Retrieve massaction row identifier filter
     *
     * @return string
     */
    public function getMassactionIdFilter()
    {
        return $this->_massactionIdFilter;
    }

    /**
     * Set massaction row identifier filter
     *
     * @param string $idFilter
     * @return $this
     */
    public function setMassactionIdFilter($idFilter)
    {
        $this->_massactionIdFilter = $idFilter;
        return $this;
    }

    /**
     * Retrieve massaction block name
     *
     * @return string
     */
    public function getMassactionBlockName()
    {
        return $this->_massactionBlockName;
    }

    /**
     * Set massaction block name
     *
     * @param  string    $blockName
     * @return $this
     */
    public function setMassactionBlockName($blockName)
    {
        $this->_massactionBlockName = $blockName;
        return $this;
    }

    /**
     * Retrieve massaction block
     *
     * @return $this
     */
    public function getMassactionBlock()
    {
        return $this->getChildBlock('massaction');
    }

    /**
     * Generate massaction block
     *
     * @return string
     */
    public function getMassactionBlockHtml()
    {
        return $this->getChildHtml('massaction');
    }

    /**
     * Retrieve columns to render
     *
     * @return array
     */
    public function getSubTotalColumns()
    {
        return $this->getColumns();
    }

    /**
     * Check whether should render cell
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
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
     * @return $this
     */
    public function setEmptyCellLabel($label)
    {
        $this->_emptyCellLabel = $label;
        return $this;
    }

    /**
     * Return row url for js event handlers
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return string
     */
    public function getRowUrl($item)
    {
        $res = parent::getRowUrl($item);
        return $res ? $res : '#';
    }

    /**
     * Get children of specified item
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     */
    public function getMultipleRows($item)
    {
        return $item->getChildren();
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
     * @param \Magento\Framework\DataObject $item
     * @return boolean
     */
    public function shouldRenderSubTotal($item)
    {
        return $this->_countSubTotals && count($this->_subtotals) > 0 && count($this->getMultipleRows($item)) > 0;
    }

    /**
     * Retrieve rowspan number
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return int|false
     */
    public function getRowspan($item, $column)
    {
        if ($this->isColumnGrouped($column)) {
            return count($this->getMultipleRows($item)) + count($this->_groupedColumn);
        }
        return false;
    }

    /**
     * Check whether given column is grouped
     *
     * @param string|object $column
     * @param string $value
     * @return boolean|$this
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
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return boolean
     */
    public function shouldRenderEmptyCell($item, $column)
    {
        return $item->getIsEmpty() && in_array($column['index'], $this->_groupedColumn);
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
     * Retrieve subtotal item
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject|string
     */
    public function getSubTotalItem($item)
    {
        foreach ($this->_subtotals as $subtotalItem) {
            foreach ($this->_groupedColumn as $groupedColumn) {
                if ($subtotalItem->getData($groupedColumn) == $item->getData($groupedColumn)) {
                    return $subtotalItem;
                }
            }
        }
        return '';
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
     * Set visibility of column headers
     *
     * @param bool $visible
     * @return void
     */
    public function setHeadersVisibility($visible = true)
    {
        $this->_headersVisibility = $visible;
    }

    /**
     * Return visibility of column headers
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->_headersVisibility;
    }

    /**
     * Set visibility of filter
     *
     * @param bool $visible
     * @return void
     */
    public function setFilterVisibility($visible = true)
    {
        $this->_filterVisibility = $visible;
    }

    /**
     * Return visibility of filter
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFilterVisibility()
    {
        return $this->_filterVisibility;
    }

    /**
     * Set empty text for grid
     *
     * @param string $text
     * @return $this
     */
    public function setEmptyText($text)
    {
        $this->_emptyText = $text;
        return $this;
    }

    /**
     * Return empty text for grid
     *
     * @return string
     */
    public function getEmptyText()
    {
        return $this->_emptyText;
    }

    /**
     * Set empty text CSS class
     *
     * @param string $cssClass
     * @return $this
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
     * Set flag whether is collapsed
     *
     * @param bool $isCollapsed
     * @return $this
     */
    public function setIsCollapsed($isCollapsed)
    {
        $this->_isCollapsed = $isCollapsed;
        return $this;
    }

    /**
     * Retrieve flag is collapsed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCollapsed()
    {
        return $this->_isCollapsed;
    }

    /**
     * Retrieve file content from file container array
     *
     * @param array $fileData
     * @return string
     */
    protected function _getFileContainerContent(array $fileData)
    {
        return $this->_directory->readFile('export/' . $fileData['value']);
    }

    /**
     * Retrieve Headers row array for Export
     *
     * @return string[]
     */
    protected function _getExportHeaders()
    {
        $row = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getExportHeader();
            }
        }
        return $row;
    }

    /**
     * Retrieve Totals row array for Export
     *
     * @return string[]
     */
    protected function _getExportTotals()
    {
        $totals = $this->getTotals();
        $row = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->hasTotalsLabel() ? $column->getTotalsLabel() : $column->getRowFieldExport($totals);
            }
        }
        return $row;
    }

    /**
     * Iterate collection and call callback method per item
     * For callback method first argument always is item object
     *
     * @param string $callback
     * @param array $args additional arguments for callback method
     * @return void
     */
    public function _exportIterateCollection($callback, array $args)
    {
        $originalCollection = $this->getCollection();
        $count = null;
        $page = 1;
        $lPage = null;
        $break = false;

        while ($break !== true) {
            $collection = clone $originalCollection;
            $collection->setPageSize($this->_exportPageSize);
            $collection->setCurPage($page);
            $collection->load();
            if ($count === null) {
                $count = $collection->getSize();
                $lPage = $collection->getLastPageNumber();
            }
            if ($lPage == $page) {
                $break = true;
            }
            $page++;

            foreach ($collection as $item) {
                call_user_func_array([$this, $callback], array_merge([$item], $args));
            }
        }
    }

    /**
     * Write item data to csv export file
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Framework\Filesystem\File\WriteInterface $stream
     * @return void
     */
    protected function _exportCsvItem(
        \Magento\Framework\DataObject $item,
        \Magento\Framework\Filesystem\File\WriteInterface $stream
    ) {
        $row = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }
        $stream->writeCsv($row);
    }

    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function getCsvFile()
    {
        $this->_isExport = true;
        $this->_prepareGrid();

        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.csv';

        $this->_directory->create($this->_path);
        $stream = $this->_directory->openFile($file, 'w+');

        $stream->lock();
        $stream->writeCsv($this->_getExportHeaders());
        $this->_exportIterateCollection('_exportCsvItem', [$stream]);

        if ($this->getCountTotals()) {
            $stream->writeCsv($this->_getExportTotals());
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv()
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"' . $column->getExportHeader() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        $column->getRowFieldExport($item)
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        $column->getRowFieldExport($this->getTotals())
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        return $csv;
    }

    /**
     * Retrieve data in xml
     *
     * @return string
     */
    public function getXml()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        $indexes = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $indexes[] = $column->getIndex();
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<items>';
        foreach ($this->getCollection() as $item) {
            $xml .= $item->toXml($indexes);
        }
        if ($this->getCountTotals()) {
            $xml .= $this->getTotals()->toXml($indexes);
        }
        $xml .= '</items>';
        return $xml;
    }

    /**
     *  Get a row data of the particular columns
     *
     * @param \Magento\Framework\DataObject $data
     * @return string[]
     */
    public function getRowRecord(\Magento\Framework\DataObject $data)
    {
        $row = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($data);
            }
        }
        return $row;
    }

    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @param string $sheetName
     * @return array
     */
    public function getExcelFile($sheetName = '')
    {
        $this->_isExport = true;
        $this->_prepareGrid();

        $convert = new \Magento\Framework\Convert\Excel(
            $this->getCollection()->getIterator(),
            [$this, 'getRowRecord']
        );

        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.xml';

        $this->_directory->create($this->_path);
        $stream = $this->_directory->openFile($file, 'w+');
        $stream->lock();

        $convert->setDataHeader($this->_getExportHeaders());
        if ($this->getCountTotals()) {
            $convert->setDataFooter($this->_getExportTotals());
        }

        $convert->write($stream, $sheetName);
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true // can delete file after use
        ];
    }

    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @return string
     */
    public function getExcel()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        $headers = [];
        $data = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $headers[] = $column->getHeader();
            }
        }
        $data[] = $headers;

        foreach ($this->getCollection() as $item) {
            $row = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($item);
                }
            }
            $data[] = $row;
        }

        if ($this->getCountTotals()) {
            $row = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($this->getTotals());
                }
            }
            $data[] = $row;
        }

        $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));
        return $convert->convert('single_sheet');
    }

    /**
     * Retrieve grid export types
     *
     * @return \Magento\Framework\DataObject[]|false
     */
    public function getExportTypes()
    {
        return empty($this->_exportTypes) ? false : $this->_exportTypes;
    }

    /**
     * Set collection object
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
    }

    /**
     * get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Set subtotals
     *
     * @param boolean $flag
     * @return $this
     */
    public function setCountSubTotals($flag = true)
    {
        $this->_countSubTotals = $flag;
        return $this;
    }

    /**
     * Return count subtotals
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCountSubTotals()
    {
        return $this->_countSubTotals;
    }

    /**
     * Set subtotal items
     *
     * @param \Magento\Framework\DataObject[] $items
     * @return $this
     */
    public function setSubTotals(array $items)
    {
        $this->_subtotals = $items;
        return $this;
    }

    /**
     * Retrieve subtotal items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getSubTotals()
    {
        return $this->_subtotals;
    }

    /**
     * Generate list of grid buttons
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getFilterVisibility()) {
            $html .= $this->getSearchButtonHtml();
            $html .= $this->getResetFilterButtonHtml();
        }
        return $html;
    }
}
