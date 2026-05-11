<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\Attribute\Data;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\StringUtils;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as MagentoTimezone;

/**
 * Customer Address Postal/Zip Code Attribute Data Model.
 *
 * This Data Model Has to Be Set Up in additional EAV attribute table
 */
class Postcode extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var StringUtils|null
     */
    private $string;

    /**
     * @param MagentoTimezone $localeDate
     * @param PsrLogger $logger
     * @param ResolverInterface $localeResolver
     * @param DirectoryHelper $directoryHelper
     * @param StringUtils|null $stringHelper
     */
    public function __construct(
        MagentoTimezone $localeDate,
        PsrLogger $logger,
        ResolverInterface $localeResolver,
        DirectoryHelper $directoryHelper,
        ?StringUtils $stringHelper
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->string = $stringHelper ?? ObjectManager::getInstance()->get(StringUtils::class);
        parent::__construct($localeDate, $logger, $localeResolver);
    }

    /**
     * Validate postal/zip code.
     *
     * Return true and skip validation if country zip code is optional
     *
     * @param array|string $value
     * @return array|bool
     */
    public function validateValue($value)
    {
        $attribute = $this->getAttribute();

        $countryId = $this->getExtractedData('country_id');
        if ($this->directoryHelper->isZipCodeOptional($countryId)) {
            return true;
        }

        $errors = [];
        if (empty($value) && $value !== '0') {
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
     * Extract data from request and return value
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function extractValue(RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        return $this->_applyInputFilter($value);
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
    public function outputValue($format = AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->getEntity()
            ->getData($this->getAttribute()->getAttributeCode());
        $value = $this->_applyOutputFilter($value);
        return $value;
    }

    /**
     * Validates value length by attribute rules
     *
     * @param AbstractAttribute $attribute
     * @param string $value
     * @return array errors
     */
    private function validateLength(AbstractAttribute $attribute, string $value): array
    {
        $errors = [];
        $length = $this->string->strlen(trim($value));
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
