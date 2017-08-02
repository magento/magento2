<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Helper;

/**
 * Carrier helper
 * @since 2.0.0
 */
class Carrier extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Carriers root xml path
     */
    const XML_PATH_CARRIERS_ROOT = 'carriers';

    /**
     * Config path to UE country list
     */
    const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * Locale interface
     *
     * @var \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct($context);
    }

    /**
     * Get online shipping carrier codes
     *
     * @param int|\Magento\Store\Model\Store|null $store
     * @return array
     * @since 2.0.0
     */
    public function getOnlineCarrierCodes($store = null)
    {
        $carriersCodes = [];
        foreach ($this->scopeConfig->getValue(
            self::XML_PATH_CARRIERS_ROOT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) as $carrierCode => $carrier) {
            if (isset($carrier['is_online']) && $carrier['is_online']) {
                $carriersCodes[] = $carrierCode;
            }
        }
        return $carriersCodes;
    }

    /**
     * Get shipping carrier config value
     *
     * @param string $carrierCode
     * @param string $configPath
     * @param null $store
     * @return string
     * @since 2.0.0
     */
    public function getCarrierConfigValue($carrierCode, $configPath, $store = null)
    {
        return $this->scopeConfig->getValue(
            sprintf('%s/%s/%s', self::XML_PATH_CARRIERS_ROOT, $carrierCode, $configPath),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Convert weight in different measure types
     *
     * @param int|float $value
     * @param string $sourceWeightMeasure
     * @param string $toWeightMeasure
     * @return int|null|string
     * @since 2.0.0
     */
    public function convertMeasureWeight($value, $sourceWeightMeasure, $toWeightMeasure)
    {
        if ($value) {
            $locale = $this->localeResolver->getLocale();
            $unitWeight = new \Zend_Measure_Weight($value, $sourceWeightMeasure, $locale);
            $unitWeight->setType($toWeightMeasure);
            return $unitWeight->getValue();
        }
        return null;
    }

    /**
     * Convert dimensions in different measure types
     *
     * @param  int|float $value
     * @param  string $sourceDimensionMeasure
     * @param  string $toDimensionMeasure
     * @return int|null|string
     * @since 2.0.0
     */
    public function convertMeasureDimension($value, $sourceDimensionMeasure, $toDimensionMeasure)
    {
        if ($value) {
            $locale = $this->localeResolver->getLocale();
            $unitDimension = new \Zend_Measure_Length($value, $sourceDimensionMeasure, $locale);
            $unitDimension->setType($toDimensionMeasure);
            return $unitDimension->getValue();
        }
        return null;
    }

    /**
     * Get name of measure by its type
     *
     * @param string $key
     * @return string
     * @since 2.0.0
     */
    public function getMeasureWeightName($key)
    {
        $weight = new \Zend_Measure_Weight(0);
        $conversionList = $weight->getConversionList();
        if (!empty($conversionList[$key]) && !empty($conversionList[$key][1])) {
            return $conversionList[$key][1];
        }
        return '';
    }

    /**
     * Get name of measure by its type
     *
     * @param string $key
     * @return string
     * @since 2.0.0
     */
    public function getMeasureDimensionName($key)
    {
        $weight = new \Zend_Measure_Length(0);
        $conversionList = $weight->getConversionList();
        if (!empty($conversionList[$key]) && !empty($conversionList[$key][1])) {
            return $conversionList[$key][1];
        }
        return '';
    }

    /**
     * Check whether specified country is in EU countries list
     *
     * @param string $countryCode
     * @param null|int $storeId
     * @return bool
     * @since 2.0.0
     */
    public function isCountryInEU($countryCode, $storeId = null)
    {
        $euCountries = explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_EU_COUNTRIES_LIST,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );

        return in_array($countryCode, $euCountries);
    }
}
