<?php
/**
 * Form Element Text Data Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Api\ArrayObjectSearch;

/**
 * Class \Magento\Customer\Model\Metadata\Form\Text
 *
 * @since 2.0.0
 */
class Text extends AbstractData
{
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $_string;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute,
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        return $this->_applyInputFilter($this->_getRequestValue($request));
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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

        // validate length
        $length = $this->_string->strlen(trim($value));

        $validateRules = $attribute->getValidationRules();

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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        return $this->_applyOutputFilter($this->_value);
    }
}
