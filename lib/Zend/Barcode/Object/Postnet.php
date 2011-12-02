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
 * @version    $Id: Postnet.php 20096 2010-01-06 02:05:09Z bkarwin $
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
 * Class for generate Postnet barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Postnet extends Zend_Barcode_Object_ObjectAbstract
{

    /**
     * Coding map
     * - 0 = half bar
     * - 1 = complete bar
     * @var array
     */
    protected $_codingMap = array(
        0 => "11000",
        1 => "00011",
        2 => "00101",
        3 => "00110",
        4 => "01001",
        5 => "01010",
        6 => "01100",
        7 => "10001",
        8 => "10010",
        9 => "10100"
    );

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barThinWidth = 2;
        $this->_barHeight = 20;
        $this->_drawText = false;
        $this->_stretchText = true;
        $this->_mandatoryChecksum = true;
    }

    /**
     * Width of the barcode (in pixels)
     * @return integer
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (2 * $this->_barThinWidth) * $this->_factor;
        $stopCharacter   = (1 * $this->_barThinWidth) * $this->_factor;
        $encodedData     = (10 * $this->_barThinWidth) * $this->_factor * strlen($this->getText());
        return $quietZone + $startCharacter + $encodedData + $stopCharacter + $quietZone;
    }

    /**
     * Partial check of interleaved Postnet barcode
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

        // Start character (1)
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , 1);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , 1);

        // Text to encode
        $textTable = str_split($this->getText());
        foreach ($textTable as $char) {
            $bars = str_split($this->_codingMap[$char]);
            foreach ($bars as $b) {
                $barcodeTable[] = array(1 , $this->_barThinWidth , 0.5 - $b * 0.5 , 1);
                $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , 1);
            }
        }

        // Stop character (1)
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , 1);
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
        $sum = array_sum(str_split($text));
        $checksum = (10 - ($sum % 10)) % 10;
        return $checksum;
    }
}
