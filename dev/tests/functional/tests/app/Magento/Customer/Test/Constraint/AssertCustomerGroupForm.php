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

use Magento\Customer\Test\Fixture\CustomerGroupInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerGroupForm
 */
class AssertCustomerGroupForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
