<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Collection;

use Magento\Framework\Data\Collection;

/**
 * Filesystem items collection
 *
 * Can scan a folder for files and/or folders recursively.
 * Creates \Magento\Framework\DataObject instance for each item, with its filename and base name
 *
 * Supports regexp masks that are applied to files and folders base names.
 * These masks apply before adding items to collection, during filesystem scanning
 *
 * Supports dirsFirst feature, that will make directories be before files, regardless of sorting column.
 *
 * Supports some fancy filters.
 *
 * At least one target directory must be set
 *
 * @api
 * @since 100.0.2
 */
class Filesystem extends \Magento\Framework\Data\Collection
{
    /**
     * Target directory
     *
     * @var string
     */
    protected $_targetDirs = [];

    /**
     * Whether to collect files
     *
     * @var bool
     */
    protected $_collectFiles = true;

    /**
     * Whether to collect directories before files
     *
     * @var bool
     */
    protected $_dirsFirst = true;

    /**
     * Whether to collect recursively
     *
     * @var bool
     */
    protected $_collectRecursively = true;

    /**
     * Whether to collect dirs
     *
     * @var bool
     */
    protected $_collectDirs = false;

    /**
     * \Directory names regex pre-filter
     *
     * @var string
     */
    protected $_allowedDirsMask = '/^[a-z0-9\.\-\_]+$/i';

    /**
     * Filenames regex pre-filter
     *
     * @var string
     */
    protected $_allowedFilesMask = '/^[a-z0-9\.\-\_]+\.[a-z0-9]+$/i';

    /**
     * Disallowed filenames regex pre-filter match for better versatility
     *
     * @var string
     */
    protected $_disallowedFilesMask = '';

    /**
     * Filter rendering helper variable
     *
     * @var int
     * @see Collection::$_filter
     * @see Collection::$_isFiltersRendered
     */
    private $_filterIncrement = 0;

    /**
     * Filter rendering helper variable
     *
     * @var array
     * @see Collection::$_filter
     * @see Collection::$_isFiltersRendered
     */
    private $_filterBrackets = [];

    /**
     * Filter rendering helper variable
     *
     * @var string
     * @see Collection::$_filter
     * @see Collection::$_isFiltersRendered
     */
    private $_filterEvalRendered = '';

    /**
     * Collecting items helper variable
     *
     * @var array
     */
    protected $_collectedDirs = [];

    /**
     * Collecting items helper variable
     *
     * @var array
     */
    protected $_collectedFiles = [];

    /**
     * Allowed dirs mask setter
     * Set empty to not filter
     *
     * @param string $regex
     * @return $this
     */
    public function setDirsFilter($regex)
    {
        $this->_allowedDirsMask = (string)$regex;
        return $this;
    }

    /**
     * Allowed files mask setter
     * Set empty to not filter
     *
     * @param string $regex
     * @return $this
     */
    public function setFilesFilter($regex)
    {
        $this->_allowedFilesMask = (string)$regex;
        return $this;
    }

    /**
     * Disallowed files mask setter
     * Set empty value to not use this filter
     *
     * @param string $regex
     * @return $this
     */
    public function setDisallowedFilesFilter($regex)
    {
        $this->_disallowedFilesMask = (string)$regex;
        return $this;
    }

    /**
     * Set whether to collect dirs
     *
     * @param bool $value
     * @return $this
     */
    public function setCollectDirs($value)
    {
        $this->_collectDirs = (bool)$value;
        return $this;
    }

    /**
     * Set whether to collect files
     *
     * @param bool $value
     * @return $this
     */
    public function setCollectFiles($value)
    {
        $this->_collectFiles = (bool)$value;
        return $this;
    }

    /**
     * Set whether to collect recursively
     *
     * @param bool $value
     * @return $this
     */
    public function setCollectRecursively($value)
    {
        $this->_collectRecursively = (bool)$value;
        return $this;
    }

    /**
     * Target directory setter. Adds directory to be scanned
     *
     * @param string $value
     * @return $this
     * @throws \Exception
     */
    public function addTargetDir($value)
    {
        $value = (string)$value;
        if (!is_dir($value)) {
            throw new \Exception('Unable to set target directory.');
        }
        $this->_targetDirs[$value] = $value;
        return $this;
    }

