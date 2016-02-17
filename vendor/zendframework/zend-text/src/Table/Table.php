<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Text\Table;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Text\Table\Decorator\DecoratorInterface as Decorator;

/**
 * Zend\Text\Table\Table enables developers to create tables out of characters
 */
class Table
{
    /**
     * Auto separator settings
     */
    const AUTO_SEPARATE_NONE   = 0x0;
    const AUTO_SEPARATE_HEADER = 0x1;
    const AUTO_SEPARATE_FOOTER = 0x2;
    const AUTO_SEPARATE_ALL    = 0x4;

    /**
     * Decorator used for the table borders
     *
     * @var Decorator
     */
    protected $decorator = null;

    /**
     * List of all column widths
     *
     * @var array
     */
    protected $columnWidths = null;

    /**
     * Rows of the table
     *
     * @var array
     */
    protected $rows = array();

    /**
     * Auto separation mode
     *
     * @var int
     */
    protected $autoSeparate = self::AUTO_SEPARATE_ALL;

    /**
     * Padding for columns
     *
     * @var int
     */
    protected $padding = 0;

    /**
     * Default column aligns for rows created by appendRow(array $data)
     *
     * @var array
     */
    protected $defaultColumnAligns = array();

    /**
     * Plugin loader for decorators
     *
     * @var DecoratorManager
     */
    protected $decoratorManager = null;

    /**
     * Charset which is used for input by default
     *
     * @var string
     */
    protected static $inputCharset = 'utf-8';

    /**
     * Charset which is used internally
     *
     * @var string
     */
    protected static $outputCharset = 'utf-8';

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $skipOptions = array(
        'options',
        'config',
        'defaultColumnAlign',
    );

    /**
     * Create a basic table object
     *
     * @param  array|Traversable $options Configuration options
     * @throws Exception\UnexpectedValueException When no columns widths were set
     */
    public function __construct($options = null)
    {
        // Set options
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }

