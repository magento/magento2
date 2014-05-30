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
 * @category   Zend
 * @package    Zend_Barcode
 * @subpackage Object
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ObjectAbstract.php 23400 2010-11-19 17:18:31Z mikaelkael $
 */

/**
 * Class for generate Barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Barcode_Object_ObjectAbstract
{
    /**
     * Namespace of the barcode for autoloading
     * @var string
     */
    protected $_barcodeNamespace = 'Zend_Barcode_Object';

    /**
     * Set of drawing instructions
     * @var array
     */
    protected $_instructions = array();

    /**
     * Barcode type
     * @var string
     */
    protected $_type = null;

    /**
     * Height of the object
     * @var integer
     */
    protected $_height = null;

    /**
     * Width of the object
     * @var integer
     */
    protected $_width = null;

    /**
     * Height of the bar
     * @var integer
     */
    protected $_barHeight = 50;

    /**
     * Width of a thin bar
     * @var integer
     */
    protected $_barThinWidth = 1;

    /**
     * Width of a thick bar
     * @var integer
     */
    protected $_barThickWidth = 3;

    /**
     * Factor to multiply bar and font measure
     * (barHeight, barThinWidth, barThickWidth & fontSize)
     * @var integer
     */
    protected $_factor = 1;

    /**
     * Font and bars color of the object
     * @var integer
     */
    protected $_foreColor = 0x000000;

    /**
     * Background color of the object
     * @var integer
     */
    protected $_backgroundColor = 0xFFFFFF;

    /**
     * Activate/deactivate border of the object
     * @var boolean
     */
    protected $_withBorder = false;

    /**
     * Activate/deactivate drawing of quiet zones
     * @var boolean
     */
    protected $_withQuietZones = true;

    /**
     * Force quiet zones even if
     * @var boolean
     */
    protected $_mandatoryQuietZones = false;

    /**
     * Orientation of the barcode in degrees
     * @var float
     */
    protected $_orientation = 0;

    /**
     * Offset from the top the object
     * (calculated from the orientation)
     * @var integer
     */
    protected $_offsetTop = null;

    /**
     * Offset from the left the object
     * (calculated from the orientation)
     * @var integer
     */
    protected $_offsetLeft = null;

    /**
     * Text to display
     * @var string
     */
    protected $_text = null;

    /**
     * Display (or not) human readable text
     * @var boolean
     */
    protected $_drawText = true;

    /**
     * Adjust (or not) position of human readable characters with barcode
     * @var boolean
     */
    protected $_stretchText = false;

    /**
     * Font resource
     *  - integer (1 to 5): corresponds to GD included fonts
     *  - string: corresponds to path of a TTF font
     * @var integer|string
     */
    protected $_font = null;

    /**
     * Font size
     * @var float
     */
    protected $_fontSize = 10;

    /**
     * Drawing of checksum
     * @var boolean
     */
    protected $_withChecksum = false;

    /**
     * Drawing of checksum inside text
     * @var boolean
     */
    protected $_withChecksumInText = false;

    /**
     * Fix barcode length (numeric or string like 'even')
     * @var $_barcodeLength integer | string
     */
    protected $_barcodeLength = null;

    /**
     * Activate automatic addition of leading zeros
     * if barcode length is fixed
     * @var $_addLeadingZeros boolean
     */
    protected $_addLeadingZeros = true;

    /**
     * Activation of mandatory checksum
     * to deactivate unauthorized modification
     * @var $_mandatoryChecksum boolean
     */
    protected $_mandatoryChecksum = false;

    /**
     * Character used to substitute checksum character for validation
     * @var $_substituteChecksumCharacter mixed
     */
    protected $_substituteChecksumCharacter = 0;

    /**
     * TTF font name: can be set before instanciation of the object
     * @var string
     */
    protected static $_staticFont = null;

    /**
     * Constructor
     * @param array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->_getDefaultOptions();
        if (self::$_staticFont !== null) {
            $this->_font = self::$_staticFont;
        }
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->_type = strtolower(substr(get_class($this), strlen($this->_barcodeNamespace) + 1));
        if ($this->_mandatoryChecksum) {
            $this->_withChecksum = true;
            $this->_withChecksumInText = true;
        }
    }

    /**
     * Set default options for particular object
     * @return void
     */
    protected function _getDefaultOptions()
    {
    }

    /**
     * Set barcode state from options array
     * @param  array $options
     * @return Zend_Barcode_Object
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set barcode state from config object
     * @param Zend_Config $config
     * @return Zend_Barcode_Object
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set barcode namespace for autoloading
     *
     * @param string $namespace
     * @return Zend_Barcode_Object
     */
    public function setBarcodeNamespace($namespace)
    {
        $this->_barcodeNamespace = $namespace;
        return $this;
    }

    /**
     * Retrieve barcode namespace
     *
     * @return string
     */
    public function getBarcodeNamespace()
    {
        return $this->_barcodeNamespace;
    }

    /**
     * Retrieve type of barcode
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set height of the barcode bar
     * @param integer $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setBarHeight($value)
    {
        if (intval($value) <= 0) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Bar height must be greater than 0'
            );
        }
        $this->_barHeight = intval($value);
        return $this;
    }

    /**
     * Get height of the barcode bar
     * @return integer
     */
    public function getBarHeight()
    {
        return $this->_barHeight;
    }

    /**
     * Set thickness of thin bar
     * @param integer $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setBarThinWidth($value)
    {
        if (intval($value) <= 0) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Bar width must be greater than 0'
            );
        }
        $this->_barThinWidth = intval($value);
        return $this;
    }

    /**
     * Get thickness of thin bar
     * @return integer
     */
    public function getBarThinWidth()
    {
        return $this->_barThinWidth;
    }

    /**
     * Set thickness of thick bar
     * @param integer $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setBarThickWidth($value)
    {
        if (intval($value) <= 0) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Bar width must be greater than 0'
            );
        }
        $this->_barThickWidth = intval($value);
        return $this;
    }

    /**
     * Get thickness of thick bar
     * @return integer
     */
    public function getBarThickWidth()
    {
        return $this->_barThickWidth;
    }

    /**
     * Set factor applying to
     * thinBarWidth - thickBarWidth - barHeight - fontSize
     * @param float $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setFactor($value)
    {
        if (floatval($value) <= 0) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Factor must be greater than 0'
            );
        }
        $this->_factor = floatval($value);
        return $this;
    }

    /**
     * Get factor applying to
     * thinBarWidth - thickBarWidth - barHeight - fontSize
     * @return integer
     */
    public function getFactor()
    {
        return $this->_factor;
    }

    /**
     * Set color of the barcode and text
     * @param string $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setForeColor($value)
    {
        if (preg_match('`\#[0-9A-F]{6}`', $value)) {
            $this->_foreColor = hexdec($value);
        } elseif (is_numeric($value) && $value >= 0 && $value <= 16777125) {
            $this->_foreColor = intval($value);
        } else {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Text color must be set as #[0-9A-F]{6}'
            );
        }
        return $this;
    }

    /**
     * Retrieve color of the barcode and text
     * @return unknown
     */
    public function getForeColor()
    {
        return $this->_foreColor;
    }

    /**
     * Set the color of the background
     * @param integer $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setBackgroundColor($value)
    {
        if (preg_match('`\#[0-9A-F]{6}`', $value)) {
            $this->_backgroundColor = hexdec($value);
        } elseif (is_numeric($value) && $value >= 0 && $value <= 16777125) {
            $this->_backgroundColor = intval($value);
        } else {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Background color must be set as #[0-9A-F]{6}'
            );
        }
        return $this;
    }

    /**
     * Retrieve background color of the image
     * @return integer
     */
    public function getBackgroundColor()
    {
        return $this->_backgroundColor;
    }

    /**
     * Activate/deactivate drawing of the bar
     * @param boolean $value
     * @return Zend_Barcode_Object
     */
    public function setWithBorder($value)
    {
        $this->_withBorder = (bool) $value;
        return $this;
    }

    /**
     * Retrieve if border are draw or not
     * @return boolean
     */
    public function getWithBorder()
    {
        return $this->_withBorder;
    }

    /**
     * Activate/deactivate drawing of the quiet zones
     * @param boolean $value
     * @return Zend_Barcode_Object
     */
    public function setWithQuietZones($value)
    {
        $this->_withQuietZones = (bool) $value;
        return $this;
    }

    /**
     * Retrieve if quiet zones are draw or not
     * @return boolean
     */
    public function getWithQuietZones()
    {
        return $this->_withQuietZones;
    }

    /**
     * Allow fast inversion of font/bars color and background color
     * @return Zend_Barcode_Object
     */
    public function setReverseColor()
    {
        $tmp                    = $this->_foreColor;
        $this->_foreColor       = $this->_backgroundColor;
        $this->_backgroundColor = $tmp;
        return $this;
    }

    /**
     * Set orientation of barcode and text
     * @param float $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setOrientation($value)
    {
        $this->_orientation = floatval($value) - floor(floatval($value) / 360) * 360;
        return $this;
    }

    /**
     * Retrieve orientation of barcode and text
     * @return float
     */
    public function getOrientation()
    {
        return $this->_orientation;
    }

    /**
     * Set text to encode
     * @param string $value
     * @return Zend_Barcode_Object
     */
    public function setText($value)
    {
        $this->_text = trim($value);
        return $this;
    }

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getText()
    {
        $text = $this->_text;
        if ($this->_withChecksum) {
            $text .= $this->getChecksum($this->_text);
        }
        return $this->_addLeadingZeros($text);
    }

    /**
     * Automatically add leading zeros if barcode length is fixed
     * @param string $text
     * @param boolean $withoutChecksum
     */
    protected function _addLeadingZeros($text, $withoutChecksum = false)
    {
        if ($this->_barcodeLength && $this->_addLeadingZeros) {
            $omitChecksum = (int) ($this->_withChecksum && $withoutChecksum);
            if (is_int($this->_barcodeLength)) {
                $length = $this->_barcodeLength - $omitChecksum;
                if (strlen($text) < $length) {
                    $text = str_repeat('0', $length - strlen($text)) . $text;
                }
            } else {
                if ($this->_barcodeLength == 'even') {
                    $text = ((strlen($text) - $omitChecksum) % 2 ? '0' . $text : $text);
                }
            }
        }
        return $text;
    }

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getRawText()
    {
        return $this->_text;
    }

    /**
     * Retrieve text to display
     * @return string
     */
    public function getTextToDisplay()
    {
        if ($this->_withChecksumInText) {
            return $this->getText();
        } else {
            return $this->_addLeadingZeros($this->_text, true);
        }
    }

    /**
     * Activate/deactivate drawing of text to encode
     * @param boolean $value
     * @return Zend_Barcode_Object
     */
    public function setDrawText($value)
    {
        $this->_drawText = (bool) $value;
        return $this;
    }

    /**
     * Retrieve if drawing of text to encode is enabled
     * @return boolean
     */
    public function getDrawText()
    {
        return $this->_drawText;
    }

    /**
     * Activate/deactivate the adjustment of the position
     * of the characters to the position of the bars
     * @param boolean $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setStretchText($value)
    {
        $this->_stretchText = (bool) $value;
        return $this;
    }

    /**
     * Retrieve if the adjustment of the position of the characters
     * to the position of the bars is enabled
     * @return boolean
     */
    public function getStretchText()
    {
        return $this->_stretchText;
    }

    /**
     * Activate/deactivate the automatic generation
     * of the checksum character
     * added to the barcode text
     * @param boolean $value
     * @return Zend_Barcode_Object
     */
    public function setWithChecksum($value)
    {
        if (!$this->_mandatoryChecksum) {
            $this->_withChecksum = (bool) $value;
        }
        return $this;
    }

    /**
     * Retrieve if the checksum character is automatically
     * added to the barcode text
     * @return boolean
     */
    public function getWithChecksum()
    {
        return $this->_withChecksum;
    }

    /**
     * Activate/deactivate the automatic generation
     * of the checksum character
     * added to the barcode text
     * @param boolean $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setWithChecksumInText($value)
    {
        if (!$this->_mandatoryChecksum) {
            $this->_withChecksumInText = (bool) $value;
        }
        return $this;
    }

    /**
     * Retrieve if the checksum character is automatically
     * added to the barcode text
     * @return boolean
     */
    public function getWithChecksumInText()
    {
        return $this->_withChecksumInText;
    }

    /**
     * Set the font for all instances of barcode
     * @param string $font
     * @return void
     */
    public static function setBarcodeFont($font)
    {
        if (is_string($font) || (is_int($font) && $font >= 1 && $font <= 5)) {
            self::$_staticFont = $font;
        }
    }

    /**
     * Set the font:
     *  - if integer between 1 and 5, use gd built-in fonts
     *  - if string, $value is assumed to be the path to a TTF font
     * @param integer|string $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setFont($value)
    {
        if (is_int($value) && $value >= 1 && $value <= 5) {
            if (!extension_loaded('gd')) {
                #require_once 'Zend/Barcode/Object/Exception.php';
                throw new Zend_Barcode_Object_Exception(
                    'GD extension is required to use numeric font'
                );
            }

            // Case of numeric font with GD
            $this->_font = $value;

            // In this case font size is given by:
            $this->_fontSize = imagefontheight($value);
        } elseif (is_string($value)) {
            $this->_font = $value;
        } else {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(sprintf(
                'Invalid font "%s" provided to setFont()',
                $value
            ));
        }
        return $this;
    }

    /**
     * Retrieve the font
     * @return integer|string
     */
    public function getFont()
    {
        return $this->_font;
    }

    /**
     * Set the size of the font in case of TTF
     * @param float $value
     * @return Zend_Barcode_Object
     * @throw Zend_Barcode_Object_Exception
     */
    public function setFontSize($value)
    {
        if (is_numeric($this->_font)) {
            // Case of numeric font with GD
            return $this;
        }

        if (!is_numeric($value)) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Font size must be a numeric value'
            );
        }

        $this->_fontSize = $value;
        return $this;
    }

    /**
     * Retrieve the size of the font in case of TTF
     * @return float
     */
    public function getFontSize()
    {
        return $this->_fontSize;
    }

    /**
     * Quiet zone before first bar
     * and after the last bar
     * @return integer
     */
    public function getQuietZone()
    {
        if ($this->_withQuietZones || $this->_mandatoryQuietZones) {
            return 10 * $this->_barThinWidth * $this->_factor;
        } else {
            return 0;
        }
    }

    /**
     * Add an instruction in the array of instructions
     * @param array $instruction
     */
    protected function _addInstruction(array $instruction)
    {
        $this->_instructions[] = $instruction;
    }

    /**
     * Retrieve the set of drawing instructions
     * @return array
     */
    public function getInstructions()
    {
        return $this->_instructions;
    }

    /**
     * Add a polygon drawing instruction in the set of instructions
     * @param array $points
     * @param integer $color
     * @param boolean $filled
     */
    protected function _addPolygon(array $points, $color = null, $filled = true)
    {
        if ($color === null) {
            $color = $this->_foreColor;
        }
        $this->_addInstruction(array(
            'type'   => 'polygon',
            'points' => $points,
            'color'  => $color,
            'filled' => $filled,
        ));
    }

    /**
     * Add a text drawing instruction in the set of instructions
     * @param string $text
     * @param float $size
     * @param array $position
     * @param string $font
     * @param integer $color
     * @param string $alignment
     * @param float $orientation
     */
    protected function _addText(
        $text,
        $size,
        $position,
        $font,
        $color,
        $alignment = 'center',
        $orientation = 0
    ) {
        if ($color === null) {
            $color = $this->_foreColor;
        }
        $this->_addInstruction(array(
            'type'        => 'text',
            'text'        => $text,
            'size'        => $size,
            'position'    => $position,
            'font'        => $font,
            'color'       => $color,
            'alignment'   => $alignment,
            'orientation' => $orientation,
        ));
    }

    /**
     * Checking of parameters after all settings
     * @return void
     */
    public function checkParams()
    {
        $this->_checkText();
        $this->_checkFontAndOrientation();
        $this->_checkParams();
        return true;
    }

    /**
     * Check if a text is really provided to barcode
     * @return void
     * @throw Zend_Barcode_Object_Exception
     */
    protected function _checkText($value = null)
    {
        if ($value === null) {
            $value = $this->_text;
        }
        if (!strlen($value)) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'A text must be provide to Barcode before drawing'
            );
        }
        $this->validateText($value);
    }

    /**
     * Check the ratio between the thick and the thin bar
     * @param integer $min
     * @param integer $max
     * @return void
     * @throw Zend_Barcode_Object_Exception
     */
    protected function _checkRatio($min = 2, $max = 3)
    {
        $ratio = $this->_barThickWidth / $this->_barThinWidth;
        if (!($ratio >= $min && $ratio <= $max)) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(sprintf(
                'Ratio thick/thin bar must be between %0.1f and %0.1f (actual %0.3f)',
                $min,
                $max,
                $ratio
            ));
        }
    }

    /**
     * Drawing with an angle is just allow TTF font
     * @return void
     * @throw Zend_Barcode_Object_Exception
     */
    protected function _checkFontAndOrientation()
    {
        if (is_numeric($this->_font) && $this->_orientation != 0) {
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception(
                'Only drawing with TTF font allow orientation of the barcode.'
            );
        }
    }

    /**
     * Width of the result image
     * (before any rotation)
     * @return integer
     */
    protected function _calculateWidth()
    {
        return (int) $this->_withBorder
            + $this->_calculateBarcodeWidth()
            + (int) $this->_withBorder;
    }

    /**
     * Calculate the width of the barcode
     * @return integer
     */
    abstract protected function _calculateBarcodeWidth();

    /**
     * Height of the result object
     * @return integer
     */
    protected function _calculateHeight()
    {
        return (int) $this->_withBorder * 2
            + $this->_calculateBarcodeHeight()
            + (int) $this->_withBorder * 2;
    }

    /**
     * Height of the barcode
     * @return integer
     */
    protected function _calculateBarcodeHeight()
    {
        $textHeight = 0;
        $extraHeight = 0;
        if ($this->_drawText) {
            $textHeight += $this->_fontSize;
            $extraHeight = 2;
        }
        return ($this->_barHeight + $textHeight) * $this->_factor + $extraHeight;
    }

    /**
     * Get height of the result object
     * @return integer
     */
    public function getHeight($recalculate = false)
    {
        if ($this->_height === null || $recalculate) {
            $this->_height =
                abs($this->_calculateHeight() * cos($this->_orientation / 180 * pi()))
                + abs($this->_calculateWidth() * sin($this->_orientation / 180 * pi()));
        }
        return $this->_height;
    }

    /**
     * Get width of the result object
     * @return integer
     */
    public function getWidth($recalculate = false)
    {
        if ($this->_width === null || $recalculate) {
            $this->_width =
                abs($this->_calculateWidth() * cos($this->_orientation / 180 * pi()))
                + abs($this->_calculateHeight() * sin($this->_orientation / 180 * pi()));
        }
        return $this->_width;
    }

    /**
     * Calculate the offset from the left of the object
     * if an orientation is activated
     * @param boolean $recalculate
     * @return float
     */
    public function getOffsetLeft($recalculate = false)
    {
        if ($this->_offsetLeft === null || $recalculate) {
            $this->_offsetLeft = - min(array(
                0 * cos(
                        $this->_orientation / 180 * pi()) - 0 * sin(
                        $this->_orientation / 180 * pi()),
                0 * cos(
                        $this->_orientation / 180 * pi()) - $this->_calculateBarcodeHeight() * sin(
                        $this->_orientation / 180 * pi()),
                $this->_calculateBarcodeWidth() * cos(
                        $this->_orientation / 180 * pi()) - $this->_calculateBarcodeHeight() * sin(
                        $this->_orientation / 180 * pi()),
                $this->_calculateBarcodeWidth() * cos(
                        $this->_orientation / 180 * pi()) - 0 * sin(
                        $this->_orientation / 180 * pi()),
            ));
        }
        return $this->_offsetLeft;
    }

    /**
     * Calculate the offset from the top of the object
     * if an orientation is activated
     * @param boolean $recalculate
     * @return float
     */
    public function getOffsetTop($recalculate = false)
    {
        if ($this->_offsetTop === null || $recalculate) {
            $this->_offsetTop = - min(array(
                0 * cos(
                        $this->_orientation / 180 * pi()) + 0 * sin(
                        $this->_orientation / 180 * pi()),
                $this->_calculateBarcodeHeight() * cos(
                        $this->_orientation / 180 * pi()) + 0 * sin(
                        $this->_orientation / 180 * pi()),
                $this->_calculateBarcodeHeight() * cos(
                        $this->_orientation / 180 * pi()) + $this->_calculateBarcodeWidth() * sin(
                        $this->_orientation / 180 * pi()),
                0 * cos(
                        $this->_orientation / 180 * pi()) + $this->_calculateBarcodeWidth() * sin(
                        $this->_orientation / 180 * pi()),
            ));
        }
        return $this->_offsetTop;
    }

    /**
     * Apply rotation on a point in X/Y dimensions
     * @param float $x1     x-position before rotation
     * @param float $y1     y-position before rotation
     * @return array        Array of two elements corresponding to the new XY point
     */
    protected function _rotate($x1, $y1)
    {
        $x2 = $x1 * cos($this->_orientation / 180 * pi())
            - $y1 * sin($this->_orientation / 180 * pi())
            + $this->getOffsetLeft();
        $y2 = $y1 * cos($this->_orientation / 180 * pi())
            + $x1 * sin($this->_orientation / 180 * pi())
            + $this->getOffsetTop();
        return array(intval($x2) , intval($y2));
    }

    /**
     * Complete drawing of the barcode
     * @return array Table of instructions
     */
    public function draw()
    {
        $this->checkParams();
        $this->_drawBarcode();
        $this->_drawBorder();
        $this->_drawText();
        return $this->getInstructions();
    }

    /**
     * Draw the barcode
     * @return void
     */
    protected function _drawBarcode()
    {
        $barcodeTable = $this->_prepareBarcode();

        $this->_preDrawBarcode();

        $xpos = (int) $this->_withBorder;
        $ypos = (int) $this->_withBorder;

        $point1 = $this->_rotate(0, 0);
        $point2 = $this->_rotate(0, $this->_calculateHeight() - 1);
        $point3 = $this->_rotate(
            $this->_calculateWidth() - 1,
            $this->_calculateHeight() - 1
        );
        $point4 = $this->_rotate($this->_calculateWidth() - 1, 0);

        $this->_addPolygon(array(
            $point1,
            $point2,
            $point3,
            $point4
        ), $this->_backgroundColor);

        $xpos     += $this->getQuietZone();
        $barLength = $this->_barHeight * $this->_factor;

        foreach ($barcodeTable as $bar) {
            $width = $bar[1] * $this->_factor;
            if ($bar[0]) {
                $point1 = $this->_rotate($xpos, $ypos + $bar[2] * $barLength);
                $point2 = $this->_rotate($xpos, $ypos + $bar[3] * $barLength);
                $point3 = $this->_rotate(
                    $xpos + $width - 1,
                    $ypos + $bar[3] * $barLength
                );
                $point4 = $this->_rotate(
                    $xpos + $width - 1,
                    $ypos + $bar[2] * $barLength
                );
                $this->_addPolygon(array(
                    $point1,
                    $point2,
                    $point3,
                    $point4,
                ));
            }
            $xpos += $width;
        }

        $this->_postDrawBarcode();
    }

    /**
     * Partial function to draw border
     * @return void
     */
    protected function _drawBorder()
    {
        if ($this->_withBorder) {
            $point1 = $this->_rotate(0, 0);
            $point2 = $this->_rotate($this->_calculateWidth() - 1, 0);
            $point3 = $this->_rotate(
                $this->_calculateWidth() - 1,
                $this->_calculateHeight() - 1
            );
            $point4 = $this->_rotate(0, $this->_calculateHeight() - 1);
            $this->_addPolygon(array(
                $point1,
                $point2,
                $point3,
                $point4,
                $point1,
            ), $this->_foreColor, false);
        }
    }

    /**
     * Partial function to draw text
     * @return void
     */
    protected function _drawText()
    {
        if ($this->_drawText) {
            $text = $this->getTextToDisplay();
            if ($this->_stretchText) {
                $textLength = strlen($text);
                $space      = ($this->_calculateWidth() - 2 * $this->getQuietZone()) / $textLength;
                for ($i = 0; $i < $textLength; $i ++) {
                    $leftPosition = $this->getQuietZone() + $space * ($i + 0.5);
                    $this->_addText(
                        $text{$i},
                        $this->_fontSize * $this->_factor,
                        $this->_rotate(
                            $leftPosition,
                            (int) $this->_withBorder * 2
                                + $this->_factor * ($this->_barHeight + $this->_fontSize) + 1
                        ),
                        $this->_font,
                        $this->_foreColor,
                        'center',
                        - $this->_orientation
                    );
                }
            } else {
                $this->_addText(
                    $text,
                    $this->_fontSize * $this->_factor,
                    $this->_rotate(
                        $this->_calculateWidth() / 2,
                        (int) $this->_withBorder * 2
                            + $this->_factor * ($this->_barHeight + $this->_fontSize) + 1
                    ),
                    $this->_font,
                    $this->_foreColor,
                    'center',
                    - $this->_orientation
                );
            }
        }
    }

    /**
     * Check for invalid characters
     * @param   string $value    Text to be ckecked
     * @return void
     */
    public function validateText($value)
    {
        $this->_validateText($value);
    }

    /**
     * Standard validation for most of barcode objects
     * @param string $value
     * @param array  $options
     */
    protected function _validateText($value, $options = array())
    {
        $validatorName = (isset($options['validator'])) ? $options['validator'] : $this->getType();

        $validator = new Zend_Validate_Barcode(array(
            'adapter'  => $validatorName,
            'checksum' => false,
        ));

        $checksumCharacter = '';
        $withChecksum = false;
        if ($this->_mandatoryChecksum) {
            $checksumCharacter = $this->_substituteChecksumCharacter;
            $withChecksum = true;
        }

        $value = $this->_addLeadingZeros($value, $withChecksum) . $checksumCharacter;

        if (!$validator->isValid($value)) {
            $message = implode("\n", $validator->getMessages());

            /**
             * @see Zend_Barcode_Object_Exception
             */
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception($message);
        }
    }

    /**
     * Each child must prepare the barcode and return
     * a table like array(
     *     0 => array(
     *         0 => int (visible(black) or not(white))
     *         1 => int (width of the bar)
     *         2 => float (0->1 position from the top of the beginning of the bar in %)
     *         3 => float (0->1 position from the top of the end of the bar in %)
     *     ),
     *     1 => ...
     * )
     *
     * @return array
     */
    abstract protected function _prepareBarcode();

    /**
     * Checking of parameters after all settings
     *
     * @return void
     */
    abstract protected function _checkParams();

    /**
     * Allow each child to draw something else
     *
     * @return void
     */
    protected function _preDrawBarcode()
    {
    }

    /**
     * Allow each child to draw something else
     * (ex: bearer bars in interleaved 2 of 5 code)
     *
     * @return void
     */
    protected function _postDrawBarcode()
    {
    }
}
