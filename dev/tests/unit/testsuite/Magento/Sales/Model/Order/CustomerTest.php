<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Class CustomerTest
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * Run test getDob method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetDob(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerDob'], $customer->getDob());
    }

    /**
     * Run test getEmail method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetEmail(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerEmail'], $customer->getEmail());
    }

    /**
     * Run test getFirstName method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetFirstName(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerFirstName'], $customer->getFirstName());
    }

    /**
     * Run test getGender method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetGender(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerGender'], $customer->getGender());
    }

    /**
     * Run test getGroupId method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetGroupId(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerGroupId'], $customer->getGroupId());
    }

    /**
     * Run test getId method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetId(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerId'], $customer->getId());
    }

    /**
     * Run test getIsGuest method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetIsGuest(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerIsGuest'], $customer->getIsGuest());
    }

    /**
     * Run test getLastName method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetLastName(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerLastName'], $customer->getLastName());
    }

    /**
     * Run test getMiddleName method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetMiddleName(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerMiddleName'], $customer->getMiddleName());
    }

    /**
     * Run test getNote method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetNote(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerNote'], $customer->getNote());
    }

    /**
     * Run test getNoteNotify method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetNoteNotify(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerNoteNotify'], $customer->getNoteNotify());
    }

    /**
     * Run test getPrefix method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetPrefix(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerPrefix'], $customer->getPrefix());
    }

    /**
     * Run test getSuffix method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetSuffix(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerSuffix'], $customer->getSuffix());
    }

    /**
     * Run test getTaxvat method
     *
     * @param array $parameters
     * @dataProvider providerCustomerData
     */
    public function testGetTaxvat(array $parameters)
    {
        /** @var \Magento\Sales\Model\Order\Customer $customer */
        $customer = $this->objectManager->getObject('Magento\Sales\Model\Order\Customer', $parameters);

        $this->assertEquals($parameters['customerTaxvat'], $customer->getTaxvat());
    }

    /**
     * Data to insert into constructor of the test object
     *
     * @return array
     */
    public function providerCustomerData()
    {
        return [
            [
                [
                    'customerDob' => 'customer_dob',
                    'customerEmail' => 'customer_email',
                    'customerFirstName' => 'customer_first_name',
                    'customerGender' => 'customer_gender',
                    'customerGroupId' => 'customer_group_id',
                    'customerId' => 'customer_id',
                    'customerIsGuest' => 'customer_is_guest',
                    'customerLastName' => 'customer_last_name',
                    'customerMiddleName' => 'customer_middle_name',
                    'customerNote' => 'customer_note',
                    'customerNoteNotify' => 'customer_note_notify',
                    'customerPrefix' => 'customer_prefix',
                    'customerSuffix' => 'customer_suffix',
                    'customerTaxvat' => 'customer_taxvat',
                ],
            ]
        ];
    }
}