        // If no decorator was given, use default unicode decorator
        if ($this->decorator === null) {
            if (static::getOutputCharset() === 'utf-8') {
                $this->setDecorator('unicode');
            } else {
                $this->setDecorator('ascii');
            }
        }
    }

    /**
     * Set options from array
     *
     * @param  array $options Configuration for Table
     * @return Table
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->skipOptions)) {
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
     * Set column widths
     *
     * @param  array $columnWidths Widths of all columns
     * @throws Exception\InvalidArgumentException When no columns were supplied
     * @throws Exception\InvalidArgumentException When a column has an invalid width
     * @return Table
     */
    public function setColumnWidths(array $columnWidths)
    {
        if (count($columnWidths) === 0) {
            throw new Exception\InvalidArgumentException('You must supply at least one column');
        }

        foreach ($columnWidths as $columnNum => $columnWidth) {
            if (is_int($columnWidth) === false or $columnWidth < 1) {
                throw new Exception\InvalidArgumentException('Column ' . $columnNum . ' has an invalid column width');
            }
        }

        $this->columnWidths = $columnWidths;

        return $this;
    }

    /**
     * Set auto separation mode
     *
     * @param  int $autoSeparate Auto separation mode
     * @return Table
     */
    public function setAutoSeparate($autoSeparate)
    {
        $this->autoSeparate = (int) $autoSeparate;
        return $this;
    }

    /**
     * Set decorator
     *
     * @param  Decorator|string $decorator Decorator to use
     * @return Table
     */
    public function setDecorator($decorator)
    {
        if (!$decorator instanceof Decorator) {
            $decorator = $this->getDecoratorManager()->get($decorator);
        }

        $this->decorator = $decorator;

        return $this;
    }

    /**
     * Set the column padding
     *
     * @param  int $padding The padding for the columns
     * @return Table
     */
    public function setPadding($padding)
    {
        $this->padding = max(0, (int) $padding);
        return $this;
    }

    /**
     * Get the plugin manager for decorators
     *
     * @return DecoratorManager
     */
    public function getDecoratorManager()
    {
        if ($this->decoratorManager instanceof DecoratorManager) {
            return $this->decoratorManager;
        }

        $this->setDecoratorManager(new DecoratorManager());
        return $this->decoratorManager;
    }

    /**
     * Set the plugin manager instance for decorators
     *
     * @param  DecoratorManager $decoratorManager
     * @return Table
     */
    public function setDecoratorManager(DecoratorManager $decoratorManager)
    {
        $this->decoratorManager = $decoratorManager;
        return $this;
    }

    /**
     * Set default column align for rows created by appendRow(array $data)
     *
     * @param  int $columnNum
     * @param  string  $align
     * @return Table
     */
    public function setDefaultColumnAlign($columnNum, $align)
    {
        $this->defaultColumnAligns[$columnNum] = $align;

        return $this;
    }

    /**
     * Set the input charset for column contents
     *
     * @param string $charset
     */
    public static function setInputCharset($charset)
    {
        static::$inputCharset = strtolower($charset);
    }

    /**
     * Get the input charset for column contents
     *
     * @return string
     */
    public static function getInputCharset()
    {
        return static::$inputCharset;
    }

    /**
     * Set the output charset for column contents
     *
     * @param string $charset
     */
    public static function setOutputCharset($charset)
    {
        static::$outputCharset = strtolower($charset);
    }

    /**
     * Get the output charset for column contents
     *
     * @return string
     */
    public static function getOutputCharset()
    {
        return static::$outputCharset;
    }

    /**
     * Append a row to the table
     *
     * @param  array|Row $row The row to append to the table
     * @throws Exception\InvalidArgumentException When $row is neither an array nor Zend\Text\Table\Row
     * @throws Exception\OverflowException When a row contains too many columns
     * @return Table
     */
    public function appendRow($row)
    {
        if (!is_array($row) && !($row instanceof Row)) {
            throw new Exception\InvalidArgumentException('$row must be an array or instance of Zend\Text\Table\Row');
        }

        if (is_array($row)) {
            if (count($row) > count($this->columnWidths)) {
                throw new Exception\OverflowException('Row contains too many columns');
            }

            $data   = $row;
            $row    = new Row();
            $colNum = 0;
            foreach ($data as $columnData) {
                if (isset($this->defaultColumnAligns[$colNum])) {
                    $align = $this->defaultColumnAligns[$colNum];
                } else {
                    $align = null;
                }

                $row->appendColumn(new Column($columnData, $align));
                $colNum++;
            }
        }

        $this->rows[] = $row;

        return $this;
    }

    /**
     * Render the table
     *
     * @throws Exception\UnexpectedValueException When no rows were added to the table
     * @return string
     */
    public function render()
    {
        // There should be at least one row
        if (count($this->rows) === 0) {
            throw new Exception\UnexpectedValueException('No rows were added to the table yet');
        }

        // Initiate the result variable
        $result = '';

        // Count total columns
        $totalNumColumns = count($this->columnWidths);

        // Check if we have a horizontal character defined
        $hasHorizontal = $this->decorator->getHorizontal() !== '';

        // Now render all rows, starting from the first one
        $numRows = count($this->rows);
        foreach ($this->rows as $rowNum => $row) {
            // Get all column widths
            if (isset($columnWidths) === true) {
                $lastColumnWidths = $columnWidths;
            }

            $renderedRow  = $row->render($this->columnWidths, $this->decorator, $this->padding);
            $columnWidths = $row->getColumnWidths();
            $numColumns   = count($columnWidths);

            // Check what we have to draw
            if ($rowNum === 0 && $hasHorizontal) {
                // If this is the first row, draw the table top
                $result .= $this->decorator->getTopLeft();

                foreach ($columnWidths as $columnNum => $columnWidth) {
                    $result .= str_repeat($this->decorator->getHorizontal(), $columnWidth);

                    if (($columnNum + 1) === $numColumns) {
                        $result .= $this->decorator->getTopRight();
                    } else {
                        $result .= $this->decorator->getHorizontalDown();
                    }
                }

                $result .= "\n";
            } else {
                // Else check if we have to draw the row separator
                if (!$hasHorizontal) {
                    $drawSeparator = false; // there is no horizontal character;
                } elseif ($this->autoSeparate & self::AUTO_SEPARATE_ALL) {
                    $drawSeparator = true;
                } elseif ($rowNum === 1 && $this->autoSeparate & self::AUTO_SEPARATE_HEADER) {
                    $drawSeparator = true;
                } elseif ($rowNum === ($numRows - 1) && $this->autoSeparate & self::AUTO_SEPARATE_FOOTER) {
                    $drawSeparator = true;
                } else {
                    $drawSeparator = false;
                }

                if ($drawSeparator) {
                    $result .= $this->decorator->getVerticalRight();

                    $currentUpperColumn = 0;
                    $currentLowerColumn = 0;
                    $currentUpperWidth  = 0;
                    $currentLowerWidth  = 0;

                    // Add horizontal lines
                    // Loop through all column widths
                    foreach ($this->columnWidths as $columnNum => $columnWidth) {
                        // Add the horizontal line
                        $result .= str_repeat($this->decorator->getHorizontal(), $columnWidth);

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
                                $result .= $this->decorator->getHorizontal();
                                break;

                            case 0x1:
                                $result .= $this->decorator->getHorizontalUp();
                                break;

                            case 0x2:
                                $result .= $this->decorator->getHorizontalDown();
                                break;

                            case 0x3:
                                $result .= $this->decorator->getCross();
                                break;

                            default:
                                // This can never happen, but the CS tells I have to have it ...
                                break;
                        }
                    }

                    $result .= $this->decorator->getVerticalLeft() . "\n";
                }
            }

            // Add the rendered row to the result
            $result .= $renderedRow;

            // If this is the last row, draw the table bottom
            if (($rowNum + 1) === $numRows && $hasHorizontal) {
                $result .= $this->decorator->getBottomLeft();

                foreach ($columnWidths as $columnNum => $columnWidth) {
                    $result .= str_repeat($this->decorator->getHorizontal(), $columnWidth);

                    if (($columnNum + 1) === $numColumns) {
                        $result .= $this->decorator->getBottomRight();
                    } else {
                        $result .= $this->decorator->getHorizontalUp();
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
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
