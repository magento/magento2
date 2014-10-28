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
 * @version    $Id: Royalmail.php 20096 2010-01-06 02:05:09Z bkarwin $
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
 * Class for generate Royal maim barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Royalmail extends Zend_Barcode_Object_ObjectAbstract
{

    /**
     * Coding map
     * - 0 = Tracker, Ascender and Descender
     * - 1 = Tracker and Ascender
     * - 2 = Tracker and Descender
     * - 3 = Tracker
     * @var array
     */
    protected $_codingMap = array(
        '0' => '3300', '1' => '3210', '2' => '3201', '3' => '2310', '4' => '2301', '5' => '2211',
        '6' => '3120', '7' => '3030', '8' => '3021', '9' => '2130', 'A' => '2121', 'B' => '2031',
        'C' => '3102', 'D' => '3012', 'E' => '3003', 'F' => '2112', 'G' => '2103', 'H' => '2013',
        'I' => '1320', 'J' => '1230', 'K' => '1221', 'L' => '0330', 'M' => '0321', 'N' => '0231',
        'O' => '1302', 'P' => '1212', 'Q' => '1203', 'R' => '0312', 'S' => '0303', 'T' => '0213',
        'U' => '1122', 'V' => '1032', 'W' => '1023', 'X' => '0132', 'Y' => '0123', 'Z' => '0033'
    );

    protected $_rows = array(
        '0' => 1, '1' => 1, '2' => 1, '3' => 1, '4' => 1, '5' => 1,
        '6' => 2, '7' => 2, '8' => 2, '9' => 2, 'A' => 2, 'B' => 2,
        'C' => 3, 'D' => 3, 'E' => 3, 'F' => 3, 'G' => 3, 'H' => 3,
        'I' => 4, 'J' => 4, 'K' => 4, 'L' => 4, 'M' => 4, 'N' => 4,
        'O' => 5, 'P' => 5, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 5,
        'U' => 0, 'V' => 0, 'W' => 0, 'X' => 0, 'Y' => 0, 'Z' => 0,
    );

    protected $_columns = array(
        '0' => 1, '1' => 2, '2' => 3, '3' => 4, '4' => 5, '5' => 0,
        '6' => 1, '7' => 2, '8' => 3, '9' => 4, 'A' => 5, 'B' => 0,
        'C' => 1, 'D' => 2, 'E' => 3, 'F' => 4, 'G' => 5, 'H' => 0,
        'I' => 1, 'J' => 2, 'K' => 3, 'L' => 4, 'M' => 5, 'N' => 0,
        'O' => 1, 'P' => 2, 'Q' => 3, 'R' => 4, 'S' => 5, 'T' => 0,
        'U' => 1, 'V' => 2, 'W' => 3, 'X' => 4, 'Y' => 5, 'Z' => 0,
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
        $encodedData     = (8 * $this->_barThinWidth) * $this->_factor * strlen($this->getText());
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
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , 5/8);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , 1);

        // Text to encode
        $textTable = str_split($this->getText());
        foreach ($textTable as $char) {
            $bars = str_split($this->_codingMap[$char]);
            foreach ($bars as $b) {
                $barcodeTable[] = array(1 , $this->_barThinWidth , ($b > 1 ? 3/8 : 0) , ($b % 2 ? 5/8 : 1));
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
        $values   = str_split($text);
        $rowvalue = 0;
        $colvalue = 0;
        foreach($values as $row) {
            $rowvalue += $this->_rows[$row];
            $colvalue += $this->_columns[$row];
        }

        $rowvalue %= 6;
        $colvalue %= 6;

        $rowchkvalue = array_keys($this->_rows, $rowvalue);
        $colchkvalue = array_keys($this->_columns, $colvalue);
        return current(array_intersect($rowchkvalue, $colchkvalue));
    }
}
