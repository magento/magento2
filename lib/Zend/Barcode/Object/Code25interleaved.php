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
 * @version    $Id: Code25interleaved.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** @see Zend_Barcode_Object_Code25 */
#require_once 'Zend/Barcode/Object/Code25.php';

/** @see Zend_Validate_Barcode */
#require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate Interleaved 2 of 5 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Code25interleaved extends Zend_Barcode_Object_Code25
{
    /**
     * Drawing of bearer bars
     * @var boolean
     */
    private $_withBearerBars = false;

    /**
     * Default options for Code25interleaved barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barcodeLength = 'even';
    }

    /**
     * Activate/deactivate drawing of bearer bars
     * @param boolean $value
     * @return Zend_Barcode_Object_Int25
     */
    public function setWithBearerBars($value)
    {
        $this->_withBearerBars = (bool) $value;
        return $this;
    }

    /**
     * Retrieve if bearer bars are enabled
     * @return boolean
     */
    public function getWithBearerBars()
    {
        return $this->_withBearerBars;
    }

    /**
     * Width of the barcode (in pixels)
     * @return integer
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (4 * $this->_barThinWidth) * $this->_factor;
        $characterLength = (3 * $this->_barThinWidth + 2 * $this->_barThickWidth) * $this->_factor;
        $encodedData     = strlen($this->getText()) * $characterLength;
        $stopCharacter   = ($this->_barThickWidth + 2 * $this->_barThinWidth) * $this->_factor;
        return $quietZone + $startCharacter + $encodedData + $stopCharacter + $quietZone;
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function _prepareBarcode()
    {
        if ($this->_withBearerBars) {
            $this->_withBorder = false;
        }

        // Start character (0000)
        $barcodeTable[] = array(1, $this->_barThinWidth, 0, 1);
        $barcodeTable[] = array(0, $this->_barThinWidth, 0, 1);
        $barcodeTable[] = array(1, $this->_barThinWidth, 0, 1);
        $barcodeTable[] = array(0, $this->_barThinWidth, 0, 1);

        // Encoded $text
        $text = $this->getText();
        for ($i = 0; $i < strlen($text); $i += 2) { // Draw 2 chars at a time
            $char1 = substr($text, $i, 1);
            $char2 = substr($text, $i + 1, 1);

            // Interleave
            for ($ibar = 0; $ibar < 5; $ibar ++) {
                // Draws char1 bar (fore color)
                $barWidth = (substr($this->_codingMap[$char1], $ibar, 1))
                          ? $this->_barThickWidth
                          : $this->_barThinWidth;

                $barcodeTable[] = array(1, $barWidth, 0, 1);

                // Left space corresponding to char2 (background color)
                $barWidth = (substr($this->_codingMap[$char2], $ibar, 1))
                          ? $this->_barThickWidth
                          : $this->_barThinWidth;
                $barcodeTable[] = array(0, $barWidth, 0 , 1);
            }
        }

        // Stop character (100)
        $barcodeTable[] = array(1 , $this->_barThickWidth, 0, 1);
        $barcodeTable[] = array(0 , $this->_barThinWidth,  0, 1);
        $barcodeTable[] = array(1 , $this->_barThinWidth,  0, 1);
        return $barcodeTable;
    }

    /**
     * Drawing of bearer bars (if enabled)
     *
     * @return void
     */
    protected function _postDrawBarcode()
    {
        if (!$this->_withBearerBars) {
            return;
        }

        $width  = $this->_barThickWidth * $this->_factor;
        $point1 = $this->_rotate(-1, -1);
        $point2 = $this->_rotate($this->_calculateWidth() - 1, -1);
        $point3 = $this->_rotate($this->_calculateWidth() - 1, $width - 1);
        $point4 = $this->_rotate(-1, $width - 1);
        $this->_addPolygon(array(
            $point1,
            $point2,
            $point3,
            $point4,
        ));
        $point1 = $this->_rotate(
            0,
            0 + $this->_barHeight * $this->_factor - 1
        );
        $point2 = $this->_rotate(
            $this->_calculateWidth() - 1,
            0 + $this->_barHeight * $this->_factor - 1
        );
        $point3 = $this->_rotate(
            $this->_calculateWidth() - 1,
            0 + $this->_barHeight * $this->_factor - $width
        );
        $point4 = $this->_rotate(
            0,
            0 + $this->_barHeight * $this->_factor - $width
        );
        $this->_addPolygon(array(
            $point1,
            $point2,
            $point3,
            $point4,
        ));
    }
}
