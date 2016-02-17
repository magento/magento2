<?php

namespace League\CLImate\TerminalObject\Basic;

use League\CLImate\TerminalObject\Helper\StringLength;

class Table extends BasicTerminalObject
{
    use StringLength;

    /**
     * The data for the table, an array of (arrays|objects)
     *
     * @var array $data
     */
    protected $data           = [];

    /**
     * An array of the widths of each column in the table
     *
     * @var array $column_widths
     */
    protected $column_widths  = [];

    /**
     * The width of the table
     *
     * @var integer $table_width
     */
    protected $table_width    = 0;

    /**
     * The divider between table cells
     *
     * @var string $column_divider
     */
    protected $column_divider = ' | ';

    /**
     * The border to divide each row of the table
     *
     * @var string $border
     */
    protected $border;

    /**
     * The array of rows that will ultimately be returned
     *
     * @var array $rows
     */
    protected $rows           = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return the built rows
     *
     * @return array
     */
    public function result()
    {
        $this->column_widths = $this->getColumnWidths();
        $this->table_width   = $this->getWidth();
        $this->border        = $this->getBorder();

        $this->buildHeaderRow();

        foreach ($this->data as $key => $columns) {
            $this->rows[] = $this->buildRow($columns);
            $this->rows[] = $this->border;
        }

        return $this->rows;
    }

    /**
     * Determine the width of the table
     *
     * @return integer
     */
    protected function getWidth()
    {
        $first_row = reset($this->data);
        $first_row = $this->buildRow($first_row);

        return $this->lengthWithoutTags($first_row);
    }

    /**
     * Get the border for each row based on the table width
     */
    protected function getBorder()
    {
        return (new Border())->length($this->table_width)->result();
    }

    /**
     * Check for a header row (if it's an array of associative arrays or objects),
     * if there is one, tack it onto the front of the rows array
     */
    protected function buildHeaderRow()
    {
        $header_row = $this->getHeaderRow();

        if ($header_row) {
            $this->rows[] = $this->border;
            $this->rows[] = $this->buildRow($header_row);
            $this->rows[] = (new Border())->char('=')->length($this->table_width)->result();
        } else {
            $this->rows[] = $this->border;
        }
    }

    /**
     * Get table row
     *
     * @param  mixed  $columns
     *
     * @return string
     */
    protected function buildRow($columns)
    {
        $row = [];

        foreach ($columns as $key => $column) {
            $row[] = $this->buildCell($key, $column);
        }

        $row = implode($this->column_divider, $row);

        return trim($this->column_divider . $row . $this->column_divider);
    }

    /**
     * Build the string for this particular table cell
     *
     * @param  mixed  $key
     * @param  string $column
     *
     * @return string
     */
    protected function buildCell($key, $column)
    {
        return  $this->pad($column, $this->column_widths[$key]);
    }

    /**
     * Get the header row for the table if it's an associative array or object
     *
     * @return mixed
     */
    protected function getHeaderRow()
    {
        $first_item = reset($this->data);

        if (is_object($first_item)) {
            $first_item = get_object_vars($first_item);
        }

        $keys       = array_keys($first_item);
        $first_key  = reset($keys);

        // We have an associative array (probably), let's have a header row
        if (!is_int($first_key)) {
            return array_combine($keys, $keys);
        }

        return false;
    }

    /**
     * Determine the width of each column
     *
     * @return array
     */
    protected function getColumnWidths()
    {
        $first_row = reset($this->data);

        if (is_object($first_row)) {
            $first_row = get_object_vars($first_row);
        }

        // Create an array with the columns as keys and values of zero
        $column_widths = $this->getDefaultColumnWidths($first_row);

        foreach ($this->data as $columns) {
            foreach ($columns as $key => $column) {
                $column_widths[$key] = $this->getCellWidth($column_widths[$key], $column);
            }
        }

        return $column_widths;
    }

    /**
     * Set up an array of default column widths
     *
     * @param array $columns
     *
     * @return array
     */
    protected function getDefaultColumnWidths(array $columns)
    {
        $widths = $this->arrayOfStrLens(array_keys($columns));

        return array_combine(array_keys($columns), $widths);
    }

    /**
     * Determine the width of the columns without tags
     *
     * @param mixed  $key
     * @param string $column
     *
     * @return integer
     */
    protected function getCellWidth($current_width, $str)
    {
        return max($current_width, $this->lengthWithoutTags($str));
    }
}