    /**
     * Set whether to collect directories before files
     * Works *before* sorting.
     *
     * @param bool $value
     * @return $this
     */
    public function setDirsFirst($value)
    {
        $this->_dirsFirst = (bool)$value;
        return $this;
    }

    /**
     * Get files from specified directory recursively (if needed)
     *
     * @param string|array $dir
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _collectRecursive($dir)
    {
        $collectedResult = [];
        if (!is_array($dir)) {
            $dir = [$dir];
        }
        foreach ($dir as $folder) {
            if ($nodes = glob($folder . '/*', GLOB_NOSORT)) {
                foreach ($nodes as $node) {
                    $collectedResult[] = $node;
                }
            }
        }
        if (empty($collectedResult)) {
            return;
        }

        foreach ($collectedResult as $item) {
            if (is_dir($item) && (!$this->_allowedDirsMask || preg_match($this->_allowedDirsMask, basename($item)))) {
                if ($this->_collectDirs) {
                    if ($this->_dirsFirst) {
                        $this->_collectedDirs[] = $item;
                    } else {
                        $this->_collectedFiles[] = $item;
                    }
                }
                if ($this->_collectRecursively) {
                    $this->_collectRecursive($item);
                }
            } elseif ($this->_collectFiles && is_file(
                $item
            ) && (!$this->_allowedFilesMask || preg_match(
                $this->_allowedFilesMask,
                basename($item)
            )) && (!$this->_disallowedFilesMask || !preg_match(
                $this->_disallowedFilesMask,
                basename($item)
            ))
            ) {
                $this->_collectedFiles[] = $item;
            }
        }
    }

    /**
     * Lauch data collecting
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        if (empty($this->_targetDirs)) {
            throw new \Exception('Please specify at least one target directory.');
        }

        $this->_collectedFiles = [];
        $this->_collectedDirs = [];
        $this->_collectRecursive($this->_targetDirs);
        $this->_generateAndFilterAndSort('_collectedFiles');
        if ($this->_dirsFirst) {
            $this->_generateAndFilterAndSort('_collectedDirs');
            $this->_collectedFiles = array_merge($this->_collectedDirs, $this->_collectedFiles);
        }

        // calculate totals
        $this->_totalRecords = count($this->_collectedFiles);
        $this->_setIsLoaded();

        // paginate and add items
        $from = ($this->getCurPage() - 1) * $this->getPageSize();
        $to = $from + $this->getPageSize() - 1;
        $isPaginated = $this->getPageSize() > 0;

        $cnt = 0;
        foreach ($this->_collectedFiles as $row) {
            $cnt++;
            if ($isPaginated && ($cnt < $from || $cnt > $to)) {
                continue;
            }
            $item = new $this->_itemObjectClass();
            $this->addItem($item->addData($row));
            if (!$item->hasId()) {
                $item->setId($cnt);
            }
        }

        return $this;
    }

    /**
     * With specified collected items:
     *  - generate data
     *  - apply filters
     *  - sort
     *
     * @param string $attributeName '_collectedFiles' | '_collectedDirs'
     * @return void
     */
    private function _generateAndFilterAndSort($attributeName)
    {
        // generate custom data (as rows with columns) basing on the filenames
        foreach ($this->{$attributeName} as $key => $filename) {
            $this->{$attributeName}[$key] = $this->_generateRow($filename);
        }

        // apply filters on generated data
        if (!empty($this->_filters)) {
            foreach ($this->{$attributeName} as $key => $row) {
                if (!$this->_filterRow($row)) {
                    unset($this->{$attributeName}[$key]);
                }
            }
        }

        // sort (keys are lost!)
        if (!empty($this->_orders)) {
            usort($this->{$attributeName}, [$this, '_usort']);
        }
    }

    /**
     * Callback for sorting items
     * Currently supports only sorting by one column
     *
     * @param array $a
     * @param array $b
     * @return int|void
     */
    protected function _usort($a, $b)
    {
        foreach ($this->_orders as $key => $direction) {
            $result = $a[$key] > $b[$key] ? 1 : ($a[$key] < $b[$key] ? -1 : 0);
            return self::SORT_ORDER_ASC === strtoupper($direction) ? $result : -$result;
            break;
        }
    }

