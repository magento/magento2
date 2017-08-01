<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\App\RequestInterface;

/**
 * EAV Entity Attribute Date Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Date extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     * @since 2.0.0
     */
    public function extractValue(RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        return $this->_applyInputFilter($value);
    }

    /**
     * Validate data
     * Return true or array of errors
     *
     * @param array|string $value
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function validateValue($value)
    {
        $errors = [];
        $attribute = $this->getAttribute();

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->getEntity()->getDataUsingMethod($attribute->getAttributeCode());
        }

        if ($attribute->getIsRequired() && empty($value)) {
            $label = __($attribute->getStoreLabel());
            $errors[] = __('"%1" is a required value.', $label);
        }

        if (!$errors && !$attribute->getIsRequired() && empty($value)) {
            return true;
        }

        $result = $this->_validateInputRule($value);
        if ($result !== true) {
            $errors = array_merge($errors, $result);
        }

        //range validation
        $validateRules = $attribute->getValidateRules();
        if (!empty($validateRules['date_range_min']) && strtotime(
            $value
        ) < $validateRules['date_range_min'] || !empty($validateRules['date_range_max']) && strtotime(
            $value
        ) > $validateRules['date_range_max']
        ) {
            if (!empty($validateRules['date_range_min']) && !empty($validateRules['date_range_max'])) {
                $label = __($attribute->getStoreLabel());
                $errors[] = __(
                    'Please enter a valid date between %1 and %2 at %3.',
                    date('d/m/Y', $validateRules['date_range_min']),
                    date('d/m/Y', $validateRules['date_range_max']),
                    $label
                );
            } elseif (!empty($validateRules['date_range_min'])) {
                $label = __($attribute->getStoreLabel());
                $errors[] = __(
                    'Please enter a valid date equal to or greater than %1 at %2.',
                    date('d/m/Y', $validateRules['date_range_min']),
                    $label
                );
            } elseif (!empty($validateRules['date_range_max'])) {
                $label = __($attribute->getStoreLabel());
                $errors[] = __(
                    'Please enter a valid date less than or equal to %1 at %2.',
                    date('d/m/Y', $validateRules['date_range_max']),
                    $label
                );
            }
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }

    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return $this
     * @since 2.0.0
     */
    public function compactValue($value)
    {
        if ($value !== false) {
            if (empty($value)) {
                $value = null;
            }
            $this->getEntity()->setDataUsingMethod($this->getAttribute()->getAttributeCode(), $value);
        }
        return $this;
    }

    /**
     * Restore attribute value from SESSION to entity model
     *
     * @param array|string $value
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * Return formated attribute value from entity model
     *
     * @param string $format
     * @return string|array
     * @since 2.0.0
     */
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        if ($value) {
            switch ($format) {
                case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT:
                case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML:
                case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_PDF:
                    $this->_dateFilterFormat(\IntlDateFormatter::MEDIUM);
                    break;
            }
            $value = $this->_applyOutputFilter($value);
        }

        $this->_dateFilterFormat(\IntlDateFormatter::SHORT);

        return $value;
    }
}
