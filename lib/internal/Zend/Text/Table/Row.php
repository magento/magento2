<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Row.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Row class for Zend_Text_Table
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_Table_Row
{
    /**
     * List of all columns
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Temporary stored column widths
     *
     * @var array
     */
    protected $_columnWidths = null;

    /**
     * Create a new column and append it to the row
     *
     * @param  string $content
     * @param  array  $options
     * @return Zend_Text_Table_Row
     */
    public function createColumn($content, array $options = null)
    {
        $align    = null;
        $colSpan  = null;
        $encoding = null;

        if ($options !== null) {
            extract($options, EXTR_IF_EXISTS);
        }

        #require_once 'Zend/Text/Table/Column.php';

        $column = new Zend_Text_Table_Column($content, $align, $colSpan, $encoding);

        $this->appendColumn($column);

        return $this;
    }

    /**
     * Append a column to the row
     *
     * @param  Zend_Text_Table_Column $column The column to append to the row
     * @return Zend_Text_Table_Row
     */
    public function appendColumn(Zend_Text_Table_Column $column)
    {
        $this->_columns[] = $column;

        return $this;
    }

    /**
     * Get a column by it's index
     *
     * Returns null, when the index is out of range
     *
     * @param  integer $index
     * @return Zend_Text_Table_Column|null
     */
    public function getColumn($index)
    {
        if (!isset($this->_columns[$index])) {
            return null;
        }

        return $this->_columns[$index];
    }

    /**
     * Get all columns of the row
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Get the widths of all columns, which were rendered last
     *
     * @throws Zend_Text_Table_Exception When no columns were rendered yet
     * @return integer
     */
    public function getColumnWidths()
    {
        if ($this->_columnWidths === null) {
            #require_once 'Zend/Text/Table/Exception.php';
            throw new Zend_Text_Table_Exception('No columns were rendered yet');
        }

        return $this->_columnWidths;
    }

    /**
     * Render the row
     *
     * @param  array                               $columnWidths Width of all columns
     * @param  Zend_Text_Table_Decorator_Interface $decorator    Decorator for the row borders
     * @param  integer                             $padding      Padding for the columns
     * @throws Zend_Text_Table_Exception When there are too many columns
     * @return string
     */
    public function render(array $columnWidths,
                           Zend_Text_Table_Decorator_Interface $decorator,
                           $padding = 0)
    {
        // Prepare an array to store all column widths
        $this->_columnWidths = array();

        // If there is no single column, create a column which spans over the
        // entire row
        if (count($this->_columns) === 0) {
            #require_once 'Zend/Text/Table/Column.php';
            $this->appendColumn(new Zend_Text_Table_Column(null, null, count($columnWidths)));
        }

        // First we have to render all columns, to get the maximum height
        $renderedColumns = array();
        $maxHeight       = 0;
        $colNum          = 0;
        foreach ($this->_columns as $column) {
            // Get the colspan of the column
            $colSpan = $column->getColSpan();

            // Verify if there are enough column widths defined
            if (($colNum + $colSpan) > count($columnWidths)) {
                #require_once 'Zend/Text/Table/Exception.php';
                throw new Zend_Text_Table_Exception('Too many columns');
            }

            // Calculate the column width
            $columnWidth = ($colSpan - 1 + array_sum(array_slice($columnWidths,
                                                                 $colNum,
                                                                 $colSpan)));

            // Render the column and split it's lines into an array
            $result = explode("\n", $column->render($columnWidth, $padding));

            // Store the width of the rendered column
            $this->_columnWidths[] = $columnWidth;

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
                               array_sum(array_slice($columnWidths,
                                                     $colNum));
            $renderedColumns[] = array(str_repeat(' ', $remainingWidth));

            $this->_columnWidths[] = $remainingWidth;
        }

        // Add each single column line to the result
        $result = '';
        for ($line = 0; $line < $maxHeight; $line++) {
            $result .= $decorator->getVertical();

            foreach ($renderedColumns as $renderedColumn) {
                if (isset($renderedColumn[$line]) === true) {
                    $result .= $renderedColumn[$line];
                } else {
                    $result .= str_repeat(' ', strlen($renderedColumn[0]));
                }

                $result .= $decorator->getVertical();
            }

            $result .= "\n";
        }

        return $result;
    }
}
