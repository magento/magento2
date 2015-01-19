<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroupInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerGroupForm
 */
class AssertCustomerGroupForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Skipped fields while verifying
     *
     * @var array
     */
    protected $skippedFields = [
        'customer_group_id',
    ];

    /**
     * Assert that customer group form equals to fixture data
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param CustomerGroupNew $customerGroupNew
     * @param CustomerGroupInjectable $customerGroup
     * @param CustomerGroupInjectable $customerGroupOriginal
     * @return void
     */
    public function processAssert(
        CustomerGroupIndex $customerGroupIndex,
        CustomerGroupNew $customerGroupNew,
        CustomerGroupInjectable $customerGroup,
        CustomerGroupInjectable $customerGroupOriginal = null
    ) {
        $data = ($customerGroupOriginal !== null)
            ? array_merge($customerGroupOriginal->getData(), $customerGroup->getData())
            : $customerGroup->getData();
        $filter = [
            'code' => $data['customer_group_code'],
        ];

        $customerGroupIndex->open();
        $customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
        $formData = $customerGroupNew->getPageMainForm()->getData();
        $dataDiff = $this->verifyForm($formData, $data);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($dataDiff),
            'Customer Group form was filled incorrectly.'
            . "\nLog:\n" . implode(";\n", $dataDiff)
        );
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

        foreach ($fixtureData as $key => $value) {
            if (in_array($key, $this->skippedFields)) {
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
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer Group form was filled correctly.';
    }
}
