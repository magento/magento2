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
 * @category  Zend
 * @package   Zend_Text
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Zend_Text_MultiByte contains multibyte safe string methods
 *
 * @category  Zend
 * @package   Zend_Text
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_MultiByte
{
    /**
     * Word wrap
     *
     * @param  string  $string
     * @param  integer $width
     * @param  string  $break
     * @param  boolean $cut
     * @param  string  $charset
     * @return string
     */
    public static function wordWrap($string, $width = 75, $break = "\n", $cut = false, $charset = 'utf-8')
    {
        $stringWidth = iconv_strlen($string, $charset);
        $breakWidth  = iconv_strlen($break, $charset);
        
        if (strlen($string) === 0) {
            return '';
        } elseif ($breakWidth === null) {
            throw new Zend_Text_Exception('Break string cannot be empty');
        } elseif ($width === 0 && $cut) {
            throw new Zend_Text_Exception('Can\'t force cut when width is zero');
        }
        
        $result    = '';
        $lastStart = $lastSpace = 0;
        
        for ($current = 0; $current < $stringWidth; $current++) {
            $char = iconv_substr($string, $current, 1, $charset);
            
            if ($breakWidth === 1) {
                $possibleBreak = $char;
            } else {
                $possibleBreak = iconv_substr($string, $current, $breakWidth, $charset);
            }
            
            if ($possibleBreak === $break) {
                $result    .= iconv_substr($string, $lastStart, $current - $lastStart + $breakWidth, $charset);
                $current   += $breakWidth - 1;
                $lastStart  = $lastSpace = $current + 1;
            } elseif ($char === ' ') {
                if ($current - $lastStart >= $width) {
                    $result    .= iconv_substr($string, $lastStart, $current - $lastStart, $charset) . $break;
                    $lastStart  = $current + 1;
                }
                
                $lastSpace = $current;
            } elseif ($current - $lastStart >= $width && $cut && $lastStart >= $lastSpace) {
                $result    .= iconv_substr($string, $lastStart, $current - $lastStart, $charset) . $break;
                $lastStart  = $lastSpace = $current;
            } elseif ($current - $lastStart >= $width && $lastStart < $lastSpace) {
                $result    .= iconv_substr($string, $lastStart, $lastSpace - $lastStart, $charset) . $break;
                $lastStart  = $lastSpace = $lastSpace + 1;
            }
        }
        
        if ($lastStart !== $current) {
            $result .= iconv_substr($string, $lastStart, $current - $lastStart, $charset);
        }
        
        return $result;
    }

    /**
     * String padding
     *
     * @param  string  $input
     * @param  integer $padLength
     * @param  string  $padString
     * @param  integer $padType
     * @param  string  $charset
     * @return string
     */
    public static function strPad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT, $charset = 'utf-8')
    {
        $return          = '';
        $lengthOfPadding = $padLength - iconv_strlen($input, $charset);
        $padStringLength = iconv_strlen($padString, $charset);

        if ($padStringLength === 0 || $lengthOfPadding <= 0) {
            $return = $input;
        } else {
            $repeatCount = floor($lengthOfPadding / $padStringLength);

            if ($padType === STR_PAD_BOTH) {
                $lastStringLeft  = '';
                $lastStringRight = '';
                $repeatCountLeft = $repeatCountRight = ($repeatCount - $repeatCount % 2) / 2;

                $lastStringLength       = $lengthOfPadding - 2 * $repeatCountLeft * $padStringLength;
                $lastStringLeftLength   = $lastStringRightLength = floor($lastStringLength / 2);
                $lastStringRightLength += $lastStringLength % 2;

                $lastStringLeft  = iconv_substr($padString, 0, $lastStringLeftLength, $charset);
                $lastStringRight = iconv_substr($padString, 0, $lastStringRightLength, $charset);

                $return = str_repeat($padString, $repeatCountLeft) . $lastStringLeft
                        . $input
                        . str_repeat($padString, $repeatCountRight) . $lastStringRight;
            } else {
                $lastString = iconv_substr($padString, 0, $lengthOfPadding % $padStringLength, $charset);

                if ($padType === STR_PAD_LEFT) {
                    $return = str_repeat($padString, $repeatCount) . $lastString . $input;
                } else {
                    $return = $input . str_repeat($padString, $repeatCount) . $lastString;
                }
            }
        }

        return $return;
    }
}
