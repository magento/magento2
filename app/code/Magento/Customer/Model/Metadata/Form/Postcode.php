<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as MagentoTimezone;

/**
 * Customer Address Postal/Zip Code Attribute Data Model
 * @since 2.0.0
 */
class Postcode extends AbstractData
{
    /**
     * @var DirectoryHelper
     * @since 2.0.0
     */
    protected $directoryHelper;

    /**
     * @param MagentoTimezone $localeDate
     * @param PsrLogger $logger
     * @param AttributeMetadataInterface $attribute
     * @param ResolverInterface $localeResolver
     * @param string $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param DirectoryHelper $directoryHelper
     * @since 2.0.0
     */
    public function __construct(
        MagentoTimezone $localeDate,
        PsrLogger $logger,
        AttributeMetadataInterface $attribute,
        ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax,
        DirectoryHelper $directoryHelper
    ) {
        $this->directoryHelper = $directoryHelper;
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
     * Return true and skip validation if country zip code is optional
     *
     * @param array|null|string $value
     * @return array|bool
     * @since 2.0.0
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
        if (count($errors) == 0) {
            return true;
        }
        return $errors;
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
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        return $this->_applyOutputFilter($this->_value);
    }
}
