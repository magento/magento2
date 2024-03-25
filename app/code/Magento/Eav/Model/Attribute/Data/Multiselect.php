<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\App\RequestInterface;

/**
 * EAV Entity Attribute Multiply select Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Multiselect extends AbstractData
{
    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
    {
        $values = $this->_getRequestValue($request);
        if ($values !== false && !is_array($values)) {
            $values = [$values];
        }
        return $values;
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
            $value = implode(',', $value);
        }
        if ($value !== false) {
            $this->getEntity()->setData($this->getAttribute()->getAttributeCode(), $value);
        }

        return $this;
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
        if (!is_array($values)) {
            $values = $values !== null ? explode(',', $values) : [];
        }

        switch ($format) {
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON:
            case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ARRAY:
                $output = $values;
                // fall-through intentional
            default:
                $output = [];
                foreach ($values as $value) {
                    if (!$value) {
                        continue;
                    }
                    $output[] = $this->getAttribute()->getSource()->getOptionText($value);
                }
                $output = implode(', ', $output);
                break;
        }

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function validateValue($value)
    {
        $errors = [];
        $attribute = $this->getAttribute();

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->getEntity()->getData($attribute->getAttributeCode());
        }

        if ($attribute->getIsRequired() && empty($value) && $value != '0') {
            $label = __($attribute->getStoreLabel());
            $errors[] = __('"%1" is a required value.', $label);
        }

        if (!empty($value) && $attribute->getSourceModel()) {
            $values = is_array($value) ? $value : explode(',', (string) $value);
            $errors = array_merge(
                $errors,
                $this->validateBySource($values)
            );
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate values using source
     *
     * @param array $values
     * @return array
     */
    private function validateBySource(array $values): array
    {
        $errors = [];
        foreach ($values as $value) {
            if (!$this->getAttribute()->getSource()->getOptionText($value)) {
                $errors[] = __(
                    'Attribute %1 does not contain option with Id %2',
                    $this->getAttribute()->getAttributeCode(),
                    $value
                );
            }
        }

        return $errors;
    }

    /**
     * @inheritdoc
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }
}
