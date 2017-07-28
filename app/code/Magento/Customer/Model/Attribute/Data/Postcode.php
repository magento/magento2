<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Attribute\Data;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as MagentoTimezone;

/**
 * Customer Address Postal/Zip Code Attribute Data Model
 * This Data Model Has to Be Set Up in additional EAV attribute table
 * @since 2.0.0
 */
class Postcode extends \Magento\Eav\Model\Attribute\Data\AbstractData
{
    /**
     * @var \Magento\Directory\Helper\Data
     * @since 2.0.0
     */
    protected $directoryHelper;

    /**
     * @param MagentoTimezone $localeDate
     * @param PsrLogger $logger
     * @param ResolverInterface $localeResolver
     * @param DirectoryHelper $directoryHelper
     * @since 2.0.0
     */
    public function __construct(
        MagentoTimezone $localeDate,
        PsrLogger $logger,
        ResolverInterface $localeResolver,
        DirectoryHelper $directoryHelper
    ) {
        $this->directoryHelper = $directoryHelper;
        parent::__construct($localeDate, $logger, $localeResolver);
    }

    /**
     * Validate postal/zip code
     * Return true and skip validation if country zip code is optional
     *
     * @param array|string $value
     * @return array|bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function outputValue($format = AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = $this->getEntity()
            ->getData($this->getAttribute()->getAttributeCode());
        $value = $this->_applyOutputFilter($value);
        return $value;
    }
}
