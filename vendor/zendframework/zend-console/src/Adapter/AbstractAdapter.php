<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Adapter;

use Zend\Console\Charset;
use Zend\Console\Exception;
use Zend\Stdlib\StringUtils;

/**
 * Common console adapter codebase
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Whether or not mbstring is enabled
     *
     * @var null|bool
     */
    protected static $hasMBString;

    /**
     * @var Charset\CharsetInterface
     */
    protected $charset;

    /**
     * Current cursor X position
     *
     * @var int
     */
    protected $posX;

    /**
     * Current cursor Y position
     *
     * @var int
     */
    protected $posY;

    /**
     * Write a chunk of text to console.
     *
     * @param string   $text
     * @param null|int $color
     * @param null|int $bgColor
     */
    public function write($text, $color = null, $bgColor = null)
    {
        //Encode text to match console encoding
        $text = $this->encodeText($text);

        if ($color !== null || $bgColor !== null) {
            echo $this->colorize($text, $color, $bgColor);
        } else {
            echo $text;
        }
    }

    /**
     * Alias for write()
     *
     * @param string   $text
     * @param null|int $color
     * @param null|int $bgColor
     */
    public function writeText($text, $color = null, $bgColor = null)
    {
        return $this->write($text, $color, $bgColor);
    }

    /**
     * Write a single line of text to console and advance cursor to the next line.
     *
     * @param string   $text
     * @param null|int $color
     * @param null|int $bgColor
     */
    public function writeLine($text = "", $color = null, $bgColor = null)
    {
        $this->write($text . PHP_EOL, $color, $bgColor);
    }

    /**
     * Write a piece of text at the coordinates of $x and $y
     *
     *
     * @param string   $text    Text to write
     * @param int      $x       Console X coordinate (column)
     * @param int      $y       Console Y coordinate (row)
     * @param null|int $color
     * @param null|int $bgColor
     */
    public function writeAt($text, $x, $y, $color = null, $bgColor = null)
    {
        $this->setPos($x, $y);
        $this->write($text, $color, $bgColor);
    }

    /**
     * Write a box at the specified coordinates.
     * If X or Y coordinate value is negative, it will be calculated as the distance from far right or bottom edge
     * of the console (respectively).
     *
     * @param int      $x1           Top-left corner X coordinate (column)
     * @param int      $y1           Top-left corner Y coordinate (row)
     * @param int      $x2           Bottom-right corner X coordinate (column)
     * @param int      $y2           Bottom-right corner Y coordinate (row)
     * @param int      $lineStyle    (optional) Box border style.
     * @param int      $fillStyle    (optional) Box fill style or a single character to fill it with.
     * @param int      $color        (optional) Foreground color
     * @param int      $bgColor      (optional) Background color
     * @param null|int $fillColor    (optional) Foreground color of box fill
     * @param null|int $fillBgColor  (optional) Background color of box fill
     * @throws Exception\BadMethodCallException if coordinates are invalid
     */
    public function writeBox(
        $x1,
        $y1,
        $x2,
        $y2,
        $lineStyle = self::LINE_SINGLE,
        $fillStyle = self::FILL_NONE,
        $color = null,
        $bgColor = null,
        $fillColor = null,
        $fillBgColor = null
    ) {
        // Sanitize coordinates
        $x1 = (int) $x1;
        $y1 = (int) $y1;
        $x2 = (int) $x2;
        $y2 = (int) $y2;

        // Translate negative coordinates
        if ($x2 < 0) {
            $x2 = $this->getWidth() - $x2;
        }

        if ($y2 < 0) {
            $y2 = $this->getHeight() - $y2;
        }

        // Validate coordinates
        if ($x1 < 0
            || $y1 < 0
            || $x2 < $x1
            || $y2 < $y1
       ) {
            throw new Exception\BadMethodCallException('Supplied X,Y coordinates are invalid.');
        }

        // Determine charset and dimensions
        $charset = $this->getCharset();
        $width   = $x2 - $x1 + 1;

        if ($width <= 2) {
            $lineStyle = static::LINE_NONE;
        }

        // Activate line drawing
        $this->write($charset::ACTIVATE);

        // Draw horizontal lines
        if ($lineStyle !== static::LINE_NONE) {
            switch ($lineStyle) {
                case static::LINE_SINGLE:
                    $lineChar = $charset::LINE_SINGLE_EW;
                    break;

                case static::LINE_DOUBLE:
                    $lineChar = $charset::LINE_DOUBLE_EW;
                    break;

                case static::LINE_BLOCK:
                default:
                    $lineChar = $charset::LINE_BLOCK_EW;
                    break;
            }

            $this->setPos($x1 + 1, $y1);
            $this->write(str_repeat($lineChar, $width - 2), $color, $bgColor);
            $this->setPos($x1 + 1, $y2);
            $this->write(str_repeat($lineChar, $width - 2), $color, $bgColor);
        }

        // Draw vertical lines and fill
        if (is_numeric($fillStyle)
            && $fillStyle !== static::FILL_NONE) {
            switch ($fillStyle) {
                case static::FILL_SHADE_LIGHT:
                    $fillChar = $charset::SHADE_LIGHT;
                    break;
                case static::FILL_SHADE_MEDIUM:
                    $fillChar = $charset::SHADE_MEDIUM;
                    break;
                case static::FILL_SHADE_DARK:
                    $fillChar = $charset::SHADE_DARK;
                    break;
                case static::FILL_BLOCK:
                default:
                    $fillChar = $charset::BLOCK;
                    break;
            }
        } elseif ($fillStyle) {
            $fillChar = StringUtils::getWrapper()->substr($fillStyle, 0, 1);
        } else {
            $fillChar = ' ';
        }

        if ($lineStyle === static::LINE_NONE) {
            for ($y = $y1; $y <= $y2; $y++) {
                $this->setPos($x1, $y);
                $this->write(str_repeat($fillChar, $width), $fillColor, $fillBgColor);
            }
        } else {
            switch ($lineStyle) {
                case static::LINE_DOUBLE:
                    $lineChar = $charset::LINE_DOUBLE_NS;
                    break;
                case static::LINE_BLOCK:
                    $lineChar = $charset::LINE_BLOCK_NS;
                    break;
                case static::LINE_SINGLE:
                default:
                    $lineChar = $charset::LINE_SINGLE_NS;
                    break;
            }

            for ($y = $y1 + 1; $y < $y2; $y++) {
                $this->setPos($x1, $y);
                $this->write($lineChar, $color, $bgColor);
                $this->write(str_repeat($fillChar, $width - 2), $fillColor, $fillBgColor);
                $this->write($lineChar, $color, $bgColor);
            }
        }

        // Draw corners
        if ($lineStyle !== static::LINE_NONE) {
            if ($color !== null) {
                $this->setColor($color);
            }
            if ($bgColor !== null) {
                $this->setBgColor($bgColor);
            }
            if ($lineStyle === static::LINE_SINGLE) {
                $this->writeAt($charset::LINE_SINGLE_NW, $x1, $y1);
                $this->writeAt($charset::LINE_SINGLE_NE, $x2, $y1);
                $this->writeAt($charset::LINE_SINGLE_SE, $x2, $y2);
                $this->writeAt($charset::LINE_SINGLE_SW, $x1, $y2);
            } elseif ($lineStyle === static::LINE_DOUBLE) {
                $this->writeAt($charset::LINE_DOUBLE_NW, $x1, $y1);
                $this->writeAt($charset::LINE_DOUBLE_NE, $x2, $y1);
                $this->writeAt($charset::LINE_DOUBLE_SE, $x2, $y2);
                $this->writeAt($charset::LINE_DOUBLE_SW, $x1, $y2);
            } elseif ($lineStyle === static::LINE_BLOCK) {
                $this->writeAt($charset::LINE_BLOCK_NW, $x1, $y1);
                $this->writeAt($charset::LINE_BLOCK_NE, $x2, $y1);
                $this->writeAt($charset::LINE_BLOCK_SE, $x2, $y2);
                $this->writeAt($charset::LINE_BLOCK_SW, $x1, $y2);
            }
        }

        // Deactivate line drawing and reset colors
        $this->write($charset::DEACTIVATE);
        $this->resetColor();
    }

    /**
     * Write a block of text at the given coordinates, matching the supplied width and height.
     * In case a line of text does not fit desired width, it will be wrapped to the next line.
     * In case the whole text does not fit in desired height, it will be truncated.
     *
     * @param string   $text    Text to write
     * @param int      $width   Maximum block width. Negative value means distance from right edge.
     * @param int|null $height  Maximum block height. Negative value means distance from bottom edge.
     * @param int      $x       Block X coordinate (column)
     * @param int      $y       Block Y coordinate (row)
     * @param null|int $color   (optional) Text color
     * @param null|int $bgColor (optional) Text background color
     * @throws Exception\InvalidArgumentException
     */
    public function writeTextBlock(
        $text,
        $width,
        $height = null,
        $x = 0,
        $y = 0,
        $color = null,
        $bgColor = null
    ) {
        if ($x < 0 || $y < 0) {
            throw new Exception\InvalidArgumentException('Supplied X,Y coordinates are invalid.');
        }

        if ($width < 1) {
            throw new Exception\InvalidArgumentException('Invalid width supplied.');
        }

        if (null !== $height && $height < 1) {
            throw new Exception\InvalidArgumentException('Invalid height supplied.');
        }

        // ensure the text is not wider than the width
        if (strlen($text) <= $width) {
            // just write the line at the spec'd position
            $this->setPos($x, $y);
            $this->write($text, $color, $bgColor);
            return;
        }

        $text = wordwrap($text, $width, PHP_EOL, true);

        // convert to array of lines
        $lines = explode(PHP_EOL, $text);

        // truncate if height was specified
        if (null !== $height && count($lines) > $height) {
            $lines = array_slice($lines, 0, $height);
        }

        // write each line
        $curY = $y;
        foreach ($lines as $line) {
            $this->setPos($x, $curY);
            $this->write($line, $color, $bgColor);
            $curY++;//next line
        }
    }

    /**
     * Determine and return current console width.
     *
     * @return int
     */
    public function getWidth()
    {
        return 80;
    }

    /**
     * Determine and return current console height.
     *
     * @return int
     */
    public function getHeight()
    {
        return 25;
    }

    /**
     * Determine and return current console width and height.
     *
     * @return int[] array($width, $height)
     */
    public function getSize()
    {
        return array(
            $this->getWidth(),
            $this->getHeight(),
        );
    }

    /**
     * Check if console is UTF-8 compatible
     *
     * @return bool
     */
    public function isUtf8()
    {
        return true;
    }

    /**
     * Set cursor position
     *
     * @param int $x
     * @param int $y
     */
    public function setPos($x, $y)
    {
    }

    /**
     * Show console cursor
     */
    public function showCursor()
    {
    }

    /**
     * Hide console cursor
     */
    public function hideCursor()
    {
    }

    /**
     * Return current console window title.
     *
     * @return string
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * Prepare a string that will be rendered in color.
     *
     * @param  string   $string
     * @param  int      $color
     * @param  null|int $bgColor
     * @return string
     */
    public function colorize($string, $color = null, $bgColor = null)
    {
        return $string;
    }

    /**
     * Change current drawing color.
     *
     * @param int $color
     */
    public function setColor($color)
    {
    }

    /**
     * Change current drawing background color
     *
     * @param int $color
     */
    public function setBgColor($color)
    {
    }

    /**
     * Reset color to console default.
     */
    public function resetColor()
    {
    }

    /**
     * Set Console charset to use.
     *
     * @param Charset\CharsetInterface $charset
     */
    public function setCharset(Charset\CharsetInterface $charset)
    {
        $this->charset = $charset;
    }

    /**
     * Get charset currently in use by this adapter.
     *
     * @return Charset\CharsetInterface $charset
     */
    public function getCharset()
    {
        if ($this->charset === null) {
            $this->charset = $this->getDefaultCharset();
        }

        return $this->charset;
    }

    /**
     * @return Charset\Utf8
     */
    public function getDefaultCharset()
    {
        return new Charset\Utf8;
    }

    /**
     * Clear console screen
     */
    public function clear()
    {
        echo "\f";
    }

    /**
     * Clear line at cursor position
     */
    public function clearLine()
    {
        echo "\r" . str_repeat(" ", $this->getWidth()) . "\r";
    }

    /**
     * Clear console screen
     */
    public function clearScreen()
    {
        return $this->clear();
    }

    /**
     * Read a single line from the console input
     *
     * @param int $maxLength        Maximum response length
     * @return string
     */
    public function readLine($maxLength = 2048)
    {
        $f    = fopen('php://stdin', 'r');
        $line = stream_get_line($f, $maxLength, PHP_EOL);
        fclose($f);
        return rtrim($line, "\n\r");
    }

    /**
     * Read a single character from the console input
     *
     * @param string|null   $mask   A list of allowed chars
     * @return string
     */
    public function readChar($mask = null)
    {
        $f = fopen('php://stdin', 'r');
        do {
            $char = fread($f, 1);
        } while ("" === $char || ($mask !== null && false === strstr($mask, $char)));
        fclose($f);
        return $char;
    }

    /**
     * Encode a text to match console encoding
     *
     * @param  string $text
     * @return string the encoding text
     */
    public function encodeText($text)
    {
        if ($this->isUtf8()) {
            if (StringUtils::isValidUtf8($text)) {
                return $text;
            }

            return utf8_encode($text);
        }

        if (StringUtils::isValidUtf8($text)) {
            return utf8_decode($text);
        }

        return $text;
    }
}
