<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib;

/**
 * Magento methods to work with string
 */
class String
{
    /**
     * Default charset
     */
    const ICONV_CHARSET = 'UTF-8';

    /**
     * Capitalize first letters and convert separators if needed
     *
     * @param string $str
     * @param string $sourceSeparator
     * @param string $destinationSeparator
     * @return string
     */
    public function upperCaseWords($str, $sourceSeparator = '_', $destinationSeparator = '_')
    {
        return str_replace(' ', $destinationSeparator, ucwords(str_replace($sourceSeparator, ' ', $str)));
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
        $str = $this->split($str, $length);
        $newStr = '';
        foreach ($str as $part) {
            if ($this->strlen($part) >= $length) {
                $lastDelimiter = $this->strpos($this->strrev($part), $needle);
                $tmpNewStr = $this->substr($this->strrev($part), 0, $lastDelimiter) . $insert
                    . $this->substr($this->strrev($part), $lastDelimiter);
                $newStr .= $this->strrev($tmpNewStr);
            } else {
                $newStr .= $part;
            }
        }
        return $newStr;
    }

    /**
     * Binary-safe variant of strSplit()
     * + option not to break words
     * + option to trim spaces (between each word)
     * + option to set character(s) (pcre pattern) to be considered as words separator
     *
     * @param string $value
     * @param int $length
     * @param bool $keepWords
     * @param bool $trim
     * @param string $wordSeparatorRegex
     * @return string[]
     */
    public function split($value, $length = 1, $keepWords = false, $trim = false, $wordSeparatorRegex = '\s')
    {
        $result = [];
        $strLen = $this->strlen($value);
        if (!$strLen || !is_int($length) || $length <= 0) {
            return $result;
        }
        if ($trim) {
            $value = trim(preg_replace('/\s{2,}/siu', ' ', $value));
        }
        // do a usual str_split, but safe for our encoding
        if (!$keepWords || $length < 2) {
            for ($offset = 0; $offset < $strLen; $offset += $length) {
                $result[] = $this->substr($value, $offset, $length);
            }
        } else {
            // split smartly, keeping words
            $split = preg_split('/(' . $wordSeparatorRegex . '+)/siu', $value, null, PREG_SPLIT_DELIM_CAPTURE);
            $index = 0;
            $space = '';
            $spaceLen = 0;
            foreach ($split as $key => $part) {
                if ($trim) {
                    // ignore spaces (even keys)
                    if ($key % 2) {
                        continue;
                    }
                    $space = ' ';
                    $spaceLen = 1;
                }
                if (empty($result[$index])) {
                    $currentLength = 0;
                    $result[$index] = '';
                    $space = '';
                    $spaceLen = 0;
                } else {
                    $currentLength = $this->strlen($result[$index]);
                }
                $partLength = $this->strlen($part);
                // add part to current last element
                if ($currentLength + $spaceLen + $partLength <= $length) {
                    $result[$index] .= $space . $part;
                } elseif ($partLength <= $length) {
                    // add part to new element
                    $index++;
                    $result[$index] = $part;
                } else {
                    // break too long part recursively
                    foreach ($this->split($part, $length, false, $trim, $wordSeparatorRegex) as $subPart) {
                        $index++;
                        $result[$index] = $subPart;
                    }
                }
            }
        }
        // remove last element, if empty
        $count = count($result);
        if ($count) {
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
     * Clean non UTF-8 characters
     *
     * @param string $string
     * @return string
     */
    public function cleanString($string)
    {
        if ('"libiconv"' == ICONV_IMPL) {
            return iconv(self::ICONV_CHARSET, self::ICONV_CHARSET . '//IGNORE', $string);
        } else {
            return $string;
        }
    }

    /**
     * Pass through to iconv_substr()
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
     * Binary-safe strrev()
     *
     * @param string $str
     * @return string
     */
    public function strrev($str)
    {
        $result = '';
        $strLen = $this->strlen($str);
        if (!$strLen) {
            return $result;
        }
        for ($i = $strLen - 1; $i >= 0; $i--) {
            $result .= $this->substr($str, $i, 1);
        }
        return $result;
    }

    /**
     * Find position of first occurrence of a string
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @return int|bool
     */
    public function strpos($haystack, $needle, $offset = null)
    {
        return iconv_strpos($haystack, $needle, $offset, self::ICONV_CHARSET);
    }
}
