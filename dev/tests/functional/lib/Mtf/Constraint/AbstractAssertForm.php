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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mtf\Constraint;

/**
 * Class AssertForm
 * Abstract class AssertForm
 */
abstract class AbstractAssertForm extends AbstractConstraint
{
    /**
     * Verify fixture and form data
     *
     * @param array $fixtureData
     * @param array $formData
     * @param bool $isStrict
     * @param bool $isPrepareError
     * @return array|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function verifyData(array $fixtureData, array $formData, $isStrict = false, $isPrepareError = true)
    {
        $errors = [];

        foreach ($fixtureData as $key => $value) {
            $formValue = isset($formData[$key]) ? $formData[$key] : null;
            if (is_numeric($formValue)) {
                $formValue = floatval($formValue);
            }

            if (null === $formValue) {
                $errors[] = '- field "' . $key . '" is absent in form';
            } elseif (is_array($value) && is_array($formValue)) {
                $valueErrors = $this->verifyData($value, $formValue, true, false);
                if (!empty($valueErrors)) {
                    $errors[$key] = $valueErrors;
                }
            } elseif ($value != $formValue) {
                if (is_array($value)) {
                    $value = $this->arrayToString($value);
                }
                if (is_array($formValue)) {
                    $formValue = $this->arrayToString($formValue);
                }
                $errors[] = sprintf('- %s: "%s" instead of "%s"', $key, $formValue, $value);
            }
        }

        if ($isStrict) {
            $diffData = array_diff(array_keys($formData), array_keys($fixtureData));
            if ($diffData) {
                $errors[] = '- fields ' . implode(', ', $diffData) . ' is absent in fixture';
            }
        }

        if ($isPrepareError) {
            return $this->prepareErrors($errors);
        }
        return $errors;
    }

    /**
     * Sort multidimensional array by paths
     *
     * @param array $data
     * @param array|string $paths
     * @return array
     */
    protected function sortData(array $data, $paths)
    {
        $paths = is_array($paths) ? $paths : [$paths];
        foreach ($paths as $path) {
            $values = &$data;
            $keys = explode('/', $path);

            $key = array_shift($keys);
            $order = null;
            while (null !== $key) {
                if (false !== strpos($key, '::')) {
                    list($key, $order) = explode('::', $key);
                }
                if ($key && !isset($values[$key])) {
                    $key = null;
                    continue;
                }

                if ($key) {
                    $values = &$values[$key];
                }
                if ($order) {
                    $values = $this->sortMultidimensionalArray($values, $order);
                    $order = null;
                }
                $key = array_shift($keys);
            }
        }

        return $data;
    }

    /**
     * Sort multidimensional array by key
     *
     * @param array $data
     * @param string $key
     * @return array
     */
    protected function sortMultidimensionalArray(array $data, $key)
    {
        $result = [];
        foreach ($data as $value) {
            $result[$value[$key]] = $value;
        }

        ksort($result);
        return $result;
    }

    /**
     * Convert array to string
     *
     * @param array $array
     * @return string
     */
    protected function arrayToString(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $value = is_array($value) ? $this->arrayToString($value) : $value;
            $result[] = "{$key} => {$value}";
        }

        return '[' . implode(', ', $result) . ']';
    }

    /**
     * Prepare errors to string
     *
     * @param array $errors
     * @param string|null $notice
     * @param string $indent
     * @return string
     */
    protected function prepareErrors(array $errors, $notice = null, $indent = '')
    {
        if (empty($errors)) {
            return '';
        }

        $result = [];
        foreach ($errors as $key => $error) {
            $result[] = is_array($error)
                ? $this->prepareErrors($error, "{$indent}{$key}:\n", $indent . "\t")
                : ($indent . $error);
        }

        if (null === $notice) {
            $notice = "\nForm data not equals to passed from fixture:\n";
        }
        return $notice . implode("\n", $result);
    }
}
