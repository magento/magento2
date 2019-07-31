<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Attribute\Data;

use Magento\Framework\App\RequestInterface;

/**
 * EAV Entity Attribute Text Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Text extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\StringUtils $stringHelper
    ) {
        parent::__construct($localeDate, $logger, $localeResolver);
        $this->_string = $stringHelper;
    }

    /**
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
    {
        $value = trim($this->_getRequestValue($request));
        return $this->_applyInputFilter($value);
    }

    /**
     * Validate data
     *
     * Return true or array of errors
     *
     * @param array|string $value
     * @return bool|array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateValue($value)
    {
        $errors = [];
        $attribute = $this->getAttribute();

        if ($value === false) {
            // try to load original value and validate it
            $value = $this->getEntity()->getDataUsingMethod($attribute->getAttributeCode());
        }

        if (!$attribute->getIsRequired() && empty($value)) {
            return true;
        }

        if (empty($value) && $value !== '0' && $attribute->getDefaultValue() === null) {
            $label = __($attribute->getStoreLabel());
            $errors[] = __('"%1" is a required value.', $label);
        }

        $validateLengthResult = $this->validateLength($attribute, $value);
        $errors = array_merge($errors, $validateLengthResult);

        $validateInputRuleResult = $this->validateInputRule($value);
        $errors = array_merge($errors, $validateInputRuleResult);

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
     */
    public function compactValue($value)
    {
        if ($value !== false) {
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
     */
    public function restoreValue($value)
    {
        return $this->compactValue($value);
    }

    /**
     * Return formatted attribute value from entity model
     *
     * @param string $format
     * @return string|array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        $value = $this->_applyOutputFilter($value);

        return $value;
    }

    /**
     * Validates value length by attribute rules
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $value
     * @return array errors
     */
    private function validateLength(\Magento\Eav\Model\Attribute $attribute, string $value): array
    {
        $errors = [];
        $length = $this->_string->strlen(trim($value));
        $validateRules = $attribute->getValidateRules();

        if (!empty($validateRules['input_validation'])) {
            if (!empty($validateRules['min_text_length']) && $length < $validateRules['min_text_length']) {
                $label = __($attribute->getStoreLabel());
                $v = $validateRules['min_text_length'];
                $errors[] = __('"%1" length must be equal or greater than %2 characters.', $label, $v);
            }
            if (!empty($validateRules['max_text_length']) && $length > $validateRules['max_text_length']) {
                $label = __($attribute->getStoreLabel());
                $v = $validateRules['max_text_length'];
                $errors[] = __('"%1" length must be equal or less than %2 characters.', $label, $v);
            }
        }

        return $errors;
    }

    /**
     * Validate value by attribute input validation rule.
     *
     * @param string $value
     * @return array
     */
    private function validateInputRule(string $value): array
    {
        $result = $this->_validateInputRule($value);
        return \is_array($result) ? $result : [];
    }
}
