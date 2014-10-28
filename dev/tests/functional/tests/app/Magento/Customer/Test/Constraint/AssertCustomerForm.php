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

namespace Magento\Customer\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Class AssertCustomerForm
 *
 */
class AssertCustomerForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Skipped fields for verify data
     *
     * @var array
     */
    protected $customerSkippedFields = [
        'id',
        'password',
        'password_confirmation',
        'is_subscribed',
    ];

    /**
     * Assert that displayed customer data on edit page(backend) equals passed from fixture
     *
     * @param CustomerInjectable $customer
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerIndexEdit $pageCustomerIndexEdit
     * @param AddressInjectable $address [optional]
     * @param CustomerInjectable $initialCustomer [optional]
     * @return void
     */
    public function processAssert(
        CustomerInjectable $customer,
        CustomerIndex $pageCustomerIndex,
        CustomerIndexEdit $pageCustomerIndexEdit,
        AddressInjectable $address = null,
        CustomerInjectable $initialCustomer = null
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
     * Verify data in form equals to passed from fixture
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
            $result[] = "\ncustomer {$name}: \"{$dataForm['customer'][$name]}\" instead of \"{$value}\"";
        }
        foreach ($dataFixture['addresses'] as $key => $address) {
            $addressDiff = array_diff($address, $dataForm['addresses'][$key]);
            foreach ($addressDiff as $name => $value) {
                $result[] = "\naddress #{$key} {$name}: \"{$dataForm['addresses'][$key][$name]}"
                . "\" instead of \"{$value}\"";
            }
        }

        return $result;
    }

    /**
     * Text success verify Customer form
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed customer data on edit page(backend) equals to passed from fixture.';
    }
}