    /**
     * Set select order
     * Currently supports only sorting by one column
     *
     * @param   string $field
     * @param   string $direction
     * @return  Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        $this->_orders = [$field => $direction];
        return $this;
    }

    /**
     * Generate item row basing on the filename
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        return ['filename' => $filename, 'basename' => basename($filename)];
    }

    /**
     * Set a custom filter with callback
     * The callback must take 3 params:
     *     string $field       - field key,
     *     mixed  $filterValue - value to filter by,
     *     array  $row         - a generated row (before generating varien objects)
     *
     * @param string $field
     * @param mixed $value
     * @param string $type 'and'|'or'
     * @param callback $callback
     * @param bool $isInverted
     * @return $this
     */
    public function addCallbackFilter($field, $value, $type, $callback, $isInverted = false)
    {
        $this->_filters[$this->_filterIncrement] = [
            'field' => $field,
            'value' => $value,
            'is_and' => 'and' === $type,
            'callback' => $callback,
            'is_inverted' => $isInverted,
        ];
        $this->_filterIncrement++;
        return $this;
    }

    /**
     * The filters renderer and caller
     * Applies to each row, renders once.
     *
     * @param array $row
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    protected function _filterRow($row)
    {
        // render filters once
        if (!$this->_isFiltersRendered) {
            $eval = '';
            for ($i = 0; $i < $this->_filterIncrement; $i++) {
                if (isset($this->_filterBrackets[$i])) {
                    $eval .= $this->_renderConditionBeforeFilterElement(
                        $i,
                        $this->_filterBrackets[$i]['is_and']
                    ) . $this->_filterBrackets[$i]['value'];
                } else {
                    $f = '$this->_filters[' . $i . ']';
                    $eval .= $this->_renderConditionBeforeFilterElement(
                        $i,
                        $this->_filters[$i]['is_and']
                    ) .
                        ($this->_filters[$i]['is_inverted'] ? '!' : '') .
                        '$this->_invokeFilter(' .
                        "{$f}['callback'], array({$f}['field'], {$f}['value'], " .
                        '$row))';
                }
            }
            $this->_filterEvalRendered = $eval;
            $this->_isFiltersRendered = true;
        }
        $result = false;
        if ($this->_filterEvalRendered) {
            eval('$result = ' . $this->_filterEvalRendered . ';');
        }
        return $result;
    }

    /**
     * Invokes specified callback
     * Skips, if there is no filtered key in the row
     *
     * @param callback $callback
     * @param array $callbackParams
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _invokeFilter($callback, $callbackParams)
    {
        list($field, $value, $row) = $callbackParams;
        if (!array_key_exists($field, $row)) {
            return false;
        }
        return call_user_func_array($callback, $callbackParams);
    }

    /**
     * Fancy field filter
     *
     * @param string $field
     * @param mixed $cond
     * @param string $type 'and' | 'or'
     * @see Db::addFieldToFilter()
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function addFieldToFilter($field, $cond, $type = 'and')
    {
        $inverted = true;

        // simply check whether equals
        if (!is_array($cond)) {
            return $this->addCallbackFilter($field, $cond, $type, [$this, 'filterCallbackEq']);
        }

        // versatile filters
        if (isset($cond['from']) || isset($cond['to'])) {
            $this->_addFilterBracket('(', 'and' === $type);
            if (isset($cond['from'])) {
                $this->addCallbackFilter(
                    $field,
                    $cond['from'],
                    'and',
                    [$this, 'filterCallbackIsLessThan'],
                    $inverted
                );
            }
            if (isset($cond['to'])) {
                $this->addCallbackFilter(
                    $field,
                    $cond['to'],
                    'and',
                    [$this, 'filterCallbackIsMoreThan'],
                    $inverted
                );
            }
            return $this->_addFilterBracket(')');
        }
        if (isset($cond['eq'])) {
            return $this->addCallbackFilter($field, $cond['eq'], $type, [$this, 'filterCallbackEq']);
        }
        if (isset($cond['neq'])) {
            return $this->addCallbackFilter($field, $cond['neq'], $type, [$this, 'filterCallbackEq'], $inverted);
        }
        if (isset($cond['like'])) {
            return $this->addCallbackFilter($field, $cond['like'], $type, [$this, 'filterCallbackLike']);
        }
        if (isset($cond['nlike'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['nlike'],
                $type,
                [$this, 'filterCallbackLike'],
                $inverted
            );
        }
        if (isset($cond['in'])) {
            return $this->addCallbackFilter($field, $cond['in'], $type, [$this, 'filterCallbackInArray']);
        }
        if (isset($cond['nin'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['nin'],
                $type,
                [$this, 'filterCallbackInArray'],
                $inverted
            );
        }
        if (isset($cond['notnull'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['notnull'],
                $type,
                [$this, 'filterCallbackIsNull'],
                $inverted
            );
        }
        if (isset($cond['null'])) {
            return $this->addCallbackFilter($field, $cond['null'], $type, [$this, 'filterCallbackIsNull']);
        }
        if (isset($cond['moreq'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['moreq'],
                $type,
                [$this, 'filterCallbackIsLessThan'],
                $inverted
            );
        }
        if (isset($cond['gt'])) {
            return $this->addCallbackFilter($field, $cond['gt'], $type, [$this, 'filterCallbackIsMoreThan']);
        }
        if (isset($cond['lt'])) {
            return $this->addCallbackFilter($field, $cond['lt'], $type, [$this, 'filterCallbackIsLessThan']);
        }
        if (isset($cond['gteq'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['gteq'],
                $type,
                [$this, 'filterCallbackIsLessThan'],
                $inverted
            );
        }
        if (isset($cond['lteq'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['lteq'],
                $type,
                [$this, 'filterCallbackIsMoreThan'],
                $inverted
            );
        }
        if (isset($cond['finset'])) {
            $filterValue = $cond['finset'] ? explode(',', $cond['finset']) : [];
            return $this->addCallbackFilter($field, $filterValue, $type, [$this, 'filterCallbackInArray']);
        }

        // add OR recursively
        foreach ($cond as $orCond) {
            $this->_addFilterBracket('(', 'and' === $type);
            $this->addFieldToFilter($field, $orCond, 'or');
            $this->_addFilterBracket(')');
        }
        return $this;
    }

    /**
     * Prepare a bracket into filters
     *
     * @param string $bracket
     * @param bool $isAnd
     * @return $this
     */
    protected function _addFilterBracket($bracket = '(', $isAnd = true)
    {
        $this->_filterBrackets[$this->_filterIncrement] = [
            'value' => $bracket === ')' ? ')' : '(',
            'is_and' => $isAnd,
        ];
        $this->_filterIncrement++;
        return $this;
    }

