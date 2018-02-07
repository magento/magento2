<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert sales rule form.
 */
class AssertCartPriceRuleForm extends AbstractConstraint
{
    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = [
        'conditions_serialized',
        'actions_serialized',
        'from_date',
        'to_date',
        'rule_id'
    ];

    /**
     * Assert that displayed sales rule data on edit page(backend) equals passed from fixture.
     *
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteEdit $promoQuoteEdit
     * @param FixtureFactory $fixtureFactory
     * @param SalesRule $salesRule
     * @param SalesRule $salesRuleOrigin
     * @return void
     */
    public function processAssert(
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteEdit $promoQuoteEdit,
        FixtureFactory $fixtureFactory,
        SalesRule $salesRule,
        SalesRule $salesRuleOrigin = null
    ) {
        $filter = [
            'name' => $salesRule->hasData('name') ? $salesRule->getName() : $salesRuleOrigin->getName(),
        ];

        $promoQuoteIndex->open();
        $promoQuoteIndex->getPromoQuoteGrid()->searchAndOpen($filter);
        $fixtureData = $salesRuleOrigin != null
            ? array_merge($salesRuleOrigin->getData(), $salesRule->getData())
            : $salesRule->getData();
        $salesRuleMerged = $fixtureFactory->createByCode('salesRule', ['data' => $fixtureData]);
        $formData = $promoQuoteEdit->getSalesRuleForm()->getData($salesRuleMerged);
        $fixtureData = $salesRuleOrigin != null
            ? array_merge($salesRuleOrigin->getData(), $salesRule->getData())
            : $salesRule->getData();
        $dataDiff = $this->verify($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($dataDiff),
            'Sales rule data on edit page(backend) not equals to passed from fixture.'
            . "\nFailed values:\n " . implode(";\n ", $dataDiff)
        );
    }

    /**
     * Verify data in form equals to passed from fixture.
     *
     * @param array $fixtureData
     * @param array $formData
     * @return array
     */
    protected function verify(array $fixtureData, array $formData)
    {
        $errorMessage = [];

        foreach ($fixtureData as $key => $value) {
            if (is_array($value)) {
                $diff = array_diff($value, $formData[$key]);
                $diff = array_merge($diff, array_diff($formData[$key], $value));
                if (!empty($diff)) {
                    $errorMessage[] = "Data in " . $key . " field is not equal."
                        . "\nExpected: " . implode(", ", $value)
                        . "\nActual: " . implode(", ", $formData[$key]);
                }
            } else {
                if (!in_array($key, $this->skippedFields) && $value !== $formData[$key]) {
                    $errorMessage[] = "Data in " . $key . " field not equal."
                        . "\nExpected: " . $value
                        . "\nActual: " . $formData[$key];
                }
            }
        }

        return $errorMessage;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed sales rule data on edit page(backend) equals to passed from fixture.';
    }
}
