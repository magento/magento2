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
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Binary.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Pdf_Element_String */
#require_once 'Zend/Pdf/Element/String.php';


/**
 * PDF file 'binary string' element implementation
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_String_Binary extends Zend_Pdf_Element_String
{
    /**
     * Object value
     *
     * @var string
     */
    public $value;


    /**
     * Escape string according to the PDF rules
     *
     * @param string $inStr
     * @return string
     */
    public static function escape($inStr)
    {
        return strtoupper(bin2hex($inStr));
    }


    /**
     * Unescape string according to the PDF rules
     *
     * @param string $inStr
     * @return string
     */
    public static function unescape($inStr)
    {
        $chunks = array();
        $offset = 0;
        $length = 0;
        while ($offset < strlen($inStr)) {
            // Collect hexadecimal characters
            $start = $offset;
            $offset += strspn($inStr, "0123456789abcdefABCDEF", $offset);
            $chunks[] = substr($inStr, $start, $offset - $start);
            $length += strlen(end($chunks));

            // Skip non-hexadecimal characters
            $offset += strcspn($inStr, "0123456789abcdefABCDEF", $offset);
        }
        if ($length % 2 != 0) {
            // We have odd number of digits.
            // Final digit is assumed to be '0'
            $chunks[] = '0';
        }

        return pack('H*' , implode($chunks));
    }


    /**
     * Return object as string
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        return '<' . self::escape((string)$this->value) . '>';
    }
}
