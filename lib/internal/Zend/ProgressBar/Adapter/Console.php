<?php
/**
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
 * @category   Zend
 * @package    Zend_ProgressBar
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Console.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_ProgressBar_Adapter
 */
#require_once 'Zend/ProgressBar/Adapter.php';

/**
 * @see Zend_Text_MultiByte
 */
#require_once 'Zend/Text/MultiByte.php';

/**
 * Zend_ProgressBar_Adapter_Console offers a text-based progressbar for console
 * applications
 *
 * @category  Zend
 * @package   Zend_ProgressBar
 * @uses      Zend_ProgressBar_Adapter_Interface
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_ProgressBar_Adapter_Console extends Zend_ProgressBar_Adapter
{
    /**
     * Percentage value of the progress
     */
    const ELEMENT_PERCENT = 'ELEMENT_PERCENT';

    /**
     * Visual value of the progress
     */
    const ELEMENT_BAR = 'ELEMENT_BAR';

    /**
     * ETA of the progress
     */
    const ELEMENT_ETA = 'ELEMENT_ETA';

    /**
     * Text part of the progress
     */
    const ELEMENT_TEXT = 'ELEMENT_TEXT';

    /**
     * Finish action: End of Line
     */
    const FINISH_ACTION_EOL = 'FINISH_ACTION_EOL';

    /**
     * Finish action: Clear Line
     */
    const FINISH_ACTION_CLEAR_LINE = 'FINISH_ACTION_CLEAR_LINE';

    /**
     * Finish action: None
     */
    const FINISH_ACTION_NONE = 'FINISH_ACTION_NONE';

    /**
     * Width of the progressbar
     *
     * @var integer
     */
    protected $_width = null;

    /**
     * Elements to display
     *
     * @var array
     */
    protected $_elements = array(self::ELEMENT_PERCENT,
                                 self::ELEMENT_BAR,
                                 self::ELEMENT_ETA);

    /**
     * Which action to do at finish call
     *
     * @var string
     */
    protected $_finishAction = self::FINISH_ACTION_EOL;

    /**
     * Width of the bar element
     *
     * @var integer
     */
    protected $_barWidth;

    /**
     * Left character(s) within the bar
     *
     * @var string
     */
    protected $_barLeftChar = '#';

    /**
     * Indicator character(s) within the bar
     *
     * @var string
     */
    protected $_barIndicatorChar = '';

    /**
     * Right character(s) within the bar
     *
     * @var string
     */
    protected $_barRightChar = '-';

    /**
     * Output-stream, when STDOUT is not defined (e.g. in CGI) or set manually
     *
     * @var resource
     */
    protected $_outputStream = null;

    /**
     * Width of the text element
     *
     * @var string
     */
    protected $_textWidth = 20;

    /**
     * Wether the output started yet or not
     *
     * @var boolean
     */
    protected $_outputStarted = false;

    /**
     * Charset of text element
     *
     * @var string
     */
    protected $_charset = 'utf-8';

    /**
     * Defined by Zend_ProgressBar_Adapter
     *
     * @param null|array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        // Call parent constructor with options
        parent::__construct($options);

        // Check if a width was set, else use auto width
        if ($this->_width === null) {
            $this->setWidth();
        }
    }

    /**
     * Close local stdout, when open
     */
    public function __destruct()
    {
        if ($this->_outputStream !== null) {
            fclose($this->_outputStream);
        }
    }

    /**
     * Set a different output-stream
     *
     * @param  string $resource
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setOutputStream($resource)
    {
       $stream = @fopen($resource, 'w');

       if ($stream === false) {
            #require_once 'Zend/ProgressBar/Adapter/Exception.php';
            throw new Zend_ProgressBar_Adapter_Exception('Unable to open stream');
       }

       if ($this->_outputStream !== null) {
           fclose($this->_outputStream);
       }

       $this->_outputStream = $stream;
    }

    /**
     * Get the current output stream
     *
     * @return resource
     */
    public function getOutputStream()
    {
        if ($this->_outputStream === null) {
            if (!defined('STDOUT')) {
                $this->_outputStream = fopen('php://stdout', 'w');
            } else {
                return STDOUT;
            }
        }

        return $this->_outputStream;
    }

    /**
     * Set the width of the progressbar
     *
     * @param  integer $width
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setWidth($width = null)
    {
        if ($width === null || !is_integer($width)) {
            if (substr(PHP_OS, 0, 3) === 'WIN') {
                // We have to default to 79 on windows, because the windows
                // terminal always has a fixed width of 80 characters and the
                // cursor is counted to the line, else windows would line break
                // after every update.
                $this->_width = 79;
            } else {
                // Set the default width of 80
                $this->_width = 80;

                // Try to determine the width through stty
                if (preg_match('#\d+ (\d+)#', @shell_exec('stty size'), $match) === 1) {
                    $this->_width = (int) $match[1];
                } else if (preg_match('#columns = (\d+);#', @shell_exec('stty'), $match) === 1) {
                    $this->_width = (int) $match[1];
                }
            }
        } else {
            $this->_width = (int) $width;
        }

        $this->_calculateBarWidth();

        return $this;
    }

    /**
     * Set the elements to display with the progressbar
     *
     * @param  array $elements
     * @throws Zend_ProgressBar_Adapter_Exception When an invalid element is foudn in the array
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setElements(array $elements)
    {
        $allowedElements = array(self::ELEMENT_PERCENT,
                                 self::ELEMENT_BAR,
                                 self::ELEMENT_ETA,
                                 self::ELEMENT_TEXT);

        if (count(array_diff($elements, $allowedElements)) > 0) {
            #require_once 'Zend/ProgressBar/Adapter/Exception.php';
            throw new Zend_ProgressBar_Adapter_Exception('Invalid element found in $elements array');
        }

        $this->_elements = $elements;

        $this->_calculateBarWidth();

        return $this;
    }

    /**
     * Set the left-hand character for the bar
     *
     * @param  string $char
     * @throws Zend_ProgressBar_Adapter_Exception When character is empty
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setBarLeftChar($char)
    {
        if (empty($char)) {
            #require_once 'Zend/ProgressBar/Adapter/Exception.php';
            throw new Zend_ProgressBar_Adapter_Exception('Character may not be empty');
        }

        $this->_barLeftChar = (string) $char;

        return $this;
    }

    /**
     * Set the right-hand character for the bar
     *
     * @param  string $char
     * @throws Zend_ProgressBar_Adapter_Exception When character is empty
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setBarRightChar($char)
    {
        if (empty($char)) {
            #require_once 'Zend/ProgressBar/Adapter/Exception.php';
            throw new Zend_ProgressBar_Adapter_Exception('Character may not be empty');
        }

        $this->_barRightChar = (string) $char;

        return $this;
    }

    /**
     * Set the indicator character for the bar
     *
     * @param  string $char
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setBarIndicatorChar($char)
    {
        $this->_barIndicatorChar = (string) $char;

        return $this;
    }

    /**
     * Set the width of the text element
     *
     * @param  integer $width
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setTextWidth($width)
    {
        $this->_textWidth = (int) $width;

        $this->_calculateBarWidth();

        return $this;
    }

    /**
     * Set the charset of the text element
     *
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;
    }

    /**
     * Set the finish action
     *
     * @param  string $action
     * @throws Zend_ProgressBar_Adapter_Exception When an invalid action is specified
     * @return Zend_ProgressBar_Adapter_Console
     */
    public function setFinishAction($action)
    {
        $allowedActions = array(self::FINISH_ACTION_CLEAR_LINE,
                                self::FINISH_ACTION_EOL,
                                self::FINISH_ACTION_NONE);

        if (!in_array($action, $allowedActions)) {
            #require_once 'Zend/ProgressBar/Adapter/Exception.php';
            throw new Zend_ProgressBar_Adapter_Exception('Invalid finish action specified');
        }

        $this->_finishAction = $action;

        return $this;
    }

    /**
     * Defined by Zend_ProgressBar_Adapter_Interface
     *
     * @param  float   $current       Current progress value
     * @param  float   $max           Max progress value
     * @param  float   $percent       Current percent value
     * @param  integer $timeTaken     Taken time in seconds
     * @param  integer $timeRemaining Remaining time in seconds
     * @param  string  $text          Status text
     * @return void
     */
    public function notify($current, $max, $percent, $timeTaken, $timeRemaining, $text)
    {
        // See if we must clear the line
        if ($this->_outputStarted) {
            $data = str_repeat("\x08", $this->_width);
        } else {
            $data = '';
            $this->_outputStarted = true;
        }

        // Build all elements
        $renderedElements = array();

        foreach ($this->_elements as $element) {
            switch ($element) {
                case self::ELEMENT_BAR:
                    $visualWidth = $this->_barWidth - 2;
                    $bar         = '[';

                    $indicatorWidth = strlen($this->_barIndicatorChar);

                    $doneWidth = min($visualWidth - $indicatorWidth, round($visualWidth * $percent));
                    if ($doneWidth > 0) {
                        $bar .= substr(str_repeat($this->_barLeftChar, ceil($doneWidth / strlen($this->_barLeftChar))), 0, $doneWidth);
                    }

                    $bar .= $this->_barIndicatorChar;

                    $leftWidth = $visualWidth - $doneWidth - $indicatorWidth;
                    if ($leftWidth > 0) {
                        $bar .= substr(str_repeat($this->_barRightChar, ceil($leftWidth / strlen($this->_barRightChar))), 0, $leftWidth);
                    }

                    $bar .= ']';

                    $renderedElements[] = $bar;
                    break;

                case self::ELEMENT_PERCENT:
                    $renderedElements[] = str_pad(round($percent * 100), 3, ' ', STR_PAD_LEFT) . '%';
                    break;

                case self::ELEMENT_ETA:
                    // In the first 5 seconds we don't get accurate results,
                    // this skipping technique is found in many progressbar
                    // implementations.
                    if ($timeTaken < 5) {
                        $renderedElements[] = str_repeat(' ', 12);
                        break;
                    }

                    if ($timeRemaining === null || $timeRemaining > 86400) {
                        $etaFormatted = '??:??:??';
                    } else {
                        $hours   = floor($timeRemaining / 3600);
                        $minutes = floor(($timeRemaining % 3600) / 60);
                        $seconds = ($timeRemaining % 3600 % 60);

                        $etaFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    }

                    $renderedElements[] = 'ETA ' . $etaFormatted;
                    break;

                case self::ELEMENT_TEXT:
                    $renderedElements[] = Zend_Text_MultiByte::strPad(substr($text, 0, $this->_textWidth), $this->_textWidth, ' ', STR_PAD_RIGHT, $this->_charset);
                    break;
            }
        }

        $data .= implode(' ', $renderedElements);

        // Output line data
        $this->_outputData($data);
    }

    /**
     * Defined by Zend_ProgressBar_Adapter_Interface
     *
     * @return void
     */
    public function finish()
    {
        switch ($this->_finishAction) {
            case self::FINISH_ACTION_EOL:
                $this->_outputData(PHP_EOL);
                break;

            case self::FINISH_ACTION_CLEAR_LINE:
                if ($this->_outputStarted) {
                    $data = str_repeat("\x08", $this->_width)
                          . str_repeat(' ', $this->_width)
                          . str_repeat("\x08", $this->_width);

                    $this->_outputData($data);
                }
                break;

            case self::FINISH_ACTION_NONE:
                break;
        }
    }

    /**
     * Calculate the bar width when other elements changed
     *
     * @return void
     */
    protected function _calculateBarWidth()
    {
        if (in_array(self::ELEMENT_BAR, $this->_elements)) {
            $barWidth = $this->_width;

            if (in_array(self::ELEMENT_PERCENT, $this->_elements)) {
                $barWidth -= 4;
            }

            if (in_array(self::ELEMENT_ETA, $this->_elements)) {
                $barWidth -= 12;
            }

            if (in_array(self::ELEMENT_TEXT, $this->_elements)) {
                $barWidth -= $this->_textWidth;
            }

            $this->_barWidth = $barWidth - (count($this->_elements) - 1);
        }
    }

    /**
     * Outputs given data to STDOUT.
     *
     * This split-off is required for unit-testing.
     *
     * @param  string $data
     * @return void
     */
    protected function _outputData($data)
    {
        fwrite($this->getOutputStream(), $data);
    }
}