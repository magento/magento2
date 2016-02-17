<?php

namespace League\CLImate\TerminalObject\Basic;

use League\CLImate\TerminalObject\Helper\StringLength;

class Columns extends BasicTerminalObject
{
    use StringLength;

    /**
     * Number of columns
     *
     * @var integer $column_count
     */
    protected $column_count;

    /**
     * Data to columnize
     *
     * @var array $data
     */
    protected $data;

    public function __construct($data, $column_count = null)
    {
        $this->data  = $data;
        $this->column_count = $column_count;
    }

    /**
     * Calculate the number of columns organize data
     *
     * @return array
     */
    public function result()
    {
        $keys      = array_keys($this->data);
        $first_key = reset($keys);

        return (!is_int($first_key)) ? $this->associativeColumns() : $this->columns();
    }

    /**
     * Get columns for a regular array
     *
     * @return array
     */
    protected function columns()
    {
        $this->data    = $this->setData();
        $column_widths = $this->getColumnWidths();
        $output        = [];

        for ($i = 0; $i < count(reset($this->data)); $i++) {
            $output[] = $this->getRow($i, $column_widths);
        }

        return $output;
    }

    /**
     * Re-configure the data into it's final form
     */
    protected function setData()
    {
        // If it's already an array of arrays, we're good to go
        if (is_array(reset($this->data))) {
            return $this->setArrayOfArraysData();
        }

        $column_width = $this->getColumnWidth($this->data);
        $row_count    = $this->getMaxRows($column_width);

        return array_chunk($this->data, $row_count);
    }

    /**
     * Re-configure an array of arrays into column arrays
     */
    protected function setArrayOfArraysData()
    {
        $this->setColumnCountViaArray($this->data);

        $new_data = array_fill(0, $this->column_count, []);

        foreach ($this->data as $items) {
            for ($i = 0; $i < $this->column_count; $i++) {
                $new_data[$i][] = (array_key_exists($i, $items)) ? $items[$i] : null;
            }
        }

        return $new_data;
    }

    /**
     * Get columns for an associative array
     *
     * @return array
     */
    protected function associativeColumns()
    {
        $column_width = $this->getColumnWidth(array_keys($this->data));
        $output       = [];

        foreach ($this->data as $key => $value) {
            $output[] = $this->pad($key, $column_width) . $value;
        }

        return $output;
    }

    /**
     * Get the row of data
     *
     * @param integer $key
     * @param integer $column_width
     *
     * @return string
     */
    protected function getRow($key, $column_widths)
    {
        $row = [];

        for ($j = 0; $j < $this->column_count; $j++) {
            if (array_key_exists($key, $this->data[$j])) {
                $row[] = $this->pad($this->data[$j][$key], $column_widths[$j]);
            }
        }

        return trim(implode('', $row));
    }

    /**
     * Get the standard column width
     *
     * @param array $data
     *
     * @return integer
     */
    protected function getColumnWidth($data)
    {
        // Return the maximum width plus a buffer
        return $this->maxStrLen($data) + 5;
    }

    /**
     * Get an array of each column's width
     *
     * @return array
     */
    protected function getColumnWidths()
    {
        $column_widths = [];

        for ($i = 0; $i < $this->column_count; $i++) {
            $column_widths[] = $this->getColumnWidth($this->data[$i]);
        }

        return $column_widths;
    }

    /**
     * Set the count property
     *
     * @param integer $column_width
     */
    protected function setColumnCount($column_width)
    {
        $this->column_count = floor($this->util->width() / $column_width);
    }

    /**
     * Set the count property via an array
     *
     * @param array $items
     */
    protected function setColumnCountViaArray($items)
    {
        $counts = array_map(function($arr) {
            return count($arr);
        }, $items);

        $this->column_count = max($counts);
    }

    /**
     * Get the number of rows per column
     *
     * @param integer $column_width
     *
     * @return integer
     */
    protected function getMaxRows($column_width)
    {
        if (!$this->column_count) {
            $this->setColumnCount($column_width);
        }

        return ceil(count($this->data) / $this->column_count);
    }
}
