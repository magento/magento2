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
 * @version    $Id: Ean8.php 21667 2010-03-28 17:45:14Z mikaelkael $
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
 * Class for generate Ean8 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Ean8 extends Zend_Barcode_Object_Ean13
{

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barcodeLength = 8;
        $this->_mandatoryChecksum = true;
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
        $encodedData     = (7 * $this->_barThinWidth) * $this->_factor * 8;
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

        // First part
        for ($i = 0; $i < 4; $i++) {
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
        for ($i = 4; $i < 8; $i++) {
            $bars = str_split($this->_codingMap['C'][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b , $this->_barThinWidth , 0 , 1);
            }
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
            $leftPosition = $this->getQuietZone() + (3 * $this->_barThinWidth) * $this->_factor;
            for ($i = 0; $i < $this->_barcodeLength; $i ++) {
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
                    'left',
                    - $this->_orientation
                );
                switch ($i) {
                    case 3:
                        $factor = 4;
                        break;
                    default:
                        $factor = 0;
                }
                $leftPosition = $leftPosition + $characterWidth + ($factor * $this->_barThinWidth * $this->_factor);
            }
        }
    }

    /**
     * Particular validation for Ean8 barcode objects
     * (to suppress checksum character substitution)
     * @param string $value
     * @param array  $options
     */
    protected function _validateText($value, $options = array())
    {
        $validator = new Zend_Validate_Barcode(array(
            'adapter'  => 'ean8',
            'checksum' => false,
        ));

        $value = $this->_addLeadingZeros($value, true);

        if (!$validator->isValid($value)) {
            $message = implode("\n", $validator->getMessages());

            /**
             * @see Zend_Barcode_Object_Exception
             */
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception($message);
        }
    }
}
