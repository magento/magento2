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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Barcode_Object_ObjectAbstract
 */
#require_once 'Zend/Barcode/Object/ObjectAbstract.php';

/**
 * @see Zend_Validate_Barcode
 */
#require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate Ean13 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Ean13 extends Zend_Barcode_Object_ObjectAbstract
{

    /**
     * Coding map
     * - 0 = narrow bar
     * - 1 = wide bar
     * @var array
     */
    protected $_codingMap = array(
        'A' => array(
            0 => "0001101", 1 => "0011001", 2 => "0010011", 3 => "0111101", 4 => "0100011",
            5 => "0110001", 6 => "0101111", 7 => "0111011", 8 => "0110111", 9 => "0001011"
        ),
        'B' => array(
            0 => "0100111", 1 => "0110011", 2 => "0011011", 3 => "0100001", 4 => "0011101",
            5 => "0111001", 6 => "0000101", 7 => "0010001", 8 => "0001001", 9 => "0010111"
        ),
        'C' => array(
            0 => "1110010", 1 => "1100110", 2 => "1101100", 3 => "1000010", 4 => "1011100",
            5 => "1001110", 6 => "1010000", 7 => "1000100", 8 => "1001000", 9 => "1110100"
        ));

    protected $_parities = array(
        0 => array('A','A','A','A','A','A'),
        1 => array('A','A','B','A','B','B'),
        2 => array('A','A','B','B','A','B'),
        3 => array('A','A','B','B','B','A'),
        4 => array('A','B','A','A','B','B'),
        5 => array('A','B','B','A','A','B'),
        6 => array('A','B','B','B','A','A'),
        7 => array('A','B','A','B','A','B'),
        8 => array('A','B','A','B','B','A'),
        9 => array('A','B','B','A','B','A')
    );

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barcodeLength = 13;
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
     * Partial check of interleaved EAN/UPC barcode
     * @return void
     */
    protected function _checkParams()
    {}

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
        $parity = $this->_parities[$textTable[0]];

        // First part
        for ($i = 1; $i < 7; $i++) {
            $bars = str_split($this->_codingMap[$parity[$i - 1]][$textTable[$i]]);
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
        for ($i = 7; $i < 13; $i++) {
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
     * Get barcode checksum
     *
     * @param  string $text
     * @return int
     */
    public function getChecksum($text)
    {
        $this->_checkText($text);
        $factor   = 3;
        $checksum = 0;

        for ($i = strlen($text); $i > 0; $i --) {
            $checksum += intval($text{$i - 1}) * $factor;
            $factor    = 4 - $factor;
        }

        $checksum = (10 - ($checksum % 10)) % 10;

        return $checksum;
    }

    /**
     * Partial function to draw text
     * @return void
     */
    protected function _drawText()
    {
        if (get_class($this) == 'Zend_Barcode_Object_Ean13') {
            $this->_drawEan13Text();
        } else {
            parent::_drawText();
        }
    }

    protected function _drawEan13Text()
    {
        if ($this->_drawText) {
            $text = $this->getTextToDisplay();
            $characterWidth = (7 * $this->_barThinWidth) * $this->_factor;
            $leftPosition = $this->getQuietZone() - $characterWidth;
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
                    case 0:
                        $factor = 3;
                        break;
                    case 6:
                        $factor = 4;
                        break;
                    default:
                        $factor = 0;
                }
                $leftPosition = $leftPosition + $characterWidth + ($factor * $this->_barThinWidth * $this->_factor);
            }
        }
    }
}
