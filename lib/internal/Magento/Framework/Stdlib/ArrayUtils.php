<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib;

/**
 * Class ArrayUtils
 *
 * @api
 */
class ArrayUtils
{
    /**
     * Sorts array with multibyte string keys
     *
     * @param  array $sort
     * @param  string $locale
     * @return array|bool
     */
    public function ksortMultibyte(array &$sort, $locale)
    {
        if (empty($sort)) {
            return false;
        }
        $oldLocale = setlocale(LC_COLLATE, "0");
        // use fallback locale if $localeCode is not available

        if (strpos($locale, '.UTF8') === false) {
            $locale .= '.UTF8';
        }

        setlocale(LC_COLLATE, $locale, 'C.UTF-8', 'en_US.utf8');
        ksort($sort, SORT_LOCALE_STRING);
        setlocale(LC_COLLATE, $oldLocale);

        return $sort;
    }

    /**
     * Decorate a plain array of arrays or objects
     * The array actually can be an object with Iterator interface
     *
     * Keys with prefix_* will be set:
     * *_is_first - if the element is first
     * *_is_odd / *_is_even - for odd/even elements
     * *_is_last - if the element is last
     *
     * The respective key/attribute will be set to element, depending on object it is or array.
     * \Magento\Framework\DataObject is supported.
     *
     * $forceSetAll true will cause to set all possible values for all elements.
     * When false (default), only non-empty values will be set.
     *
     * @param array $array
     * @param string $prefix
     * @param bool $forceSetAll
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function decorateArray($array, $prefix = 'decorated_', $forceSetAll = false)
    {
        // check if array or an object to be iterated given
        if (!(is_array($array) || is_object($array))) {
            return $array;
        }

        $keyIsFirst = "{$prefix}is_first";
        $keyIsOdd = "{$prefix}is_odd";
        $keyIsEven = "{$prefix}is_even";
        $keyIsLast = "{$prefix}is_last";

        $count = count($array);
        // this will force Iterator to load
        $index = 0;
        $isEven = false;
        foreach ($array as $key => $element) {
            if (is_object($element)) {
                $this->_decorateArrayObject($element, $keyIsFirst, 0 === $index, $forceSetAll || 0 === $index);
                $this->_decorateArrayObject($element, $keyIsOdd, !$isEven, $forceSetAll || !$isEven);
                $this->_decorateArrayObject($element, $keyIsEven, $isEven, $forceSetAll || $isEven);
                $isEven = !$isEven;
                $index++;
                $this->_decorateArrayObject(
                    $element,
                    $keyIsLast,
                    $index === $count,
                    $forceSetAll || $index === $count
                );
            } elseif (is_array($element)) {
                if ($forceSetAll || 0 === $index) {
                    $array[$key][$keyIsFirst] = 0 === $index;
                }
                if ($forceSetAll || !$isEven) {
                    $array[$key][$keyIsOdd] = !$isEven;
                }
                if ($forceSetAll || $isEven) {
                    $array[$key][$keyIsEven] = $isEven;
                }
                $isEven = !$isEven;
                $index++;
                if ($forceSetAll || $index === $count) {
                    $array[$key][$keyIsLast] = $index === $count;
                }
            }
        }
        return $array;
    }

    /**
     * Mark passed object with specified flag and appropriate value.
     *
     * @param \Magento\Framework\DataObject $element
     * @param string $key
     * @param bool $value
     * @param bool $isSkipped
     * @return void
     */
    private function _decorateArrayObject($element, $key, $value, $isSkipped)
    {
        if ($isSkipped && $element instanceof \Magento\Framework\DataObject) {
            $element->setData($key, $value);
        }
    }

    /**
     * Expands multidimensional array into flat structure.
     *
     * Example:
     *
     * ```php
     *  [
     *      'default' => [
     *          'web' => 2
     *      ]
     *  ]
     * ```
     *
     * Expands to:
     *
     * ```php
     *  [
     *      'default/web' => 2,
     *  ]
     * ```
     *
     * @param array $data The data to be flatten
     * @param string $path The leading path
     * @param string $separator The path parts separator
     * @return array
     */
    public function flatten(array $data, $path = '', $separator = '/')
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $result[$path . $separator . $key] = $value;

                continue;
            }

            $result = array_merge(
                $result,
                $this->flatten($value, $path ? $path . $separator . $key : $key, $separator)
            );
        }

        return $result;
    }

    /**
     * Search for array differences recursively.
     *
     * @param array $array1 The first array
     * @param array $array2 The second array
     * @return array Diff array
     */
    public function recursiveDiff(array $array1, array $array2)
    {
        $diff = [];

        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $aRecursiveDiff = $this->recursiveDiff($value, $array2[$key]);
                    if (count($aRecursiveDiff)) {
                        $diff[$key] = $aRecursiveDiff;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $diff[$key] = $value;
                    }
                }
            } else {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }
}
