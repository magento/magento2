<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\App\RequestInterface;

/**
 * EAV Entity Attribute Multiply line Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Multiline extends \Magento\Eav\Model\Attribute\Data\Text
{
    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
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
     * Validate data
     *
     * Return true or array of errors
     *
     * @param array|string $value
     * @return bool|array
     */
    public function validateValue($value)
    {
        $errors = [];
        $lines = $this->processValue($value);
        $attribute = $this->getAttribute();

        if ($attribute->getIsRequired() && empty($lines)) {
            $attributeLabel = __($attribute->getStoreLabel());
            $errors[] = __('"%1" is a required value.', $attributeLabel);
        }

        $maxAllowedLineCount = $attribute->getMultilineCount();
        if (count($lines) > $maxAllowedLineCount) {
            $attributeLabel = __($attribute->getStoreLabel());
            $errors[] = __('"%1" cannot contain more than %2 lines.', $attributeLabel, $maxAllowedLineCount);
        }

        foreach ($lines as $lineIndex => $line) {
            // First line must be always validated
            if ($lineIndex == 0 || !empty($line)) {
                $result = parent::validateValue($line);
                if ($result !== true) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $errors = array_merge($errors, $result);
                }
            }
        }

        return (count($errors) == 0) ? true : $errors;
    }

    /**
     * Process value before validation
     *
     * @param bool|string|array $value
     * @return array list of lines represented by given value
     */
    protected function processValue($value)
    {
        if ($value === false) {
            // try to load original value and validate it
            $attribute = $this->getAttribute();
            $entity = $this->getEntity();
            $value = $entity->getDataUsingMethod($attribute->getAttributeCode());
        }
        if (!is_array($value)) {
            $value = $value !== null ? explode("\n", $value) : [];
        }
        return $value;
    }

    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return $this
     */
    public function compactValue($value)
    {
        if (is_array($value)) {
            $value = trim(implode("\n", $value));
        }
        return parent::compactValue($value);
    }

    /**
     * Restore attribute value from SESSION to entity model
     *
     * @param array|string $value
     * @return $this
     * @codeCoverageIgnore
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @return array|string
     */
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $values = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        if ($values && !is_array($values)) {
            $values = explode("\n", $values);
        }
        $values = array_map([$this, '_applyOutputFilter'], $values);
        switch ($format) {
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ARRAY:
                $output = $values;
                break;
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML:
                $output = implode("<br />", $values);
                break;
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ONELINE:
                $output = implode(" ", $values);
                break;
            default:
                $output = implode("\n", $values);
                break;
        }
        return $output;
    }
}
