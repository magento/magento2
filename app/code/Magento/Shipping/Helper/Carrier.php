<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Helper;

/**
 * Carrier helper
 */
class Carrier extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Carriers root xml path
     */
    const XML_PATH_CARRIERS_ROOT = 'carriers';

    /**
     * Locale interface
     *
     * @var \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    protected $localeResolver;

    /**
     * Store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->localeResolver = $localeResolver;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Get online shipping carrier codes
     *
     * @param int|\Magento\Store\Model\Store|null $store
     * @return array
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
}
