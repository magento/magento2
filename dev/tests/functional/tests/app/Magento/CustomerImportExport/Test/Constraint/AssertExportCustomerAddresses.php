<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\Constraint;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\File\Export;
use Magento\Mtf\Util\Command\File\Export\Data;

/**
 * Assert that exported file contains customer addresses data.
 */
class AssertExportCustomerAddresses extends AbstractConstraint
{
    /**
     * Assert that exported file contains customer addresses data.
     *
     * @param Export $export
     * @param array $exportedFields
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        Export $export,
        array $exportedFields,
        Customer $customer
    ) {
        $exportData = $export->getLatest();

        foreach ($customer->getDataFieldConfig('address')['source']->getAddresses() as $address) {
            \PHPUnit_Framework_Assert::assertTrue(
                $this->isAddressDataInFile(
                    $exportedFields,
                    $customer,
                    $address,
                    $exportData
                ),
                'Customer address was not found in exported file.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer address exists in exported file.';
    }

    /**
     * Get customer address data from exported file.
     *
     * @param array $fields
     * @param Customer $customer
     * @param Address $address
     * @param Data $exportData
     * @return bool
     */
    private function isAddressDataInFile(
        array $fields,
        Customer $customer,
        Address $address,
        Data $exportData
    ) {
        $expectedFields = [];
        $result = true;

        foreach ($fields as $field) {
            $fixture = ($field == 'email') ? $customer : $address;
            $expectedFields[] = $fixture->getData($field);
        }

        $content = $exportData->getContent();

        foreach ($expectedFields as $expectedField) {
            if (strpos($content, $expectedField) === false) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}
