<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator\Barcode;

/**
 * @category   Zend
 * @package    Zend_Validate
 */
class Code128 extends AbstractAdapter
{
    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength(-1);
        $this->setCharacters(array(
        'A' => array(
             0 => ' ',  1 => '!',  2 => '"',  3 => '#',  4 => '$',  5 => '%',  6 => '&',  7 => "'",
             8 => '(',  9 => ')', 10 => '*', 11 => '+', 12 => ',', 13 => '-', 14 => '.', 15 => '/',
            16 => '0', 17 => '1', 18 => '2', 19 => '3', 20 => '4', 21 => '5', 22 => '6', 23 => '7',
            24 => '8', 25 => '9', 26 => ':', 27 => ';', 28 => '<', 29 => '=', 30 => '>', 31 => '?',
            32 => '@', 33 => 'A', 34 => 'B', 35 => 'C', 36 => 'D', 37 => 'E', 38 => 'F', 39 => 'G',
            40 => 'H', 41 => 'I', 42 => 'J', 43 => 'K', 44 => 'L', 45 => 'M', 46 => 'N', 47 => 'O',
            48 => 'P', 49 => 'Q', 50 => 'R', 51 => 'S', 52 => 'T', 53 => 'U', 54 => 'V', 55 => 'W',
            56 => 'X', 57 => 'Y', 58 => 'Z', 59 => '[', 60 => '\\',61 => ']', 62 => '^', 63 => '_',
            64 =>0x00, 65 =>0x01, 66 =>0x02, 67 =>0x03, 68 =>0x04, 69 =>0x05, 70 =>0x06, 71 =>0x07,
            72 =>0x08, 73 =>0x09, 74 =>0x0A, 75 =>0x0B, 76 =>0x0C, 77 =>0x0D, 78 =>0x0E, 79 =>0x0F,
            80 =>0x10, 81 =>0x11, 82 =>0x12, 83 =>0x13, 84 =>0x14, 85 =>0x15, 86 =>0x16, 87 =>0x17,
            88 =>0x18, 89 =>0x19, 90 =>0x1A, 91 =>0x1B, 92 =>0x1C, 93 =>0x1D, 94 =>0x1E, 95 =>0x1F,
            96 => 'Ç', 97 => 'ü', 98 => 'é', 99 => 'â',100 => 'ä',101 => 'à',102 => 'å',103 => '‡',
           104 => 'ˆ',105 => '‰',106 => 'Š'),
        'B' => array(
             0 => ' ',  1 => '!',  2 => '"',  3 => '#',  4 => '$',  5 => '%',  6 => '&',  7 => "'",
             8 => '(',  9 => ')', 10 => '*', 11 => '+', 12 => ',', 13 => '-', 14 => '.', 15 => '/',
            16 => '0', 17 => '1', 18 => '2', 19 => '3', 20 => '4', 21 => '5', 22 => '6', 23 => '7',
            24 => '8', 25 => '9', 26 => ':', 27 => ';', 28 => '<', 29 => '=', 30 => '>', 31 => '?',
            32 => '@', 33 => 'A', 34 => 'B', 35 => 'C', 36 => 'D', 37 => 'E', 38 => 'F', 39 => 'G',
            40 => 'H', 41 => 'I', 42 => 'J', 43 => 'K', 44 => 'L', 45 => 'M', 46 => 'N', 47 => 'O',
            48 => 'P', 49 => 'Q', 50 => 'R', 51 => 'S', 52 => 'T', 53 => 'U', 54 => 'V', 55 => 'W',
            56 => 'X', 57 => 'Y', 58 => 'Z', 59 => '[', 60 => '\\',61 => ']', 62 => '^', 63 => '_',
            64 => '`', 65 => 'a', 66 => 'b', 67 => 'c', 68 => 'd', 69 => 'e', 70 => 'f', 71 => 'g',
            72 => 'h', 73 => 'i', 74 => 'j', 75 => 'k', 76 => 'l', 77 => 'm', 78 => 'n', 79 => 'o',
            80 => 'p', 81 => 'q', 82 => 'r', 83 => 's', 84 => 't', 85 => 'u', 86 => 'v', 87 => 'w',
            88 => 'x', 89 => 'y', 90 => 'z', 91 => '{', 92 => '|', 93 => '}', 94 => '~', 95 =>0x7F,
            96 => 'Ç', 97 => 'ü', 98 => 'é', 99 => 'â',100 => 'ä',101 => 'à',102 => 'å',103 => '‡',
           104 => 'ˆ',105 => '‰',106 => 'Š'),
        'C' => array(
             0 => '00',  1 => '01',  2 => '02',  3 => '03',  4 => '04',  5 => '05',  6 => '06',  7 => '07',
             8 => '08',  9 => '09', 10 => '10', 11 => '11', 12 => '12', 13 => '13', 14 => '14', 15 => '15',
            16 => '16', 17 => '17', 18 => '18', 19 => '19', 20 => '20', 21 => '21', 22 => '22', 23 => '23',
            24 => '24', 25 => '25', 26 => '26', 27 => '27', 28 => '28', 29 => '29', 30 => '30', 31 => '31',
            32 => '32', 33 => '33', 34 => '34', 35 => '35', 36 => '36', 37 => '37', 38 => '38', 39 => '39',
            40 => '40', 41 => '41', 42 => '42', 43 => '43', 44 => '44', 45 => '45', 46 => '46', 47 => '47',
            48 => '48', 49 => '49', 50 => '50', 51 => '51', 52 => '52', 53 => '53', 54 => '54', 55 => '55',
            56 => '56', 57 => '57', 58 => '58', 59 => '59', 60 => '60', 61 => '61', 62 => '62', 63 => '63',
            64 => '64', 65 => '65', 66 => '66', 67 => '67', 68 => '68', 69 => '69', 70 => '70', 71 => '71',
            72 => '72', 73 => '73', 74 => '74', 75 => '75', 76 => '76', 77 => '77', 78 => '78', 79 => '79',
            80 => '80', 81 => '81', 82 => '82', 83 => '83', 84 => '84', 85 => '85', 86 => '86', 87 => '87',
            88 => '88', 89 => '89', 90 => '90', 91 => '91', 92 => '92', 93 => '93', 94 => '94', 95 => '95',
            96 => '96', 97 => '97', 98 => '98', 99 => '99',100 => 'ä', 101 => 'à', 102 => 'å', 103 => '‡',
           104 => 'ˆ', 105 => '‰', 106 => 'Š')));
        $this->setChecksum('code128');

    }

    /**
     * Checks for allowed characters within the barcode
     *
     * @param  string $value The barcode to check for allowed characters
     * @return boolean
     */
    public function hasValidCharacters($value)
    {
        if (!is_string($value)) {
            return false;
        }

        // detect starting charset
        $set        = $this->getCodingSet($value);
        $read       = $set;
        if ($set != '') {
            $value = iconv_substr($value, 1, iconv_strlen($value, 'UTF-8'), 'UTF-8');
        }

        // process barcode
        while ($value != '') {
            $char = iconv_substr($value, 0, 1, 'UTF-8');

            switch ($char) {
                // Function definition
                case 'Ç' :
                case 'ü' :
                case 'å' :
                    break;

                // Switch 1 char between A and B
                case 'é' :
                    if ($set == 'A') {
                        $read = 'B';
                    } elseif ($set == 'B') {
                        $read = 'A';
                    }
                    break;

                // Switch to C
                case 'â' :
                    $set = 'C';
                    $read = 'C';
                    break;

                // Switch to B
                case 'ä' :
                    $set  = 'B';
                    $read = 'B';
                    break;

                // Switch to A
                case 'à' :
                    $set  = 'A';
                    $read = 'A';
                    break;

                // Doubled start character
                case '‡' :
                case 'ˆ' :
                case '‰' :
                    return false;
                    break;

                // Chars after the stop character
                case 'Š' :
                    break 2;

                default:
                    // Does the char exist within the charset to read?
                    if ($this->ord128($char, $read) == -1) {
                        return false;
                    }

                    break;
            }

            $value = iconv_substr($value, 1);
            $read  = $set;
        }

        if (($value != '') && (iconv_strlen($value, 'UTF-8') != 1)) {
            return false;
        }

        return true;
    }

    /**
     * Validates the checksum ()
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function code128($value)
    {
        $sum        = 0;
        $pos        = 1;
        $set        = $this->getCodingSet($value);
        $read       = $set;
        $usecheck   = $this->useChecksum(null);
        $char       = iconv_substr($value, 0, 1, 'UTF-8');
        if ($char == '‡') {
            $sum = 103;
        } elseif ($char == 'ˆ') {
            $sum = 104;
        } elseif ($char == '‰') {
            $sum = 105;
        } elseif ($usecheck == true) {
            // no start value, unable to detect an proper checksum
            return false;
        }

        $value = iconv_substr($value, 1, iconv_strlen($value, 'UTF-8'), 'UTF-8');
        while (iconv_strpos($value, 'Š') || ($value != '')) {
            $char = iconv_substr($value, 0, 1, 'UTF-8');
            if ($read == 'C') {
                $char = iconv_substr($value, 0, 2, 'UTF-8');
            }

            switch ($char) {
                // Function definition
                case 'Ç' :
                case 'ü' :
                case 'å' :
                    $sum += ($pos * $this->ord128($char, $set));
                    break;

                case 'é' :
                    $sum += ($pos * $this->ord128($char, $set));
                     if ($set == 'A') {
                        $read = 'B';
                    } elseif ($set == 'B') {
                        $read = 'A';
                    }
                    break;

                // Switch to C
                case 'â' :
                    $sum += ($pos * $this->ord128($char, $set));
                    $set = 'C';
                    $read = 'C';
                    break;

                // Switch to B
                case 'ä' :
                    $sum += ($pos * $this->ord128($char, $set));
                    $set  = 'B';
                    $read = 'B';
                    break;

                // Switch to A
                case 'à' :
                    $sum += ($pos * $this->ord128($char, $set));
                    $set  = 'A';
                    $read = 'A';
                    break;

                case '‡' :
                case 'ˆ' :
                case '‰' :
                    return false;
                    break;

                default:
                    // Does the char exist within the charset to read?
                    if ($this->ord128($char, $read) == -1) {
                        return false;
                    }

                    $sum += ($pos * $this->ord128($char, $set));
                    break;
            }

            $value = iconv_substr($value, 1, iconv_strlen($value, 'UTF-8'), 'UTF-8');
            ++$pos;
            if ((iconv_strpos($value, 'Š', 0, 'UTF-8') == 1) && (iconv_strlen($value, 'UTF-8') == 2)) {
                // break by stop and checksum char
                break;
            }
            $read  = $set;
        }

        if ((iconv_strpos($value, 'Š', 0, 'UTF-8') != 1) || (iconv_strlen($value, 'UTF-8') != 2)) {
            // return false if checksum is not readable and true if no startvalue is detected
            return (!$usecheck);
        }

        $mod = $sum % 103;
        if (iconv_substr($value, 0, 1, 'UTF-8') == $this->chr128($mod, $set)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the coding set for a barcode
     *
     * @param string $value Barcode
     * @return string
     */
    protected function getCodingSet($value)
    {
        $value = iconv_substr($value, 0, 1, 'UTF-8');
        switch ($value) {
            case '‡' :
                return 'A';
                break;
            case 'ˆ' :
                return 'B';
                break;
            case '‰' :
                return 'C';
                break;
        }

        return '';
    }

    /**
     * Internal method to return the code128 integer from an ascii value
     *
     * Table A
     *    ASCII       CODE128
     *  32 to  95 ==  0 to  63
     *   0 to  31 == 64 to  95
     * 128 to 138 == 96 to 106
     *
     * Table B
     *    ASCII       CODE128
     *  32 to 138 == 0 to 106
     *
     * Table C
     *    ASCII       CODE128
     *  "00" to "99" ==   0 to  99
     *   132 to  138 == 100 to 106
     *
     * @param string $value
     * @param string $set
     * @return integer
     */
    protected function ord128($value, $set)
    {
        $ord = ord($value);
        if ($set == 'A') {
            if ($ord < 32) {
                return ($ord + 64);
            } elseif ($ord < 96) {
                return ($ord - 32);
            } elseif ($ord > 138) {
                return -1;
            } else {
                return ($ord - 32);
            }
        } elseif ($set == 'B') {
            if ($ord < 32) {
                return -1;
            } elseif ($ord <= 138) {
                return ($ord - 32);
            } else {
                return -1;
            }
        } elseif ($set == 'C') {
            $val = (int) $value;
            if (($val >= 0) && ($val <= 99)) {
                return $val;
            } elseif (($ord >= 132) && ($ord <= 138)) {
                return ($ord - 32);
            } else {
                return -1;
            }
        } else {
            if ($ord < 32) {
                return ($ord +64);
            } elseif ($ord <= 138) {
                return ($ord - 32);
            } else {
                return -1;
            }
        }
    }

    /**
     * Internal Method to return the ascii value from an code128 integer
     *
     * Table A
     *    ASCII       CODE128
     *  32 to  95 ==  0 to  63
     *   0 to  31 == 64 to  95
     * 128 to 138 == 96 to 106
     *
     * Table B
     *    ASCII       CODE128
     *  32 to 138 == 0 to 106
     *
     * Table C
     *    ASCII       CODE128
     *  "00" to "99" ==   0 to  99
     *   132 to  138 == 100 to 106
     *
     * @param integer $value
     * @param string $set
     * @return string
     */
    protected function chr128($value, $set)
    {
        if ($set == 'A') {
            if ($value < 64) {
                return chr($value + 32);
            } elseif ($value < 96) {
                return chr($value - 64);
            } elseif ($value > 106) {
                return -1;
            } else {
                return chr($value + 32);
            }
        } elseif ($set == 'B') {
            if ($value > 106) {
                return -1;
            } else {
                return chr($value + 32);
            }
        } elseif ($set == 'C') {
            if (($value >= 0) && ($value <= 9)) {
                return "0" . (string) $value;
            } elseif ($value <= 99) {
                return (string) $value;
            } elseif ($value <= 106) {
                return chr($value + 32);
            } else {
                return -1;
            }
        } else {
            if ($ord <= 106) {
                return ($ord + 32);
            } else {
                return -1;
            }
        }
    }
}
