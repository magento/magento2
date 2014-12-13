<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Page\Adminhtml\TaxRateIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRateNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRateForm
 */
class AssertTaxRateForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

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
        $data = ($initialTaxRate !== null)
            ? array_merge($initialTaxRate->getData(), $taxRate->getData())
            : $taxRate->getData();
        $data = $this->prepareData($data);
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
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data)
    {
        if ($data['zip_is_range'] === 'Yes') {
            unset($data['tax_postcode']);
        } else {
            unset($data['zip_from'], $data['zip_to']);
        }

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
