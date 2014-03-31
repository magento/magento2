<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Shipping\Helper;

/**
 * Carrier helper
 */
class Carrier extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Carriers root xml path
     */
    const XML_PATH_CARRIERS_ROOT = 'carriers';

    /**
     * Locale interface
     *
     * @var \Magento\Locale\ResolverInterface $localeResolver
     */
    protected $localeResolver;

    /**
     * Store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $storeConfig;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Locale\ResolverInterface $localeResolver
     * @param \Magento\Core\Model\Store\ConfigInterface $storeConfig
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Locale\ResolverInterface $localeResolver,
        \Magento\Core\Model\Store\ConfigInterface $storeConfig
    ) {
        $this->localeResolver = $localeResolver;
        $this->storeConfig = $storeConfig;
        parent::__construct($context);
    }

    /**
     * Get online shipping carrier codes
     *
     * @param int|\Magento\Core\Model\Store|null $store
     * @return array
     */
    public function getOnlineCarrierCodes($store = null)
    {
        $carriersCodes = array();
        foreach ($this->storeConfig->getConfig(self::XML_PATH_CARRIERS_ROOT, $store) as $carrierCode => $carrier) {
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
        return $this->storeConfig->getConfig(
            sprintf('%s/%s/%s', self::XML_PATH_CARRIERS_ROOT, $carrierCode, $configPath),
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
