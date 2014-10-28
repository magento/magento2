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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: MultiByte.php 21931 2010-04-18 15:25:32Z dasprid $
 */

/**
 * Zend_Text_MultiByte contains multibyte safe string methods
 *
 * @category  Zend
 * @package   Zend_Text
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
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
    public static function wordWrap($string, $width = 75, $break = "\n", $cut = false, $charset = 'UTF-8')
    {
        $result     = array();
        $breakWidth = iconv_strlen($break, $charset);

        while (($stringLength = iconv_strlen($string, $charset)) > 0) {
            $breakPos = iconv_strpos($string, $break, 0, $charset);

            if ($breakPos !== false && $breakPos < $width) {
                if ($breakPos === $stringLength - $breakWidth) {
                    $subString = $string;
                    $cutLength = null;
                } else {
                    $subString = iconv_substr($string, 0, $breakPos, $charset);
                    $cutLength = $breakPos + $breakWidth;
                }
            } else {
                $subString = iconv_substr($string, 0, $width, $charset);

                if ($subString === $string) {
                    $cutLength = null;
                } else {
                    $nextChar = iconv_substr($string, $width, 1, $charset);
                    
                    if ($breakWidth === 1) {
                        $nextBreak = $nextChar;
                    } else {
                        $nextBreak = iconv_substr($string, $breakWidth, 1, $charset);
                    }

                    if ($nextChar === ' ' || $nextBreak === $break) {
                        $afterNextChar = iconv_substr($string, $width + 1, 1, $charset);

                        if ($afterNextChar === false) {
                            $subString .= $nextChar;
                        }

                        $cutLength = iconv_strlen($subString, $charset) + 1;
                    } else {
                        $spacePos = iconv_strrpos($subString, ' ', $charset);

                        if ($spacePos !== false) {
                            $subString = iconv_substr($subString, 0, $spacePos, $charset);
                            $cutLength = $spacePos + 1;
                        } else if ($cut === false) {
                            $spacePos = iconv_strpos($string, ' ', 0, $charset);

                            if ($spacePos !== false) {
                                $subString = iconv_substr($string, 0, $spacePos, $charset);
                                $cutLength = $spacePos + 1;
                            } else {
                                $subString = $string;
                                $cutLength = null;
                            }
                        } else {
                            $subString = iconv_substr($subString, 0, $width, $charset);
                            $cutLength = $width;
                        }
                    }
                }
            }

            $result[] = $subString;

            if ($cutLength !== null) {
                $string = iconv_substr($string, $cutLength, ($stringLength - $cutLength), $charset);
            } else {
                break;
            }
        }

        return implode($break, $result);
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
    public static function strPad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT, $charset = 'UTF-8')
    {
        $return          = '';
        $lengthOfPadding = $padLength - iconv_strlen($input, $charset);
        $padStringLength = iconv_strlen($padString, $charset);

        if ($padStringLength === 0 || $lengthOfPadding === 0) {
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
