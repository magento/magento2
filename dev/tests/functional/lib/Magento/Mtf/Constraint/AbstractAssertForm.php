<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Constraint;

/**
 * Class AssertForm
 * Abstract class AssertForm
 * Implements:
 *  - verify fixture data and form data
 *  - sort multidimensional array by paths
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractAssertForm extends AbstractConstraint
{
    /**
     * Notice message.
     *
     * @var string
     */
    protected $notice = "\nForm data not equals to passed from fixture:\n";

    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = [];

    /**
     * Verify fixture and form data.
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
            if (in_array($key, $this->skippedFields)) {
                continue;
            }
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
     * Sort array by value.
     *
     * @param array $data
     * @return array
     */
    protected function sortData(array $data)
    {
        $scalarValues = [];
        $arrayValues = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $arrayValues[$key] = $this->sortData($value);
            } else {
                $scalarValues[$key] = $value;
            }
        }
        asort($scalarValues);
        foreach (array_keys($arrayValues) as $key) {
            if (!is_numeric($key)) {
                ksort($arrayValues);
                break;
            }
        }

        return $scalarValues + $arrayValues;
    }

    /**
     * Sort multidimensional array by paths.
     * Pattern path: key/subKey::sorkKey.
     * Exapmle:
     * $data = [
     *     'custom_options' => [
     *         'options' => [
     *             0 => [
     *                 'title' => 'title_2',
     *             ],
     *             1 => [
     *                 'title' => 'title_1'
     *             ]
     *         ]
     *     ]
     * ];
     * $paths = ['custom_options/options::title'];
     *
     * Result:
     * $data = [
     *     'custom_options' => [
     *         'options' => [
     *             title_1 => [
     *                 'title' => 'title_1',
     *             ],
     *             title_2 => [
     *                 'title' => 'title_2'
     *             ]
     *         ]
     *     ]
     * ];
     *
     * @param array $data
     * @param string $path
     * @param string $path
     * @return array
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortDataByPath(array $data, $path)
    {
        $steps = explode('/', $path);
        $key = array_shift($steps);
        $order = null;
        $nextPath = empty($steps) ? null : implode('/', $steps);

        if (false !== strpos($key, '::')) {
            list($key, $order) = explode('::', $key);
        }
        if ($key && !isset($data[$key])) {
            return $data;
        }

        if ($key) {
            if ($order) {
                $data[$key] = $this->sortMultidimensionalArray($data[$key], $order);
            }
            if ($nextPath) {
                $data[$key] = $this->sortDataByPath($data[$key], $nextPath);
            }
        } else {
            $data = $this->sortMultidimensionalArray($data, $order);
            if ($nextPath) {
                $data = $this->sortDataByPath($data, $nextPath);
            }
        }

        return $data;
    }

    /**
     * Sort multidimensional array by key.
     *
     * @param array $data
     * @param string $orderKey
     * @return array
     */
    protected function sortMultidimensionalArray(array $data, $orderKey)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (isset($value[$orderKey])) {
                $key = is_numeric($value[$orderKey]) ? (int)$value[$orderKey] : $value[$orderKey];
            }
            $result[$key] = $value;
        }
        ksort($result);
        return $result;
    }

    /**
     * Convert array to string.
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
     * Prepare errors to string.
     *
     * @param array $errors
     * @param string|null $notice
     * @param string $indent [optional]
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
            $notice = $this->notice;
        }
        return $notice . implode("\n", $result);
    }
}
