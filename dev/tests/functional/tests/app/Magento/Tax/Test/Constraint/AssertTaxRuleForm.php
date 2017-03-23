<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRuleForm
 */
class AssertTaxRuleForm extends AbstractConstraint
{
    /**
     * Assert that tax rule form filled right
     *
     * @param TaxRuleNew $taxRuleNew
     * @param TaxRuleIndex $taxRuleIndex
     * @param TaxRule $taxRule
     * @param TaxRule $initialTaxRule
     */
    public function processAssert(
        TaxRuleNew $taxRuleNew,
        TaxRuleIndex $taxRuleIndex,
        TaxRule $taxRule,
        TaxRule $initialTaxRule = null
    ) {
        $data = $taxRule->getData();
        if ($initialTaxRule !== null) {
            $taxRuleCode = ($taxRule->hasData('code')) ? $taxRule->getCode() : $initialTaxRule->getCode();
        } else {
            $taxRuleCode = $taxRule->getCode();
        }
        $filter = [
            'code' => $taxRuleCode,
        ];
        $taxRuleIndex->open();
        $taxRuleIndex->getTaxRuleGrid()->searchAndOpen($filter);
        $taxRuleNew->getTaxRuleForm()->openAdditionalSettings();
        $formData = $taxRuleNew->getTaxRuleForm()->getData($taxRule);
        $dataDiff = $this->verifyForm($formData, $data);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($dataDiff),
            'Tax Rule form was filled not right.'
            . "\nLog:\n" . implode(";\n", $dataDiff)
        );
    }

    /**
     * Verifying that form is filled right
     *
     * @param array $formData
     * @param array $fixtureData
     * @return array $errorMessage
     */
    protected function verifyForm(array $formData, array $fixtureData)
    {
        $errorMessage = [];

        foreach ($fixtureData as $key => $value) {
            if (is_array($value)) {
                $diff = array_diff($value, $formData[$key]);
                $diff = array_merge($diff, array_diff($formData[$key], $value));
                if (!empty($diff)) {
                    $errorMessage[] = "Data in " . $key . " field not equal."
                        . "\nExpected: " . implode(", ", $value)
                        . "\nActual: " . implode(", ", $formData[$key]);
                }
            } else {
                if ($value !== $formData[$key]) {
                    $errorMessage[] = "Data in " . $key . " field not equal."
                        . "\nExpected: " . $value
                        . "\nActual: " . $formData[$key];
                }
            }
        }

        return $errorMessage;
    }

    /**
     * Text that form was filled right
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax Rule form has been filled right.';
    }
}
