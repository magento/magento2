<?php
/**
 * Form Element Multiline Data Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

class Multiline extends Text
{
    /**
     * @inheritDoc
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        if (!is_array($value)) {
            $value = false;
        } else {
            $value = array_map([$this, '_applyInputFilter'], $value);
        }
        return $value;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateValue($value)
    {
        $errors = [];
        $attribute = $this->getAttribute();

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->_value;
            if (!is_array($value)) {
                $value = explode("\n", $value);
            }
        }

        if (!is_array($value)) {
            $value = [$value];
        }
        $multilineCount = $attribute->getMultilineCount();
        for ($i = 0; $i < $multilineCount; $i++) {
            if (!isset($value[$i])) {
                $value[$i] = null;
            }
            // validate first line
            if ($i == 0) {
                $result = parent::validateValue($value[$i]);
                if ($result !== true) {
                    $errors = $result;
                }
            } else {
                if (!empty($value[$i])) {
                    $result = parent::validateValue($value[$i]);
                    if ($result !== true) {
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $errors = array_merge($errors, $result);
                    }
                }
            }
        }

        if (count($errors) == 0) {
            return true;
        }
        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function compactValue($value)
    {
        if (is_array($value)) {
            $value = trim(implode("\n", $value));
        }
        $value = [$value];

        return parent::compactValue($value);
    }

    /**
     * @inheritDoc
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * @inheritDoc
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $values = $this->_value;
        if (!is_array($values)) {
            $values = explode("\n", $values);
        }
        $values = array_map([$this, '_applyOutputFilter'], $values);
        switch ($format) {
            case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_ARRAY:
                $output = $values;
                break;
            case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_HTML:
                $output = implode("<br />", $values);
                break;
            case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_ONELINE:
                $output = implode(" ", $values);
                break;
            default:
                $output = implode("\n", $values);
                break;
        }
        return $output;
    }
}
