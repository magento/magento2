<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation\Rate;

/**
 * Tax Rate Model converter.
 *
 * Converts a Tax Rate Model to a Data Object or vice versa.
 */
class Converter
{
    /**
     * Convert a tax rate data object to an array of associated titles
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @return array
     */
    public function createTitleArrayFromServiceObject(\Magento\Tax\Api\Data\TaxRateInterface $taxRate)
    {
        $titles = $taxRate->getTitles();
        $titleData = [];
        if ($titles) {
            foreach ($titles as $title) {
                $titleData[$title->getStoreId()] = $title->getValue();
            }
        }
        return $titleData;
    }

    /**
     * Extract tax rate data in a format which is array
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @return array
     */
    public function createSimpleArrayFromServiceObject(\Magento\Tax\Api\Data\TaxRateInterface $taxRate)
    {
        $taxRateData = $taxRate->getData();
        if (isset($taxRateData['titles'])) {
            foreach ($taxRateData['titles'] as $storeId => $value) {
                $taxRateData['title[' . $storeId . ']'] = $value;
            }
        }
        unset($taxRateData['titles']);

        return $taxRateData;
    }

    /**
     * Extract tax rate data in a format which is
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @return array
     */
    public function createArrayFromServiceObject($taxRate)
    {
        $formData = [
            'tax_calculation_rate_id' => $taxRate->getId(),
            'tax_country_id' => $taxRate->getTaxCountryId(),
            'tax_region_id' => $taxRate->getTaxRegionId(),
            'tax_postcode' => $taxRate->getTaxPostcode(),
            'code' => $taxRate->getCode(),
            'rate' => $taxRate->getRate(),
            'zip_is_range' => false,
        ];

        if ($taxRate->getZipFrom() && $taxRate->getZipTo()) {
            $formData['zip_is_range'] = true;
            $formData['zip_from'] = $taxRate->getZipFrom();
            $formData['zip_to'] = $taxRate->getZipTo();
        }

        if ($taxRate->getTitles()) {
            $titleData = [];
            foreach ($taxRate->getTitles() as $title) {
                $titleData[] = [$title->getStoreId() => $title->getValue()];
            }
            $formData['title'] = $titleData;
        }

        return $formData;
    }
}
