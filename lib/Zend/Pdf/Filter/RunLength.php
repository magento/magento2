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
 * @version    $Id: RunLength.php 20785 2010-01-31 09:43:03Z mikaelkael $
 */


/** Zend_Pdf_Filter_Interface */
#require_once 'Zend/Pdf/Filter/Interface.php';

/**
 * RunLength stream filter
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Filter_RunLength implements Zend_Pdf_Filter_Interface
{
    /**
     * Encode data
     *
     * @param string $data
     * @param array $params
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public static function encode($data, $params = null)
    {
        $output = '';

        $chainStartOffset = 0;
        $offset = 0;

        while ($offset < strlen($data)) {
            // Do not encode 2 char chains since they produce 2 char run sequence,
            // but it takes more time to decode such output (because of processing additional run)
            if (($repeatedCharChainLength = strspn($data, $data[$offset], $offset + 1, 127) + 1)  >  2) {
                if ($chainStartOffset != $offset) {
                    // Drop down previouse (non-repeatable chars) run
                    $output .= chr($offset - $chainStartOffset - 1)
                             . substr($data, $chainStartOffset, $offset - $chainStartOffset);
                }

                $output .= chr(257 - $repeatedCharChainLength) . $data[$offset];

                $offset += $repeatedCharChainLength;
                $chainStartOffset = $offset;
            } else {
                $offset++;

                if ($offset - $chainStartOffset == 128) {
                    // Maximum run length is reached
                    // Drop down non-repeatable chars run
                    $output .= "\x7F" . substr($data, $chainStartOffset, 128);

                    $chainStartOffset = $offset;
                }
            }
        }

        if ($chainStartOffset != $offset) {
            // Drop down non-repeatable chars run
            $output .= chr($offset - $chainStartOffset - 1) . substr($data, $chainStartOffset, $offset - $chainStartOffset);
        }

        $output .= "\x80";

        return $output;
    }

    /**
     * Decode data
     *
     * @param string $data
     * @param array $params
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public static function decode($data, $params = null)
    {
        $dataLength = strlen($data);
        $output = '';
        $offset = 0;

        while($offset < $dataLength) {
            $length = ord($data[$offset]);

            $offset++;

            if ($length == 128) {
                // EOD byte
                break;
            } else if ($length < 128) {
                $length++;

                $output .= substr($data, $offset, $length);

                $offset += $length;
            } else if ($length > 128) {
                $output .= str_repeat($data[$offset], 257 - $length);

                $offset++;
            }
        }

        return $output;
    }
}

