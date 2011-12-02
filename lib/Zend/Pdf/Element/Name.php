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
 * @version    $Id: Name.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Pdf_Element */
#require_once 'Zend/Pdf/Element.php';


/**
 * PDF file 'name' element implementation
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_Name extends Zend_Pdf_Element
{
    /**
     * Object value
     *
     * @var string
     */
    public $value;


    /**
     * Object constructor
     *
     * @param string $val
     * @throws Zend_Pdf_Exception
     */
    public function __construct($val)
    {
        settype($val, 'string');
        if (strpos($val,"\x00") !== false) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Null character is not allowed in PDF Names');
        }
        $this->value   = (string)$val;
    }


    /**
     * Return type of the element.
     *
     * @return integer
     */
    public function getType()
    {
        return Zend_Pdf_Element::TYPE_NAME;
    }


    /**
     * Escape string according to the PDF rules
     *
     * @param string $inStr
     * @return string
     */
    public static function escape($inStr)
    {
        $outStr = '';

        for ($count = 0; $count < strlen($inStr); $count++) {
            $nextCode = ord($inStr[$count]);

            switch ($inStr[$count]) {
                case '(':
                // fall through to next case
                case ')':
                // fall through to next case
                case '<':
                // fall through to next case
                case '>':
                // fall through to next case
                case '[':
                // fall through to next case
                case ']':
                // fall through to next case
                case '{':
                // fall through to next case
                case '}':
                // fall through to next case
                case '/':
                // fall through to next case
                case '%':
                // fall through to next case
                case '\\':
                // fall through to next case
                case '#':
                    $outStr .= sprintf('#%02X', $nextCode);
                    break;

                default:
                    if ($nextCode >= 33 && $nextCode <= 126 ) {
                        // Visible ASCII symbol
                        $outStr .= $inStr[$count];
                    } else {
                        $outStr .= sprintf('#%02X', $nextCode);
                    }
            }

        }

        return $outStr;
    }


    /**
     * Unescape string according to the PDF rules
     *
     * @param string $inStr
     * @return string
     */
    public static function unescape($inStr)
    {
        $outStr = '';

        for ($count = 0; $count < strlen($inStr); $count++) {
            if ($inStr[$count] != '#' )  {
                $outStr .= $inStr[$count];
            } else {
                // Escape sequence
                $outStr .= chr(base_convert(substr($inStr, $count+1, 2), 16, 10 ));
                $count +=2;
            }
        }
        return $outStr;
    }


    /**
     * Return object as string
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        return '/' . self::escape((string)$this->value);
    }
}
