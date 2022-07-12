<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as MagentoTimezone;

/**
 * Customer Address Postal/Zip Code Attribute Data Model
 */
class Postcode extends AbstractData
{
    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var StringUtils
     */
    protected $_string;

    /**
     * @param MagentoTimezone $localeDate
     * @param PsrLogger $logger
     * @param AttributeMetadataInterface $attribute
     * @param ResolverInterface $localeResolver
     * @param string $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param DirectoryHelper $directoryHelper
     * @param StringUtils|null $stringHelper
     */
    public function __construct(
        MagentoTimezone $localeDate,
        PsrLogger $logger,
        AttributeMetadataInterface $attribute,
        ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax,
        DirectoryHelper $directoryHelper,
        StringUtils $stringHelper = null
    ) {
        $this->directoryHelper = $directoryHelper;
        if (is_null($stringHelper)) {
            $stringHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(StringUtils::class);
        }
        $this->_string = $stringHelper;
        parent::__construct(
            $localeDate,
            $logger,
            $attribute,
            $localeResolver,
            $value,
            $entityTypeCode,
            $isAjax
        );
    }

    /**
     * Validate postal/zip code
     *
     * Return true and skip validation if country zip code is optional
     *
     * @param array|null|string $value
     * @return array|bool
     */
    public function validateValue($value)
    {
        $attribute = $this->getAttribute();
        $label = __($attribute->getStoreLabel());

        $countryId = $this->getExtractedData('country_id');
        if ($this->directoryHelper->isZipCodeOptional($countryId)) {
            return true;
        }

        $errors = [];
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
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        return $this->_applyInputFilter($this->_getRequestValue($request));
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
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
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
