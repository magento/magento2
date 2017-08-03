<?php
/**
 * Form Element Select Data Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class \Magento\Customer\Model\Metadata\Form\Select
 *
 * @since 2.0.0
 */
class Select extends AbstractData
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function extractValue(RequestInterface $request)
    {
        return $this->_getRequestValue($request);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function validateValue($value)
    {
        $errors = [];
        $attribute = $this->getAttribute();
        $label = __($attribute->getStoreLabel());

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->_value;
        }

        if ($attribute->isRequired() && empty($value) && $value !== '0') {
            $errors[] = __('"%1" is a required value.', $label);
        }

        if (!$errors && !$attribute->isRequired() && empty($value)) {
            return true;
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function compactValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * Return a text for option value
     *
     * @param string|int $value
     * @return string
     * @since 2.0.0
     */
    protected function _getOptionText($value)
    {
        foreach ($this->getAttribute()->getOptions() as $option) {
            if ($option->getValue() == $value && !is_bool($value)) {
                return $option->getLabel();
            }
        }
        return '';
    }

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @return string
     * @since 2.0.0
     */
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->_value;
        if ($format === ElementFactory::OUTPUT_FORMAT_JSON) {
            $output = $value;
        } elseif ($value != '') {
            $output = $this->_getOptionText($value);
        } else {
            $output = '';
        }

        return $output;
    }
}