    /**
     * Render condition sign before element, if required
     *
     * @param int $increment
     * @param bool $isAnd
     * @return string
     */
    protected function _renderConditionBeforeFilterElement($increment, $isAnd)
    {
        if (isset($this->_filterBrackets[$increment]) && ')' === $this->_filterBrackets[$increment]['value']) {
            return '';
        }
        $prevIncrement = $increment - 1;
        $prevBracket = false;
        if (isset($this->_filterBrackets[$prevIncrement])) {
            $prevBracket = $this->_filterBrackets[$prevIncrement]['value'];
        }
        if ($prevIncrement < 0 || $prevBracket === '(') {
            return '';
        }
        return $isAnd ? ' && ' : ' || ';
    }

    /**
     * Does nothing. Intentionally disabled parent method
     * @param string $field
     * @param string $value
     * @param string $type
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter($field, $value, $type = 'and')
    {
        return $this;
    }

    /**
     * Get all ids of collected items
     *
     * @return array
     */
    public function getAllIds()
    {
        return array_keys($this->_items);
    }

    /**
     * Callback method for 'like' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackLike($field, $filterValue, $row)
    {
        $filterValue = trim(stripslashes($filterValue), '\'');
        $filterValue = trim($filterValue, '%');
        $filterValueRegex = '(.*?)' . preg_quote($filterValue, '/') . '(.*?)';

        return (bool)preg_match("/^{$filterValueRegex}\$/i", $row[$field]);
    }

    /**
     * Callback method for 'eq' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackEq($field, $filterValue, $row)
    {
        return $filterValue == $row[$field];
    }

    /**
     * Callback method for 'in' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackInArray($field, $filterValue, $row)
    {
        return in_array($row[$field], $filterValue);
    }

    /**
     * Callback method for 'isnull' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filterCallbackIsNull($field, $filterValue, $row)
    {
        return null === $row[$field];
    }

    /**
     * Callback method for 'moreq' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackIsMoreThan($field, $filterValue, $row)
    {
        return $row[$field] > $filterValue;
    }

    /**
     * Callback method for 'lteq' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackIsLessThan($field, $filterValue, $row)
    {
        return $row[$field] < $filterValue;
    }
}
