<?php
/**
 * Form Element Multiline Data Model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

class Multiline extends Text
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
        for ($i = 0; $i < $attribute->getMultilineCount(); $i++) {
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
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        return parent::compactValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * {@inheritdoc}
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
