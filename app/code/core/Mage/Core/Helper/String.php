<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core data helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Helper_String extends Mage_Core_Helper_Abstract
{
    const ICONV_CHARSET = 'UTF-8';

    /**
     * Truncate a string to a certain length if necessary, appending the $etc string.
     * $remainder will contain the string that has been replaced with $etc.
     *
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncate($string, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        $remainder = '';
        if (0 == $length) {
            return '';
        }

        $originalLength = $this->strlen($string);
        if ($originalLength > $length) {
            $length -= $this->strlen($etc);
            if ($length <= 0) {
                return '';
            }
            $preparedString = $string;
            $preparedlength = $length;
            if (!$breakWords) {
                $preparedString = preg_replace('/\s+?(\S+)?$/u', '', $this->substr($string, 0, $length + 1));
                $preparedlength = $this->strlen($preparedString);
            }
            $remainder = $this->substr($string, $preparedlength, $originalLength);
            return $this->substr($preparedString, 0, $length) . $etc;
        }

        return $string;
    }

    /**
     * Retrieve string length using default charset
     *
     * @param string $string
     * @return int
     */
    public function strlen($string)
    {
        return iconv_strlen($string, self::ICONV_CHARSET);
    }

    /**
     * Passthrough to iconv_substr()
     *
     * @param string $string
     * @param int $offset
     * @param int $length
     * @return string
     */
    public function substr($string, $offset, $length = null)
    {
        $string = $this->cleanString($string);
        if (is_null($length)) {
            $length = $this->strlen($string) - $offset;
        }
        return iconv_substr($string, $offset, $length, self::ICONV_CHARSET);
    }

    /**
     * Split string and appending $insert string after $needle
     *
     * @param string $str
     * @param integer $length
     * @param string $needle
     * @param string $insert
     * @return string
     */
    public function splitInjection($str, $length = 50, $needle = '-', $insert = ' ')
    {
        $str = $this->str_split($str, $length);
        $newStr = '';
        foreach ($str as $part) {
            if ($this->strlen($part) >= $length) {
                $lastDelimetr = $this->strpos($this->strrev($part), $needle);
                $tmpNewStr = '';
                $tmpNewStr = $this->substr($this->strrev($part), 0, $lastDelimetr) . $insert . $this->substr($this->strrev($part), $lastDelimetr);
                $newStr .= $this->strrev($tmpNewStr);
            } else {
                $newStr .= $part;
            }
        }
        return $newStr;
    }

    /**
     * Binary-safe strrev()
     *
     * @param string $str
     * @return string
     */
    public function strrev($str)
    {
        $result = '';
        $strlen = $this->strlen($str);
        if (!$strlen) {
            return $result;
        }
        for ($i = $strlen-1; $i >= 0; $i--) {
            $result .= $this->substr($str, $i, 1);
        }
        return $result;
    }

    /**
     * Binary-safe variant of str_split()
     * + option not to break words
     * + option to trim spaces (between each word)
     * + option to set character(s) (pcre pattern) to be considered as words separator
     *
     * @param string $str
     * @param int $length
     * @param bool $keepWords
     * @param bool $trim
     * @param string $wordSeparatorRegex
     * @return array
     */
    public function str_split($str, $length = 1, $keepWords = false, $trim = false, $wordSeparatorRegex = '\s')
    {
        $result = array();
        $strlen = $this->strlen($str);
        if ((!$strlen) || (!is_int($length)) || ($length <= 0)) {
            return $result;
        }
        // trim
        if ($trim) {
            $str = trim(preg_replace('/\s{2,}/siu', ' ', $str));
        }
        // do a usual str_split, but safe for our encoding
        if ((!$keepWords) || ($length < 2)) {
            for ($offset = 0; $offset < $strlen; $offset += $length) {
                $result[] = $this->substr($str, $offset, $length);
            }
        }
        // split smartly, keeping words
        else {
            $split = preg_split('/(' . $wordSeparatorRegex . '+)/siu', $str, null, PREG_SPLIT_DELIM_CAPTURE);
            $i        = 0;
            $space    = '';
            $spaceLen = 0;
            foreach ($split as $key => $part) {
                if ($trim) {
                    // ignore spaces (even keys)
                    if ($key % 2) {
                        continue;
                    }
                    $space    = ' ';
                    $spaceLen = 1;
                }
                if (empty($result[$i])) {
                    $currentLength = 0;
                    $result[$i]    = '';
                    $space         = '';
                    $spaceLen      = 0;
                }
                else {
                    $currentLength = $this->strlen($result[$i]);
                }
                $partLength = $this->strlen($part);
                // add part to current last element
                if (($currentLength + $spaceLen + $partLength) <= $length) {
                    $result[$i] .= $space . $part;
                }
                // add part to new element
                elseif ($partLength <= $length) {
                    $i++;
                    $result[$i] = $part;
                }
                // break too long part recursively
                else {
                    foreach ($this->str_split($part, $length, false, $trim, $wordSeparatorRegex) as $subpart) {
                        $i++;
                        $result[$i] = $subpart;
                    }
                }
            }
        }
        // remove last element, if empty
        if ($count = count($result)) {
            if ($result[$count - 1] === '') {
                unset($result[$count - 1]);
            }
        }
        // remove first element, if empty
        if (isset($result[0]) && $result[0] === '') {
            array_shift($result);
        }
        return $result;
    }

    /**
     * Split words
     *
     * @param string $str The source string
     * @param bool $uniqueOnly Unique words only
     * @param int $maxWordLength Limit words count
     * @param string $wordSeparatorRegexp
     * @return array
     */
    function splitWords($str, $uniqueOnly = false, $maxWordLength = 0, $wordSeparatorRegexp = '\s')
    {
        $result = array();
        $split = preg_split('#' . $wordSeparatorRegexp . '#siu', $str, null, PREG_SPLIT_NO_EMPTY);
        foreach ($split as $word) {
            if ($uniqueOnly) {
                $result[$word] = $word;
            }
            else {
                $result[] = $word;
            }
        }
        if ($maxWordLength && count($result) > $maxWordLength) {
            $result = array_slice($result, 0, $maxWordLength);
        }
        return $result;
    }

    /**
     * Clean non UTF-8 characters
     *
     * @param string $string
     * @return string
     */
    public function cleanString($string)
    {
        return '"libiconv"' == ICONV_IMPL ? iconv(self::ICONV_CHARSET, self::ICONV_CHARSET . '//IGNORE', $string) : $string;
    }

    /**
     * Find position of first occurrence of a string
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @return int|false
     */
    public function strpos($haystack, $needle, $offset = null)
    {
        return iconv_strpos($haystack, $needle, $offset, self::ICONV_CHARSET);
    }

    /**
     * Sorts array with multibyte string keys
     *
     * @param array $sort
     * @return array
     */
    public function ksortMultibyte(array &$sort)
    {
        if (empty($sort)) {
            return false;
        }
        $oldLocale = setlocale(LC_COLLATE, "0");
        setlocale(LC_COLLATE, Mage::app()->getLocale()->getLocaleCode() . '.UTF8');
        ksort($sort, SORT_LOCALE_STRING);
        setlocale(LC_COLLATE, $oldLocale);

        return $sort;
    }

}
