<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Complex;

/**
 * Complex pattern class for complex generator (used for creating configurable products)
 *
 *
 */
class Pattern
{
    /**
     * Pattern headers set
     *
     * @var array
     */
    protected $_headers;

    /**
     * Rows set - array of rows pattern, can contain as many rows as you need
     *
     * @var array(array)
     */
    protected $_rowsSet;

    /**
     * Position
     *
     * @var int
     */
    protected $_position = 0;

    /**
     * Set headers
     *
     * @param array $headers
     *
     * @return Pattern
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    /**
     * Get headers array
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Set combined rows set
     *
     * @param array $rowsSet
     *
     * @return Pattern
     * @throws \Exception
     */
    public function setRowsSet(array $rowsSet)
    {
        if (!count($rowsSet)) {
            throw new \Exception("Rows set must contain at least 1 array representing a row pattern");
        }
        $this->_rowsSet = $rowsSet;
        if (!isset($this->_headers)) {
            $this->_headers = array_keys($rowsSet[0]);
        }
        return $this;
    }

    /**
     * Add row
     *
     * @param array $row
     *
     * @return Pattern
     */
    public function addRow(array $row)
    {
        $this->_rowsSet[] = $row;
        return $this;
    }

    /**
     * Get row
     *
     * @param int $index
     * @param int $generatorKey
     *
     * @return array|null
     */
    public function getRow($index, $generatorKey)
    {
        $row = $this->_rowsSet[$generatorKey % count($this->_rowsSet)];
        foreach ($this->getHeaders() as $key) {
            if (isset($row[$key])) {
                if (is_callable($row[$key])) {
                    $row[$key] = call_user_func($row[$key], $index);
                } else {
                    $row[$key] = str_replace('%s', $index, $row[$key]);
                }
            } else {
                $row[$key] = '';
            }
        }
        return $row;
    }

    /**
     * Get rows count
     *
     * @return int
     */
    public function getRowsCount()
    {
        return count($this->_rowsSet);
    }
}
