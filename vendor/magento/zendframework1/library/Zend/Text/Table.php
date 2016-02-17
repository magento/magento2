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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Zend_Text_Table enables developers to create tables out of characters
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_Table
{
    /**
     * Auto seperator settings
     */
    const AUTO_SEPARATE_NONE   = 0x0;
    const AUTO_SEPARATE_HEADER = 0x1;
    const AUTO_SEPARATE_FOOTER = 0x2;
    const AUTO_SEPARATE_ALL    = 0x4;

    /**
     * Decorator used for the table borders
     *
     * @var Zend_Text_Table_Decorator_Interface
     */
    protected $_decorator = null;

    /**
     * List of all column widths
     *
     * @var array
     */
    protected $_columnWidths = null;

    /**
     * Rows of the table
     *
     * @var array
     */
    protected $_rows = array();

    /**
     * Auto separation mode
     *
     * @var integer
     */
    protected $_autoSeparate = self::AUTO_SEPARATE_ALL;

    /**
     * Padding for columns
     *
     * @var integer
     */
    protected $_padding = 0;

    /**
     * Default column aligns for rows created by appendRow(array $data)
     *
     * @var array
     */
    protected $_defaultColumnAligns = array();

    /**
     * Plugin loader for decorators
     *
     * @var string
     */
    protected $_pluginLoader = null;

    /**
     * Charset which is used for input by default
     *
     * @var string
     */
    protected static $_inputCharset = 'utf-8';

    /**
     * Charset which is used internally
     *
     * @var string
     */
    protected static $_outputCharset = 'utf-8';

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $_skipOptions = array(
        'options',
        'config',
        'defaultColumnAlign',
    );

    /**
     * Create a basic table object
     *
     * @param  array             $columnsWidths List of all column widths
     * @param  Zend_Config|array $options       Configuration options
     * @throws Zend_Text_Table_Exception When no columns widths were set
     */
    public function __construct($options = null)
    {
        // Set options
        if (is_array($options)) {
            $this->setOptions($options);
        } else if ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }

        // Check if column widths were set
        // @todo When column widths were not set, assume auto-sizing
        if ($this->_columnWidths === null) {
            #require_once 'Zend/Text/Table/Exception.php';
            throw new Zend_Text_Table_Exception('You must define the column widths');
        }

        // If no decorator was given, use default unicode decorator
        if ($this->_decorator === null) {
            if (self::getOutputCharset() === 'utf-8') {
                $this->setDecorator('unicode');
            } else {
                $this->setDecorator('ascii');
            }
        }
    }

    /**
     * Set options from array
     *
     * @param  array $options Configuration for Zend_Text_Table
     * @return Zend_Text_Table
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->_skipOptions)) {
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Set options from config object
     *
     * @param  Zend_Config $config Configuration for Zend_Text_Table
     * @return Zend_Text_Table
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set column widths
     *
     * @param  array $columnWidths Widths of all columns
     * @throws Zend_Text_Table_Exception When no columns were supplied
     * @throws Zend_Text_Table_Exception When a column has an invalid width
     * @return Zend_Text_Table
     */
    public function setColumnWidths(array $columnWidths)
    {
        if (count($columnWidths) === 0) {
            #require_once 'Zend/Text/Table/Exception.php';
            throw new Zend_Text_Table_Exception('You must supply at least one column');
        }

        foreach ($columnWidths as $columnNum => $columnWidth) {
            if (is_int($columnWidth) === false or $columnWidth < 1) {
                #require_once 'Zend/Text/Table/Exception.php';
                throw new Zend_Text_Table_Exception('Column ' . $columnNum . ' has an invalid'
                                                    . ' column width');
            }
        }

        $this->_columnWidths = $columnWidths;

        return $this;
    }

    /**
     * Set auto separation mode
     *
     * @param  integer $autoSeparate Auto separation mode
     * @return Zend_Text_Table
     */
    public function setAutoSeparate($autoSeparate)
    {
        $this->_autoSeparate = (int) $autoSeparate;
        return $this;
    }

    /**
     * Set decorator
     *
     * @param  Zend_Text_Table_Decorator_Interface|string $decorator Decorator to use
     * @return Zend_Text_Table
     */
    public function setDecorator($decorator)
    {
        if ($decorator instanceof Zend_Text_Table_Decorator_Interface) {
            $this->_decorator = $decorator;
        } else {
            $classname        = $this->getPluginLoader()->load($decorator);
            $this->_decorator = new $classname;
        }

        return $this;
    }

    /**
     * Set the column padding
     *
     * @param  integer $padding The padding for the columns
     * @return Zend_Text_Table
     */
    public function setPadding($padding)
    {
        $this->_padding = max(0, (int) $padding);
        return $this;
    }

    /**
     * Get the plugin loader for decorators
     *
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        if ($this->_pluginLoader === null) {
            $prefix     = 'Zend_Text_Table_Decorator_';
            $pathPrefix = 'Zend/Text/Table/Decorator/';

            #require_once 'Zend/Loader/PluginLoader.php';
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array($prefix => $pathPrefix));
        }

        return $this->_pluginLoader;
    }

    /**
     * Set default column align for rows created by appendRow(array $data)
     *
     * @param  integer $columnNum
     * @param  string  $align
     * @return Zend_Text_Table
     */
    public function setDefaultColumnAlign($columnNum, $align)
    {
        $this->_defaultColumnAligns[$columnNum] = $align;

        return $this;
    }

    /**
     * Set the input charset for column contents
     *
     * @param string $charset
     */
    public static function setInputCharset($charset)
    {
        self::$_inputCharset = strtolower($charset);
    }

    /**
     * Get the input charset for column contents
     *
     * @param string $charset
     */
    public static function getInputCharset()
    {
        return self::$_inputCharset;
    }

    /**
     * Set the output charset for column contents
     *
     * @param string $charset
     */
    public static function setOutputCharset($charset)
    {
        self::$_outputCharset = strtolower($charset);
    }

    /**
     * Get the output charset for column contents
     *
     * @param string $charset
     */
    public static function getOutputCharset()
    {
        return self::$_outputCharset;
    }

    /**
     * Append a row to the table
     *
     * @param  array|Zend_Text_Table_Row $row The row to append to the table
     * @throws Zend_Text_Table_Exception When $row is neither an array nor Zend_Zext_Table_Row
     * @throws Zend_Text_Table_Exception When a row contains too many columns
     * @return Zend_Text_Table
     */
    public function appendRow($row)
    {
        if (!is_array($row) && !($row instanceof Zend_Text_Table_Row)) {
            #require_once 'Zend/Text/Table/Exception.php';
            throw new Zend_Text_Table_Exception('$row must be an array or instance of Zend_Text_Table_Row');
        }

        if (is_array($row)) {
            if (count($row) > count($this->_columnWidths)) {
                #require_once 'Zend/Text/Table/Exception.php';
                throw new Zend_Text_Table_Exception('Row contains too many columns');
            }

            #require_once 'Zend/Text/Table/Row.php';
            #require_once 'Zend/Text/Table/Column.php';

            $data   = $row;
            $row    = new Zend_Text_Table_Row();
            $colNum = 0;
            foreach ($data as $columnData) {
                if (isset($this->_defaultColumnAligns[$colNum])) {
                    $align = $this->_defaultColumnAligns[$colNum];
                } else {
                    $align = null;
                }

                $row->appendColumn(new Zend_Text_Table_Column($columnData, $align));
                $colNum++;
            }
        }

        $this->_rows[] = $row;

        return $this;
    }

    /**
     * Render the table
     *
     * @throws Zend_Text_Table_Exception When no rows were added to the table
     * @return string
     */
    public function render()
    {
        // There should be at least one row
        if (count($this->_rows) === 0) {
            #require_once 'Zend/Text/Table/Exception.php';
            throw new Zend_Text_Table_Exception('No rows were added to the table yet');
        }

        // Initiate the result variable
        $result = '';

        // Count total columns
        $totalNumColumns = count($this->_columnWidths);

        // Now render all rows, starting from the first one
        $numRows = count($this->_rows);
        foreach ($this->_rows as $rowNum => $row) {
            // Get all column widths
            if (isset($columnWidths) === true) {
                $lastColumnWidths = $columnWidths;
            }

            $renderedRow  = $row->render($this->_columnWidths, $this->_decorator, $this->_padding);
            $columnWidths = $row->getColumnWidths();
            $numColumns   = count($columnWidths);

            // Check what we have to draw
            if ($rowNum === 0) {
                // If this is the first row, draw the table top
                $result .= $this->_decorator->getTopLeft();

                foreach ($columnWidths as $columnNum => $columnWidth) {
                    $result .= str_repeat($this->_decorator->getHorizontal(),
                                          $columnWidth);

                    if (($columnNum + 1) === $numColumns) {
                        $result .= $this->_decorator->getTopRight();
                    } else {
                        $result .= $this->_decorator->getHorizontalDown();
                    }
                }

                $result .= "\n";
            } else {
                // Else check if we have to draw the row separator
                if ($this->_autoSeparate & self::AUTO_SEPARATE_ALL) {
                    $drawSeparator = true;
                } else if ($rowNum === 1 && $this->_autoSeparate & self::AUTO_SEPARATE_HEADER) {
                    $drawSeparator = true;
                } else if ($rowNum === ($numRows - 1) && $this->_autoSeparate & self::AUTO_SEPARATE_FOOTER) {
                    $drawSeparator = true;
                } else {
                    $drawSeparator = false;
                }

                if ($drawSeparator) {
                    $result .= $this->_decorator->getVerticalRight();

                    $currentUpperColumn = 0;
                    $currentLowerColumn = 0;
                    $currentUpperWidth  = 0;
                    $currentLowerWidth  = 0;

                    // Loop through all column widths
                    foreach ($this->_columnWidths as $columnNum => $columnWidth) {
                        // Add the horizontal line
                        $result .= str_repeat($this->_decorator->getHorizontal(),
                                              $columnWidth);

                        // If this is the last line, break out
                        if (($columnNum + 1) === $totalNumColumns) {
                            break;
                        }

                        // Else check, which connector style has to be used
                        $connector          = 0x0;
                        $currentUpperWidth += $columnWidth;
                        $currentLowerWidth += $columnWidth;

                        if ($lastColumnWidths[$currentUpperColumn] === $currentUpperWidth) {
                            $connector          |= 0x1;
                            $currentUpperColumn += 1;
                            $currentUpperWidth   = 0;
                        } else {
                            $currentUpperWidth += 1;
                        }

                        if ($columnWidths[$currentLowerColumn] === $currentLowerWidth) {
                            $connector          |= 0x2;
                            $currentLowerColumn += 1;
                            $currentLowerWidth   = 0;
                        } else {
                            $currentLowerWidth += 1;
                        }

                        switch ($connector) {
                            case 0x0:
                                $result .= $this->_decorator->getHorizontal();
                                break;

                            case 0x1:
                                $result .= $this->_decorator->getHorizontalUp();
                                break;

                            case 0x2:
                                $result .= $this->_decorator->getHorizontalDown();
                                break;

                            case 0x3:
                                $result .= $this->_decorator->getCross();
                                break;

                            default:
                                // This can never happen, but the CS tells I have to have it ...
                                break;
                        }
                    }

                    $result .= $this->_decorator->getVerticalLeft() . "\n";
                }
            }

            // Add the rendered row to the result
            $result .= $renderedRow;

            // If this is the last row, draw the table bottom
            if (($rowNum + 1) === $numRows) {
                $result .= $this->_decorator->getBottomLeft();

                foreach ($columnWidths as $columnNum => $columnWidth) {
                    $result .= str_repeat($this->_decorator->getHorizontal(),
                                          $columnWidth);

                    if (($columnNum + 1) === $numColumns) {
                        $result .= $this->_decorator->getBottomRight();
                    } else {
                        $result .= $this->_decorator->getHorizontalUp();
                    }
                }

                $result .= "\n";
            }
        }

        return $result;
    }

    /**
     * Magic method which returns the rendered table
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

    }
}
