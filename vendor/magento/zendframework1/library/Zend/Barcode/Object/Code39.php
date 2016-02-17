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
 * @see 'Zend_Validate_Barcode'
 */
#require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate Code39 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Code39 extends Zend_Barcode_Object_ObjectAbstract
{
    /**
     * Coding map
     * @var array
     */
    protected $_codingMap = array(
        '0' => '000110100',
        '1' => '100100001',
        '2' => '001100001',
        '3' => '101100000',
        '4' => '000110001',
        '5' => '100110000',
        '6' => '001110000',
        '7' => '000100101',
        '8' => '100100100',
        '9' => '001100100',
        'A' => '100001001',
        'B' => '001001001',
        'C' => '101001000',
        'D' => '000011001',
        'E' => '100011000',
        'F' => '001011000',
        'G' => '000001101',
        'H' => '100001100',
        'I' => '001001100',
        'J' => '000011100',
        'K' => '100000011',
        'L' => '001000011',
        'M' => '101000010',
        'N' => '000010011',
        'O' => '100010010',
        'P' => '001010010',
        'Q' => '000000111',
        'R' => '100000110',
        'S' => '001000110',
        'T' => '000010110',
        'U' => '110000001',
        'V' => '011000001',
        'W' => '111000000',
        'X' => '010010001',
        'Y' => '110010000',
        'Z' => '011010000',
        '-' => '010000101',
        '.' => '110000100',
        ' ' => '011000100',
        '$' => '010101000',
        '/' => '010100010',
        '+' => '010001010',
        '%' => '000101010',
        '*' => '010010100',
    );

    /**
     * Partial check of Code39 barcode
     * @return void
     */
    protected function _checkParams()
    {
        $this->_checkRatio();
    }

    /**
     * Width of the barcode (in pixels)
     * @return int
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $characterLength = (6 * $this->_barThinWidth + 3 * $this->_barThickWidth + 1) * $this->_factor;
        $encodedData     = strlen($this->getText()) * $characterLength - $this->_factor;
        return $quietZone + $encodedData + $quietZone;
    }

    /**
     * Set text to encode
     * @param string $value
     * @return Zend_Barcode_Object
     */
    public function setText($value)
    {
        $this->_text = $value;
        return $this;
    }

    /**
     * Retrieve text to display
     * @return string
     */
    public function getText()
    {
        return '*' . parent::getText() . '*';
    }

    /**
     * Retrieve text to display
     * @return string
     */
    public function getTextToDisplay()
    {
        $text = parent::getTextToDisplay();
        if (substr($text, 0, 1) != '*' && substr($text, -1) != '*') {
            return '*' . $text . '*';
        } else {
            return $text;
        }
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function _prepareBarcode()
    {
        $text         = str_split($this->getText());
        $barcodeTable = array();
        foreach ($text as $char) {
            $barcodeChar = str_split($this->_codingMap[$char]);
            $visible     = true;
            foreach ($barcodeChar as $c) {
                /* visible, width, top, length */
                $width          = $c ? $this->_barThickWidth : $this->_barThinWidth;
                $barcodeTable[] = array((int) $visible, $width, 0, 1);
                $visible = ! $visible;
            }
            $barcodeTable[] = array(0 , $this->_barThinWidth);
        }
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
        $text     = str_split($text);
        $charset  = array_flip(array_keys($this->_codingMap));
        $checksum = 0;
        foreach ($text as $character) {
            $checksum += $charset[$character];
        }
        return array_search(($checksum % 43), $charset);
    }
}
