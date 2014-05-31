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
 * @version    $Id: Upca.php 21667 2010-03-28 17:45:14Z mikaelkael $
 */

/**
 * @see Zend_Barcode_Object_Ean13
 */
#require_once 'Zend/Barcode/Object/Ean13.php';

/**
 * @see Zend_Validate_Barcode
 */
#require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate UpcA barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Upca extends Zend_Barcode_Object_Ean13
{

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barcodeLength = 12;
        $this->_mandatoryChecksum = true;
        $this->_mandatoryQuietZones = true;
    }

    /**
     * Width of the barcode (in pixels)
     * @return integer
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (3 * $this->_barThinWidth) * $this->_factor;
        $middleCharacter = (5 * $this->_barThinWidth) * $this->_factor;
        $stopCharacter   = (3 * $this->_barThinWidth) * $this->_factor;
        $encodedData     = (7 * $this->_barThinWidth) * $this->_factor * 12;
        return $quietZone + $startCharacter + $middleCharacter + $encodedData + $stopCharacter + $quietZone;
    }

        /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function _prepareBarcode()
    {
        $barcodeTable = array();
        $height = ($this->_drawText) ? 1.1 : 1;

        // Start character (101)
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);

        $textTable = str_split($this->getText());

        // First character
        $bars = str_split($this->_codingMap['A'][$textTable[0]]);
        foreach ($bars as $b) {
            $barcodeTable[] = array($b , $this->_barThinWidth , 0 , $height);
        }

        // First part
        for ($i = 1; $i < 6; $i++) {
            $bars = str_split($this->_codingMap['A'][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b , $this->_barThinWidth , 0 , 1);
            }
        }

        // Middle character (01010)
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);

        // Second part
        for ($i = 6; $i < 11; $i++) {
            $bars = str_split($this->_codingMap['C'][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b , $this->_barThinWidth , 0 , 1);
            }
        }

        // Last character
        $bars = str_split($this->_codingMap['C'][$textTable[11]]);
        foreach ($bars as $b) {
            $barcodeTable[] = array($b , $this->_barThinWidth , 0 , $height);
        }

        // Stop character (101)
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        return $barcodeTable;
    }

    /**
     * Partial function to draw text
     * @return void
     */
    protected function _drawText()
    {
        if ($this->_drawText) {
            $text = $this->getTextToDisplay();
            $characterWidth = (7 * $this->_barThinWidth) * $this->_factor;
            $leftPosition = $this->getQuietZone() - $characterWidth;
            for ($i = 0; $i < $this->_barcodeLength; $i ++) {
                $fontSize = $this->_fontSize;
                if ($i == 0 || $i == 11) {
                    $fontSize *= 0.8;
                }
                $this->_addText(
                    $text{$i},
                    $fontSize * $this->_factor,
                    $this->_rotate(
                        $leftPosition,
                        (int) $this->_withBorder * 2
                            + $this->_factor * ($this->_barHeight + $fontSize) + 1
                    ),
                    $this->_font,
                    $this->_foreColor,
                    'left',
                    - $this->_orientation
                );
                switch ($i) {
                    case 0:
                        $factor = 10;
                        break;
                    case 5:
                        $factor = 4;
                        break;
                    case 10:
                        $factor = 11;
                        break;
                    default:
                        $factor = 0;
                }
                $leftPosition = $leftPosition + $characterWidth + ($factor * $this->_barThinWidth * $this->_factor);
            }
        }
    }
}
