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
     * Extract tax rate data in a format which is
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @param Boolean $returnNumericLogic
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createArrayFromServiceObject($taxRate, $returnNumericLogic = false)
    {
        $taxRateFormData = [
            'tax_calculation_rate_id' => $taxRate->getId(),
            'tax_country_id' => $taxRate->getTaxCountryId(),
            'tax_region_id' => $taxRate->getTaxRegionId(),
            'tax_postcode' => $taxRate->getTaxPostcode(),
            'code' => $taxRate->getCode(),
            'rate' => $taxRate->getRate(),
            'zip_is_range' => $returnNumericLogic?0:false,
        ];

        if ($taxRate->getZipFrom() && $taxRate->getZipTo()) {
            $taxRateFormData['zip_is_range'] = $returnNumericLogic?1:true;
            $taxRateFormData['zip_from'] = $taxRate->getZipFrom();
            $taxRateFormData['zip_to'] = $taxRate->getZipTo();
        }

        if ($returnNumericLogic) {
            //format for the ajax on multiple sites titles
            $titleArray=($this->createTitleArrayFromServiceObject($taxRate));
            if (is_array($titleArray)) {
                foreach ($titleArray as $storeId => $title) {
                    $taxRateFormData['title[' . $storeId . ']']=$title;
                }
            }
        } else {
            //format for the form array on multiple sites titles
            $titleArray=($this->createTitleArrayFromServiceObject($taxRate));
            if (is_array($titleArray)) {
                $titleData = [];
                foreach ($titleArray as $storeId => $title) {
                    $titleData[] = [$storeId => $title];
                }
                if (count($titleArray)>0) {
                    $taxRateFormData['title'] = $titleData;
                }
            }
        }

        return $taxRateFormData;
    }
}
