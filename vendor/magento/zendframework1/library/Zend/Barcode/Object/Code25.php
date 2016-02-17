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
 * Class for generate Interleaved 2 of 5 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Code25 extends Zend_Barcode_Object_ObjectAbstract
{
    /**
     * Coding map
     * - 0 = narrow bar
     * - 1 = wide bar
     * @var array
     */
    protected $_codingMap = array(
        '0' => '00110',
        '1' => '10001',
        '2' => '01001',
        '3' => '11000',
        '4' => '00101',
        '5' => '10100',
        '6' => '01100',
        '7' => '00011',
        '8' => '10010',
        '9' => '01010',
    );

    /**
     * Width of the barcode (in pixels)
     * @return integer
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (2 * $this->_barThickWidth + 4 * $this->_barThinWidth) * $this->_factor;
        $characterLength = (3 * $this->_barThinWidth + 2 * $this->_barThickWidth + 5 * $this->_barThinWidth)
                         * $this->_factor;
        $encodedData     = strlen($this->getText()) * $characterLength;
        $stopCharacter   = (2 * $this->_barThickWidth + 4 * $this->_barThinWidth) * $this->_factor;
        return $quietZone + $startCharacter + $encodedData + $stopCharacter + $quietZone;
    }

    /**
     * Partial check of interleaved 2 of 5 barcode
     * @return void
     */
    protected function _checkParams()
    {
        $this->_checkRatio();
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function _prepareBarcode()
    {
        $barcodeTable = array();

        // Start character (30301)
        $barcodeTable[] = array(1 , $this->_barThickWidth , 0 , 1);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , 1);
        $barcodeTable[] = array(1 , $this->_barThickWidth , 0 , 1);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , 1);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , 1);
        $barcodeTable[] = array(0 , $this->_barThinWidth);

        $text = str_split($this->getText());
        foreach ($text as $char) {
            $barcodeChar = str_split($this->_codingMap[$char]);
            foreach ($barcodeChar as $c) {
                /* visible, width, top, length */
                $width = $c ? $this->_barThickWidth : $this->_barThinWidth;
                $barcodeTable[] = array(1 , $width , 0 , 1);
                $barcodeTable[] = array(0 , $this->_barThinWidth);
            }
        }

        // Stop character (30103)
        $barcodeTable[] = array(1 , $this->_barThickWidth , 0 , 1);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , 1);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , 1);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , 1);
        $barcodeTable[] = array(1 , $this->_barThickWidth , 0 , 1);
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
}
