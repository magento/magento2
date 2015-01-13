<?php
/**
 * Form Element Date Data Model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Api\ArrayObjectSearch;

class Date extends AbstractData
{
    /**
     * {@inheritdoc}
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        return $this->_applyInputFilter($value);
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value)
    {
        $errors = [];
        $attribute = $this->getAttribute();
        $label = $attribute->getStoreLabel();

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->_value;
        }

        if ($attribute->isRequired() && empty($value)) {
            $errors[] = __('"%1" is a required value.', $label);
        }

        if (!$errors && !$attribute->isRequired() && empty($value)) {
            return true;
        }

        $result = $this->_validateInputRule($value);
        if ($result !== true) {
            $errors = array_merge($errors, $result);
        }

        //range validation
        $validateRules = $attribute->getValidationRules();

        $minDateValue = ArrayObjectSearch::getArrayElementByName(
            $validateRules,
            'date_range_min'
        );

        $maxDateValue = ArrayObjectSearch::getArrayElementByName(
            $validateRules,
            'date_range_max'
        );

        if (!is_null($minDateValue) && strtotime($value) < $minDateValue
            || !is_null($maxDateValue) && strtotime($value) > $maxDateValue
        ) {
            if (!is_null($minDateValue) && !is_null($maxDateValue)) {
                $errors[] = __(
                    'Please enter a valid date between %1 and %2 at %3.',
                    date('d/m/Y', $minDateValue),
                    date('d/m/Y', $maxDateValue),
                    $label
                );
            } elseif (!is_null($minDateValue)) {
                $errors[] = __(
                    'Please enter a valid date equal to or greater than %1 at %2.',
                    date('d/m/Y', $minDateValue),
                    $label
                );
            } elseif (!is_null($maxDateValue)) {
                $errors[] = __(
                    'Please enter a valid date less than or equal to %1 at %2.',
                    date('d/m/Y', $maxDateValue),
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
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
        if ($value !== false) {
            if (empty($value)) {
                $value = null;
            }
            return $value;
        }
        return false;
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
        $value = $this->_value;
        if ($value) {
            switch ($format) {
                case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT:
                case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_HTML:
                case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_PDF:
                    $this->_dateFilterFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM);
                    break;
            }
            $value = $this->_applyOutputFilter($value);
        }

        $this->_dateFilterFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);

        return $value;
    }
}
