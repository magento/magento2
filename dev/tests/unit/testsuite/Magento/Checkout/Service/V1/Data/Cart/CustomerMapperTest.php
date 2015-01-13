<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

class CustomerMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Data\Cart\CustomerMapper
     */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new \Magento\Checkout\Service\V1\Data\Cart\CustomerMapper();
    }

    public function testMap()
    {
        $methods = ['getCustomerId', 'getCustomerEmail', 'getCustomerGroupId', 'getCustomerTaxClassId',
            'getCustomerPrefix', 'getCustomerFirstname', 'getCustomerMiddlename', 'getCustomerLastname',
            'getCustomerSuffix', 'getCustomerDob', 'getCustomerNote', 'getCustomerNoteNotify',
            'getCustomerIsGuest', 'getCustomerGender', 'getCustomerTaxvat', '__wakeUp', ];
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', $methods, [], '', false);
        $expected = [
            Customer::ID => 10,
            Customer::EMAIL => 'customer@example.com',
            Customer::GROUP_ID => '4',
            Customer::TAX_CLASS_ID => 10,
            Customer::PREFIX => 'prefix_',
            Customer::FIRST_NAME => 'First Name',
            Customer::MIDDLE_NAME => 'Middle Name',
            Customer::LAST_NAME => 'Last Name',
            Customer::SUFFIX => 'suffix',
            Customer::DOB => '1/1/1989',
            Customer::NOTE => 'customer_note',
            Customer::NOTE_NOTIFY => 'note_notify',
            Customer::IS_GUEST => false,
            Customer::GENDER => 'male',
            Customer::TAXVAT => 'taxvat',
        ];
        $expectedMethods = [
            'getCustomerId' => 10,
            'getCustomerEmail' => 'customer@example.com',
            'getCustomerGroupId' => 4,
            'getCustomerTaxClassId' => 10,
            'getCustomerPrefix' => 'prefix_',
            'getCustomerFirstname' => 'First Name',
            'getCustomerMiddlename' => 'Middle Name',
            'getCustomerLastname' => 'Last Name',
            'getCustomerSuffix' => 'suffix',
            'getCustomerDob' => '1/1/1989',
            'getCustomerNote' => 'customer_note',
            'getCustomerNoteNotify' => 'note_notify',
            'getCustomerIsGuest' => false,
            'getCustomerGender' => 'male',
            'getCustomerTaxvat' => 'taxvat',
        ];
        foreach ($expectedMethods as $method => $value) {
            $quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        $this->assertEquals($expected, $this->mapper->map($quoteMock));
    }
}
