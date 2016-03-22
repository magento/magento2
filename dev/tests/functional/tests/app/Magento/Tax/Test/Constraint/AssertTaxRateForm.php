<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Page\Adminhtml\TaxRateIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRateNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRateForm
 */
class AssertTaxRateForm extends AbstractConstraint
{
    /**
     * Assert that tax rate form filled correctly
     *
     * @param TaxRateIndex $taxRateIndexPage
     * @param TaxRateNew $taxRateNewPage
     * @param TaxRate $taxRate
     * @param TaxRate $initialTaxRate
     * @return void
     */
    public function processAssert(
        TaxRateIndex $taxRateIndexPage,
        TaxRateNew $taxRateNewPage,
        TaxRate $taxRate,
        TaxRate $initialTaxRate = null
    ) {
        $data = $this->prepareData($taxRate, $initialTaxRate);
        $filter = [
            'code' => $data['code'],
        ];

        $taxRateIndexPage->open();
        $taxRateIndexPage->getTaxRateGrid()->searchAndOpen($filter);
        $formData = $taxRateNewPage->getTaxRateForm()->getData($taxRate);
        $dataDiff = $this->verifyForm($formData, $data);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($dataDiff),
            'Tax Rate form was filled incorrectly.'
            . "\nLog:\n" . implode(";\n", $dataDiff)
        );
    }

    /**
     * Preparing data for verification
     *
     * @param TaxRate $taxRate
     * @param TaxRate $initialTaxRate
     * @return array
     */
    protected function prepareData(TaxRate $taxRate, TaxRate $initialTaxRate = null)
    {
        if ($initialTaxRate !== null) {
            $data = array_merge($initialTaxRate->getData(), $taxRate->getData());
            if ($taxRate->hasData('tax_country_id') && !$taxRate->hasData('tax_region_id')) {
                unset($data['tax_region_id']);
            }
        } else {
            $data = $taxRate->getData();
        }
        if ($data['zip_is_range'] === 'Yes') {
            unset($data['tax_postcode']);
        } else {
            unset($data['zip_from'], $data['zip_to']);
        }
        $data['rate'] = number_format($data['rate'], 4);

        return $data;
    }

    /**
     * Verifying that form is filled correctly
     *
     * @param array $formData
     * @param array $fixtureData
     * @return array $errorMessages
     */
    protected function verifyForm(array $formData, array $fixtureData)
    {
        $errorMessages = [];
        $skippedFields = [
            'id',
        ];

        foreach ($fixtureData as $key => $value) {
            if (in_array($key, $skippedFields)) {
                continue;
            }
            if ($value !== $formData[$key]) {
                $errorMessages[] = "Data in " . $key . " field is not equal."
                    . "\nExpected: " . $value
                    . "\nActual: " . $formData[$key];
            }
        }

        return $errorMessages;
    }

    /**
     * Text that form was filled correctly
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax Rate form was filled correctly.';
    }
}
