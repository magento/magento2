<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Complex;

use Magento\ImportExport\Model\Import\AbstractSource;

/**
 * Class Generator
 *
 *
 * @since 2.0.0
 */
class Generator extends AbstractSource
{
    /**
     * Data row pattern
     *
     * @var Pattern
     * @since 2.0.0
     */
    protected $_pattern;

    /**
     * Entities limit
     *
     * @var int
     * @since 2.0.0
     */
    protected $_limit = 0;

    /**
     * Entities Count
     *
     * @var int
     * @since 2.0.0
     */
    protected $_count = 0;

    /**
     * Array of template variables (static values or callables)
     *
     * @var array
     * @since 2.0.0
     */
    protected $_variables = [];

    /**
     * Current index
     *
     * @var int
     * @since 2.0.0
     */
    protected $_index = 1;

    /**
     * Rows count in pattern
     *
     * @var int
     * @since 2.0.0
     */
    protected $_patternRowsCount = 0;

    /**
     * Read the row pattern to determine which columns are dynamic, set the collection size
     *
     * @param Pattern $rowPattern
     * @param int $count how many records to generate
     * @since 2.0.0
     */
    public function __construct(Pattern $rowPattern, $count)
    {
        $this->_pattern = $rowPattern;
        $this->_count = $count;
        $this->_patternRowsCount = $this->_pattern->getRowsCount();
        $this->_limit = (int)$count * $this->_patternRowsCount;
        parent::__construct($this->_pattern->getHeaders());
    }

    /**
     * Get row index for template
     *
     * @param int $key
     *
     * @return float
     * @since 2.0.0
     */
    public function getIndex($key)
    {
        return floor($key / $this->_patternRowsCount) + 1;
    }

    /**
     * Whether limit of generated elements is reached (according to "Iterator" interface)
     *
     * @return bool
     * @since 2.0.0
     */
    public function valid()
    {
        return $this->_key + 1 <= $this->_limit;
    }

    /**
     * Get next row in set
     *
     * @return array|bool
     * @since 2.0.0
     */
    protected function _getNextRow()
    {
        $key = $this->key();
        $this->_index = $this->getIndex($key);

        if ($key > $this->_limit) {
            return false;
        }
        return $this->_pattern->getRow($this->_index, $key);
    }

    /**
     * Return the current element
     *
     * Returns the row in associative array format: array(<col_name> => <value>, ...)
     *
     * @return array
     * @since 2.0.0
     */
    public function current()
    {
        return $this->_row;
    }
}
