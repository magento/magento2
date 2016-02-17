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
 * @version    $Id: Code25.php 20096 2010-01-06 02:05:09Z bkarwin $
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
 * Class for generate Code128 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Code128 extends Zend_Barcode_Object_ObjectAbstract
{
    /**
     * Drawing of checksum
     * (even if it's sometime optional, most of time it's required)
     * @var boolean
     */
    protected $_withChecksum = true;

    /**
     * @var array
     */
    protected $_convertedText = array();

    protected $_codingMap = array(
                 0 => "11011001100",   1 => "11001101100",   2 => "11001100110",
                 3 => "10010011000",   4 => "10010001100",   5 => "10001001100",
                 6 => "10011001000",   7 => "10011000100",   8 => "10001100100",
                 9 => "11001001000",  10 => "11001000100",  11 => "11000100100",
                12 => "10110011100",  13 => "10011011100",  14 => "10011001110",
                15 => "10111001100",  16 => "10011101100",  17 => "10011100110",
                18 => "11001110010",  19 => "11001011100",  20 => "11001001110",
                21 => "11011100100",  22 => "11001110100",  23 => "11101101110",
                24 => "11101001100",  25 => "11100101100",  26 => "11100100110",
                27 => "11101100100",  28 => "11100110100",  29 => "11100110010",
                30 => "11011011000",  31 => "11011000110",  32 => "11000110110",
                33 => "10100011000",  34 => "10001011000",  35 => "10001000110",
                36 => "10110001000",  37 => "10001101000",  38 => "10001100010",
                39 => "11010001000",  40 => "11000101000",  41 => "11000100010",
                42 => "10110111000",  43 => "10110001110",  44 => "10001101110",
                45 => "10111011000",  46 => "10111000110",  47 => "10001110110",
                48 => "11101110110",  49 => "11010001110",  50 => "11000101110",
                51 => "11011101000",  52 => "11011100010",  53 => "11011101110",
                54 => "11101011000",  55 => "11101000110",  56 => "11100010110",
                57 => "11101101000",  58 => "11101100010",  59 => "11100011010",
                60 => "11101111010",  61 => "11001000010",  62 => "11110001010",
                63 => "10100110000",  64 => "10100001100",  65 => "10010110000",
                66 => "10010000110",  67 => "10000101100",  68 => "10000100110",
                69 => "10110010000",  70 => "10110000100",  71 => "10011010000",
                72 => "10011000010",  73 => "10000110100",  74 => "10000110010",
                75 => "11000010010",  76 => "11001010000",  77 => "11110111010",
                78 => "11000010100",  79 => "10001111010",  80 => "10100111100",
                81 => "10010111100",  82 => "10010011110",  83 => "10111100100",
                84 => "10011110100",  85 => "10011110010",  86 => "11110100100",
                87 => "11110010100",  88 => "11110010010",  89 => "11011011110",
                90 => "11011110110",  91 => "11110110110",  92 => "10101111000",
                93 => "10100011110",  94 => "10001011110",  95 => "10111101000",
                96 => "10111100010",  97 => "11110101000",  98 => "11110100010",
                99 => "10111011110", 100 => "10111101110", 101 => "11101011110",
               102 => "11110101110",
               103 => "11010000100", 104 => "11010010000", 105 => "11010011100",
               106 => "1100011101011");

    /**
    * Character sets ABC
    * @var array
    */
    protected $_charSets = array(
        'A' => array(
            ' ', '!', '"', '#', '$', '%', '&', "'",
            '(', ')', '*', '+', ',', '-', '.', '/',
            '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', ':', ';', '<', '=', '>', '?',
            '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
            'X', 'Y', 'Z', '[', '\\', ']', '^', '_',
            0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07,
            0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F,
            0x10, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17,
            0x18, 0x19, 0x1A, 0x1B, 0x1C, 0x1D, 0x1E, 0x1F,
            'FNC3', 'FNC2', 'SHIFT', 'Code C', 'Code B', 'FNC4', 'FNC1',
            'START A', 'START B', 'START C', 'STOP'),
        'B' => array(
            ' ', '!', '"', '#', '$', '%', '&', "'",
            '(', ')', '*', '+', ',', '-', '.', '/',
            '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', ':', ';', '<', '=', '>', '?',
            '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
            'X', 'Y', 'Z', '[', '\\', ']', '^', '_',
            '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g',
            'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
            'p', 'q', 'r', 's', 't', 'u', 'v', 'w',
            'x', 'y', 'z', '{', '|', '}', '~', 0x7F,
            'FNC3', 'FNC2', 'SHIFT', 'Code C', 'FNC4', 'Code A', 'FNC1',
            'START A', 'START B', 'START C', 'STOP',),
        'C' => array(
            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
            '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '20', '21', '22', '23', '24', '25', '26', '27', '28', '29',
            '30', '31', '32', '33', '34', '35', '36', '37', '38', '39',
            '40', '41', '42', '43', '44', '45', '46', '47', '48', '49',
            '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
            '60', '61', '62', '63', '64', '65', '66', '67', '68', '69',
            '70', '71', '72', '73', '74', '75', '76', '77', '78', '79',
            '80', '81', '82', '83', '84', '85', '86', '87', '88', '89',
            '90', '91', '92', '93', '94', '95', '96', '97', '98', '99',
            'Code B', 'Code A', 'FNC1', 'START A', 'START B', 'START C', 'STOP'));
    /*'A' => array(
      ' '=>0, '!'=>1, '"'=>2, '#'=>3, '$'=>4, '%'=>5, '&'=>6, "'"=>7,
      '('=>8, ')'=>9, '*'=>10, '+'=>11, ','=>12, '-'=>13, '.'=>14, '/'=>15,
      '0'=>16, '1'=>17, '2'=>18, '3'=>19, '4'=>20, '5'=>21, '6'=>22, '7'=>23,
      '8'=>24, '9'=>25, ':'=>26, ';'=>27, '<'=>28, '='=>29, '>'=>30, '?'=>31,
      '@'=>32, 'A'=>33, 'B'=>34, 'C'=>35, 'D'=>36, 'E'=>37, 'F'=>38, 'G'=>39,
      'H'=>40, 'I'=>41, 'J'=>42, 'K'=>43, 'L'=>44, 'M'=>45, 'N'=>46, 'O'=>47,
      'P'=>48, 'Q'=>49, 'R'=>50, 'S'=>51, 'T'=>52, 'U'=>53, 'V'=>54, 'W'=>55,
      'X'=>56, 'Y'=>57, 'Z'=>58, '['=>59, '\\'=>60, ']'=>61, '^'=>62, '_'=>63,
      0x00=>64, 0x01=>65, 0x02=>66, 0x03=>67, 0x04=>68, 0x05=>69, 0x06=>70, 0x07=>71,
      0x08=>72, 0x09=>73, 0x0A=>74, 0x0B=>75, 0x0C=>76, 0x0D=>77, 0x0E=>78, 0x0F=>79,
      0x10=>80, 0x11=>81, 0x12=>82, 0x13=>83, 0x14=>84, 0x15=>85, 0x16=>86, 0x17=>87,
      0x18=>88, 0x19=>89, 0x1A=>90, 0x1B=>91, 0x1C=>92, 0x1D=>93, 0x1E=>94, 0x1F=>95,
      'FNC3'=>96, 'FNC2'=>97, 'SHIFT'=>98, 'Code C'=>99, 'Code B'=>100, 'FNC4'=>101, 'FNC1'=>102, 'START A'=>103,
      'START B'=>104, 'START C'=>105, 'STOP'=>106),
    'B' => array(
      ' '=>0, '!'=>1, '"'=>2, '#'=>3, '$'=>4, '%'=>5, '&'=>6, "'"=>7,
      '('=>8, ')'=>9, '*'=>10, '+'=>11, ','=>12, '-'=>13, '.'=>14, '/'=>15,
      '0'=>16, '1'=>17, '2'=>18, '3'=>19, '4'=>20, '5'=>21, '6'=>22, '7'=>23,
      '8'=>24, '9'=>25, ':'=>26, ';'=>27, '<'=>28, '='=>29, '>'=>30, '?'=>31,
      '@'=>32, 'A'=>33, 'B'=>34, 'C'=>35, 'D'=>36, 'E'=>37, 'F'=>38, 'G'=>39,
      'H'=>40, 'I'=>41, 'J'=>42, 'K'=>43, 'L'=>44, 'M'=>45, 'N'=>46, 'O'=>47,
      'P'=>48, 'Q'=>49, 'R'=>50, 'S'=>51, 'T'=>52, 'U'=>53, 'V'=>54, 'W'=>55,
      'X'=>56, 'Y'=>57, 'Z'=>58, '['=>59, '\\'=>60, ']'=>61, '^'=>62, '_'=>63,
      '`' =>64, 'a'=>65, 'b'=>66, 'c'=>67, 'd'=>68, 'e'=>69, 'f'=>70, 'g'=>71,
      'h'=>72, 'i'=>73, 'j'=>74, 'k'=>75, 'l'=>76, 'm'=>77, 'n'=>78, 'o'=>79,
      'p'=>80, 'q'=>81, 'r'=>82, 's'=>83, 't'=>84, 'u'=>85, 'v'=>86, 'w'=>87,
      'x'=>88, 'y'=>89, 'z'=>90, '{'=>91, '|'=>92, '}'=>93, '~'=>94, 0x7F=>95,
      'FNC3'=>96, 'FNC2'=>97, 'SHIFT'=>98, 'Code C'=>99, 'FNC4'=>100, 'Code A'=>101, 'FNC1'=>102, 'START A'=>103,
      'START B'=>104, 'START C'=>105, 'STOP'=>106,),
    'C' => array(
      '00'=>0, '01'=>1, '02'=>2, '03'=>3, '04'=>4, '05'=>5, '06'=>6, '07'=>7, '08'=>8, '09'=>9,
      '10'=>10, '11'=>11, '12'=>12, '13'=>13, '14'=>14, '15'=>15, '16'=>16, '17'=>17, '18'=>18, '19'=>19,
      '20'=>20, '21'=>21, '22'=>22, '23'=>23, '24'=>24, '25'=>25, '26'=>26, '27'=>27, '28'=>28, '29'=>29,
      '30'=>30, '31'=>31, '32'=>32, '33'=>33, '34'=>34, '35'=>35, '36'=>36, '37'=>37, '38'=>38, '39'=>39,
      '40'=>40, '41'=>41, '42'=>42, '43'=>43, '44'=>44, '45'=>45, '46'=>46, '47'=>47, '48'=>48, '49'=>49,
      '50'=>50, '51'=>51, '52'=>52, '53'=>53, '54'=>54, '55'=>55, '56'=>56, '57'=>57, '58'=>58, '59'=>59,
      '60'=>60, '61'=>61, '62'=>62, '63'=>63, '64'=>64, '65'=>65, '66'=>66, '67'=>67, '68'=>68, '69'=>69,
      '70'=>70, '71'=>71, '72'=>72, '73'=>73, '74'=>74, '75'=>75, '76'=>76, '77'=>77, '78'=>78, '79'=>79,
      '80'=>80, '81'=>81, '82'=>82, '83'=>83, '84'=>84, '85'=>85, '86'=>86, '87'=>87, '88'=>88, '89'=>89,
      '90'=>90, '91'=>91, '92'=>92, '93'=>93, '94'=>94, '95'=>95, '96'=>96, '97'=>97, '98'=>98, '99'=>99,
      'Code B'=>100, 'Code A'=>101, 'FNC1'=>102, 'START A'=>103, 'START B'=>104, 'START C'=>105, 'STOP'=>106));*/

    /**
     * Width of the barcode (in pixels)
     * @return integer
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone = $this->getQuietZone();
        // Each characters contain 11 bars...
        $characterLength = 11 * $this->_barThinWidth * $this->_factor;
        $convertedChars = count($this->_convertToBarcodeChars($this->getText()));
        if ($this->_withChecksum) {
            $convertedChars++;
        }
        $encodedData = $convertedChars * $characterLength;
        // ...except the STOP character (13)
        $encodedData += $characterLength + 2 * $this->_barThinWidth * $this->_factor;
        $width = $quietZone + $encodedData + $quietZone;
        return $width;
    }

    /**
     * Partial check of code128 barcode
     * @return void
     */
    protected function _checkParams()
    {
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function _prepareBarcode()
    {
        $barcodeTable = array();

        $convertedChars = $this->_convertToBarcodeChars($this->getText());

        if ($this->_withChecksum) {
            $convertedChars[] = $this->getChecksum($this->getText());
        }

        // STOP CHARACTER
        $convertedChars[] = 106;

        foreach ($convertedChars as $barcodeChar) {
            $barcodePattern = $this->_codingMap[$barcodeChar];
            foreach (str_split($barcodePattern) as $c) {
                $barcodeTable[] = array($c, $this->_barThinWidth, 0, 1);
            }
        }
        return $barcodeTable;
    }

    /**
     * Checks if the next $length chars of $string starting at $pos are numeric.
     * Returns false if the end of the string is reached.
     * @param string $string String to search
     * @param int    $pos Starting position
     * @param int    $length Length to search
     * @return bool
     */
    protected static function _isDigit($string, $pos, $length = 2)
    {
        if ($pos + $length > strlen($string)) {
           return false;
        }

        for ($i = $pos; $i < $pos + $length; $i++) {
              if (!is_numeric($string[$i])) {
                  return false;
              }
        }
        return true;
    }

    /**
     * Convert string to barcode string
     *
     * @param $string
     * @return array
     */
    protected function _convertToBarcodeChars($string)
    {
        $string = (string) $string;
        if (!strlen($string)) {
            return array();
        }

        if (isset($this->_convertedText[md5($string)])) {
            return $this->_convertedText[md5($string)];
        }

        $currentCharset = null;
        $sum = 0;
        $fak = 0;
        $result = array();

        for ($pos = 0; $pos < strlen($string); $pos++) {
            $char = $string[$pos];
            $code = null;

            if (self::_isDigit($string, $pos, 4) && $currentCharset != 'C'
             || self::_isDigit($string, $pos, 2) && $currentCharset == 'C') {
                /**
                 * Switch to C if the next 4 chars are numeric or stay C if the next 2
                 * chars are numeric
                 */
                if ($currentCharset != 'C') {
                    if ($pos == 0) {
                        $code = array_search("START C", $this->_charSets['C']);
                    } else {
                        $code = array_search("Code C", $this->_charSets[$currentCharset]);
                    }
                    $result[] = $code;
                    $currentCharset = 'C';
                }
            } else if (in_array($char, $this->_charSets['B']) && $currentCharset != 'B'
                  && !(in_array($char, $this->_charSets['A']) && $currentCharset == 'A')) {
                /**
                 * Switch to B as B contains the char and B is not the current charset.
                 */
                if ($pos == 0) {
                    $code = array_search("START B", $this->_charSets['B']);
                } else {
                    $code = array_search("Code B", $this->_charSets[$currentCharset]);
                }
                $result[] = $code;
                $currentCharset = 'B';
            } else if (array_key_exists($char, $this->_charSets['A']) && $currentCharset != 'A'
                  && !(array_key_exists($char, $this->_charSets['B']) && $currentCharset == 'B')) {
                /**
                 * Switch to C as C contains the char and C is not the current charset.
                 */
                if ($pos == 0) {
                    $code = array_search("START A", $this->_charSets['A']);
                } else {
                    $code =array_search("Code A", $this->_charSets[$currentCharset]);
                }
                $result[] = $code;
                $currentCharset = 'A';
            }

            if ($currentCharset == 'C') {
                $code = array_search(substr($string, $pos, 2), $this->_charSets['C']);
                $pos++; //Two chars from input
            } else {
                $code = array_search($string[$pos], $this->_charSets[$currentCharset]);
            }
            $result[] = $code;
        }

        $this->_convertedText[md5($string)] = $result;
        return $result;
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
     * Retrieve text to encode
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Get barcode checksum
     *
     * @param  string $text
     * @return int
     */
    public function getChecksum($text)
    {
        $tableOfChars = $this->_convertToBarcodeChars($text);

        $sum = $tableOfChars[0];
        unset($tableOfChars[0]);

        $k = 1;
        foreach ($tableOfChars as $char) {
            $sum += ($k++) * $char;
        }

        $checksum = $sum % 103;

        return $checksum;
    }

    /**
     * Standard validation for most of barcode objects
     *
     * @param string $value
     * @param array  $options
     * @return bool
     */
    protected function _validateText($value, $options = array())
    {
        // @TODO: add code128 validator
        return true;
    }
}
