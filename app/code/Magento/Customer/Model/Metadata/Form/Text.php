<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Api\ArrayObjectSearch;

/**
 * Form Text metadata
 */
class Text extends AbstractData
{
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param AttributeMetadataInterface $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        AttributeMetadataInterface $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax,
        \Magento\Framework\Stdlib\StringUtils $stringHelper
    ) {
        parent::__construct($localeDate, $logger, $attribute, $localeResolver, $value, $entityTypeCode, $isAjax);
        $this->_string = $stringHelper;
    }

    /**
     * @inheritdoc
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        return $this->_applyInputFilter($this->_getRequestValue($request));
    }

    /**
     * @inheritdoc
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

        if (!$attribute->isRequired() && empty($value)) {
            return true;
        }

        if (empty($value) && $value !== '0') {
            $errors[] = __('"%1" is a required value.', $label);
        }

        $errors = $this->validateLength($value, $attribute, $errors);

        $result = $this->_validateInputRule($value);
        if ($result !== true) {
            $errors = array_merge($errors, $result);
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }

    /**
     * @inheritdoc
     */
    public function compactValue($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * @inheritdoc
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        return $this->_applyOutputFilter($this->_value);
    }

    /**
     * Length validation
     *
     * @param mixed $value
     * @param AttributeMetadataInterface $attribute
     * @param array $errors
     * @return array
     */
    private function validateLength($value, AttributeMetadataInterface $attribute, array $errors): array
    {
        // validate length
        $label = __($attribute->getStoreLabel());

        $length = $value ? $this->_string->strlen(trim($value)) : 0;

        $validateRules = $attribute->getValidationRules();

        if (!empty(ArrayObjectSearch::getArrayElementByName($validateRules, 'input_validation'))) {
            $minTextLength = ArrayObjectSearch::getArrayElementByName(
                $validateRules,
                'min_text_length'
            );
            if ($minTextLength !== null && $length < $minTextLength) {
                $errors[] = __('"%1" length must be equal or greater than %2 characters.', $label, $minTextLength);
            }

            $maxTextLength = ArrayObjectSearch::getArrayElementByName(
                $validateRules,
                'max_text_length'
            );
            if ($maxTextLength !== null && $length > $maxTextLength) {
                $errors[] = __('"%1" length must be equal or less than %2 characters.', $label, $maxTextLength);
            }
        }

        return $errors;
    }
}
