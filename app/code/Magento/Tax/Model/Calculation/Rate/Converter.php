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
}
