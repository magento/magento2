<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Text\Table;

use Zend\Text\Table\Decorator\DecoratorInterface as Decorator;

/**
 * Row class for Zend\Text\Table
 */
class Row
{
    /**
     * List of all columns
     *
     * @var array
     */
    protected $columns = array();

    /**
     * Temporary stored column widths
     *
     * @var array
     */
    protected $columnWidths = null;

    /**
     * Create a new column and append it to the row
     *
     * @param  string $content
     * @param  array  $options
     * @return Row
     */
    public function createColumn($content, array $options = null)
    {
        $align    = null;
        $colSpan  = null;
        $encoding = null;

        if ($options !== null) {
            extract($options, EXTR_IF_EXISTS);
        }

        $column = new Column($content, $align, $colSpan, $encoding);

        $this->appendColumn($column);

        return $this;
    }

    /**
     * Append a column to the row
     *
     * @param  \Zend\Text\Table\Column $column The column to append to the row
     * @return Row
     */
    public function appendColumn(Column $column)
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * Get a column by it's index
     *
     * Returns null, when the index is out of range
     *
     * @param  int $index
     * @return Column|null
     */
    public function getColumn($index)
    {
        if (!isset($this->columns[$index])) {
            return;
        }

        return $this->columns[$index];
    }

    /**
     * Get all columns of the row
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get the widths of all columns, which were rendered last
     *
     * @throws Exception\UnexpectedValueException When no columns were rendered yet
     * @return int
     */
    public function getColumnWidths()
    {
        if ($this->columnWidths === null) {
            throw new Exception\UnexpectedValueException(
                'render() must be called before columnWidths can be populated'
            );
        }

        return $this->columnWidths;
    }

    /**
     * Render the row
     *
     * @param  array                               $columnWidths Width of all columns
     * @param  Decorator $decorator    Decorator for the row borders
     * @param  int                             $padding      Padding for the columns
     * @throws Exception\OverflowException When there are too many columns
     * @return string
     */
    public function render(array $columnWidths, Decorator $decorator, $padding = 0)
    {
        // Prepare an array to store all column widths
        $this->columnWidths = array();

        // If there is no single column, create a column which spans over the
        // entire row
        if (count($this->columns) === 0) {
            $this->appendColumn(new Column(null, null, count($columnWidths)));
        }

        // First we have to render all columns, to get the maximum height
        $renderedColumns = array();
        $maxHeight       = 0;
        $colNum          = 0;
        foreach ($this->columns as $column) {
            // Get the colspan of the column
            $colSpan = $column->getColSpan();

            // Verify if there are enough column widths defined
            if (($colNum + $colSpan) > count($columnWidths)) {
                throw new Exception\OverflowException('Too many columns');
            }

            // Calculate the column width
            $columnWidth = ($colSpan - 1 + array_sum(array_slice($columnWidths, $colNum, $colSpan)));

            // Render the column and split it's lines into an array
            $result = explode("\n", $column->render($columnWidth, $padding));

            // Store the width of the rendered column
            $this->columnWidths[] = $columnWidth;

            // Store the rendered column and calculate the new max height
            $renderedColumns[] = $result;
            $maxHeight         = max($maxHeight, count($result));

            // Set up the internal column number
            $colNum += $colSpan;
        }

        // If the row doesnt contain enough columns to fill the entire row, fill
        // it with an empty column
        if ($colNum < count($columnWidths)) {
            $remainingWidth = (count($columnWidths) - $colNum - 1) +
                               array_sum(array_slice($columnWidths, $colNum));
            $renderedColumns[] = array(str_repeat(' ', $remainingWidth));

            $this->columnWidths[] = $remainingWidth;
        }

        // Add each single column line to the result
        $result = '';
        for ($line = 0; $line < $maxHeight; $line++) {
            $result .= $decorator->getVertical();

            foreach ($renderedColumns as $index => $renderedColumn) {
                if (isset($renderedColumn[$line]) === true) {
                    $result .= $renderedColumn[$line];
                } else {
                    $result .= str_repeat(' ', $this->columnWidths[$index]);
                }

                $result .= $decorator->getVertical();
            }

            $result .= "\n";
        }

        return $result;
    }
}
