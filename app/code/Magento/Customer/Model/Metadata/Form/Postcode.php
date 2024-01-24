<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as MagentoTimezone;

/**
 * Customer Address Postal/Zip Code Attribute Data Model
 */
class Postcode extends Text
{
    /**
     * @var DirectoryHelper
     */
    protected DirectoryHelper $directoryHelper;

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
        $stringHelper = $stringHelper ?? \Magento\Framework\App\ObjectManager::getInstance()->get(StringUtils::class);
        parent::__construct(
            $localeDate,
            $logger,
            $attribute,
            $localeResolver,
            $value,
            $entityTypeCode,
            $isAjax,
            $stringHelper
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
}
