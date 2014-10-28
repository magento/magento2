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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Data\Collection;

use Magento\Framework\Data\Collection;

/**
 * Filesystem items collection
 *
 * Can scan a folder for files and/or folders recursively.
 * Creates \Magento\Framework\Object instance for each item, with its filename and base name
 *
 * Supports regexp masks that are applied to files and folders base names.
 * These masks apply before adding items to collection, during filesystem scanning
 *
 * Supports dirsFirst feature, that will make directories be before files, regardless of sorting column.
 *
 * Supports some fancy filters.
 *
 * At least one target directory must be set
 */
class Filesystem extends \Magento\Framework\Data\Collection
{
    /**
     * Target directory
     *
     * @var string
     */
    protected $_targetDirs = array();

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
    private $_filterBrackets = array();

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
    protected $_collectedDirs = array();

    /**
     * Collecting items helper variable
     *
     * @var array
     */
    protected $_collectedFiles = array();

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
     */
    protected function _collectRecursive($dir)
    {
        $collectedResult = array();
        if (!is_array($dir)) {
            $dir = array($dir);
        }
        foreach ($dir as $folder) {
            if ($nodes = glob($folder . '/*')) {
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

        $this->_collectedFiles = array();
        $this->_collectedDirs = array();
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
            usort($this->{$attributeName}, array($this, '_usort'));
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
        $this->_orders = array($field => $direction);
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
        return array('filename' => $filename, 'basename' => basename($filename));
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
        $this->_filters[$this->_filterIncrement] = array(
            'field' => $field,
            'value' => $value,
            'is_and' => 'and' === $type,
            'callback' => $callback,
            'is_inverted' => $isInverted
        );
        $this->_filterIncrement++;
        return $this;
    }

    /**
     * The filters renderer and caller
     * Applies to each row, renders once.
     *
     * @param array $row
     * @return bool
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
     */
    public function addFieldToFilter($field, $cond, $type = 'and')
    {
        $inverted = true;

        // simply check whether equals
        if (!is_array($cond)) {
            return $this->addCallbackFilter($field, $cond, $type, array($this, 'filterCallbackEq'));
        }

        // versatile filters
        if (isset($cond['from']) || isset($cond['to'])) {
            $this->_addFilterBracket('(', 'and' === $type);
            if (isset($cond['from'])) {
                $this->addCallbackFilter(
                    $field,
                    $cond['from'],
                    'and',
                    array($this, 'filterCallbackIsLessThan'),
                    $inverted
                );
            }
            if (isset($cond['to'])) {
                $this->addCallbackFilter(
                    $field,
                    $cond['to'],
                    'and',
                    array($this, 'filterCallbackIsMoreThan'),
                    $inverted
                );
            }
            return $this->_addFilterBracket(')');
        }
        if (isset($cond['eq'])) {
            return $this->addCallbackFilter($field, $cond['eq'], $type, array($this, 'filterCallbackEq'));
        }
        if (isset($cond['neq'])) {
            return $this->addCallbackFilter($field, $cond['neq'], $type, array($this, 'filterCallbackEq'), $inverted);
        }
        if (isset($cond['like'])) {
            return $this->addCallbackFilter($field, $cond['like'], $type, array($this, 'filterCallbackLike'));
        }
        if (isset($cond['nlike'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['nlike'],
                $type,
                array($this, 'filterCallbackLike'),
                $inverted
            );
        }
        if (isset($cond['in'])) {
            return $this->addCallbackFilter($field, $cond['in'], $type, array($this, 'filterCallbackInArray'));
        }
        if (isset($cond['nin'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['nin'],
                $type,
                array($this, 'filterCallbackInArray'),
                $inverted
            );
        }
        if (isset($cond['notnull'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['notnull'],
                $type,
                array($this, 'filterCallbackIsNull'),
                $inverted
            );
        }
        if (isset($cond['null'])) {
            return $this->addCallbackFilter($field, $cond['null'], $type, array($this, 'filterCallbackIsNull'));
        }
        if (isset($cond['moreq'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['moreq'],
                $type,
                array($this, 'filterCallbackIsLessThan'),
                $inverted
            );
        }
        if (isset($cond['gt'])) {
            return $this->addCallbackFilter($field, $cond['gt'], $type, array($this, 'filterCallbackIsMoreThan'));
        }
        if (isset($cond['lt'])) {
            return $this->addCallbackFilter($field, $cond['lt'], $type, array($this, 'filterCallbackIsLessThan'));
        }
        if (isset($cond['gteq'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['gteq'],
                $type,
                array($this, 'filterCallbackIsLessThan'),
                $inverted
            );
        }
        if (isset($cond['lteq'])) {
            return $this->addCallbackFilter(
                $field,
                $cond['lteq'],
                $type,
                array($this, 'filterCallbackIsMoreThan'),
                $inverted
            );
        }
        if (isset($cond['finset'])) {
            $filterValue = $cond['finset'] ? explode(',', $cond['finset']) : array();
            return $this->addCallbackFilter($field, $filterValue, $type, array($this, 'filterCallbackInArray'));
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
        $this->_filterBrackets[$this->_filterIncrement] = array(
            'value' => $bracket === ')' ? ')' : '(',
            'is_and' => $isAnd
        );
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
        $filterValueRegex = str_replace('%', '(.*?)', preg_quote($filterValue, '/'));
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
