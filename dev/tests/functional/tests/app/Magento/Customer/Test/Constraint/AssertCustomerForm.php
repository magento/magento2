<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert customer data on customer backend form.
 */
class AssertCustomerForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $customerSkippedFields = [
        'id',
        'password',
        'password_confirmation',
        'is_subscribed',
        'address'
    ];

    /**
     * Assert that displayed customer data on edit page(backend) equals passed from fixture.
     *
     * @param Customer $customer
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerIndexEdit $pageCustomerIndexEdit
     * @param Address $address[optional]
     * @param Customer $initialCustomer [optional]
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CustomerIndex $pageCustomerIndex,
        CustomerIndexEdit $pageCustomerIndexEdit,
        Address $address = null,
        Customer $initialCustomer = null
    ) {
        $data = [];
        $filter = [];

        if ($initialCustomer) {
            $data['customer'] = $customer->hasData()
                ? array_merge($initialCustomer->getData(), $customer->getData())
                : $initialCustomer->getData();
        } else {
            $data['customer'] = $customer->getData();
        }
        if ($address) {
            $data['addresses'][1] = $address->hasData() ? $address->getData() : [];
        } else {
            $data['addresses'] = [];
        }
        $filter['email'] = $data['customer']['email'];

        $pageCustomerIndex->open();
        $pageCustomerIndex->getCustomerGridBlock()->searchAndOpen($filter);

        $dataForm = $pageCustomerIndexEdit->getCustomerForm()->getDataCustomer($customer, $address);
        $dataDiff = $this->verify($data, $dataForm);
        \PHPUnit_Framework_Assert::assertTrue(
            empty($dataDiff),
            'Customer data on edit page(backend) not equals to passed from fixture.'
            . "\nFailed values: " . implode(', ', $dataDiff)
        );
    }

    /**
     * Verify data in form equals to passed from fixture.
     *
     * @param array $dataFixture
     * @param array $dataForm
     * @return array
     */
    protected function verify(array $dataFixture, array $dataForm)
    {
        $result = [];

        $customerDiff = array_diff_assoc($dataFixture['customer'], $dataForm['customer']);
        foreach ($customerDiff as $name => $value) {
            if (in_array($name, $this->customerSkippedFields)) {
                continue;
            }
            if (isset($dataForm['customer'][$name])) {
                $result[] = "\ncustomer {$name}: \"{$dataForm['customer'][$name]}\" instead of \"{$value}\"";
            } else {
                $result[] = "\ncustomer {$name}: Field is absent. Expected value \"{$value}\"";
            }
        }
        foreach ($dataFixture['addresses'] as $key => $address) {
            $addressDiff = array_diff($address, $dataForm['addresses'][$key]);
            foreach ($addressDiff as $name => $value) {
                if (isset($dataForm['addresses'][$key][$name])) {
                    $result[] = "\naddress #{$key} {$name}: \"{$dataForm['addresses'][$key][$name]}"
                        . "\" instead of \"{$value}\"";
                } else {
                    $result[] = "\naddress #{$key} {$name}: Field absent. Expected value \"{$value}\"";
                }
            }
        }

        return $result;
    }

    /**
     * Text success verify Customer form.
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed customer data on edit page(backend) equals to passed from fixture.';
    }
}
