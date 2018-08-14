<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Customer;

class CustomerDataGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private $customerStructure = [
        'customer',
        'addresses',
    ];

    /**
     * @var array
     */
    private $config = [
        'addresses-count' => 10
    ];

    /**
     * @var \Magento\Setup\Model\Address\AddressDataGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressGeneratorMock;

    /**
     * @var \Magento\Setup\Model\Customer\CustomerDataGenerator
     */
    private $customerGenerator;

    public function setUp()
    {
        $this->groupCollectionFactoryMock =
            $this->createPartialMock(
                \Magento\Customer\Model\ResourceModel\Group\CollectionFactory::class,
                ['create', 'getAllIds']
            );

        $this->groupCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->groupCollectionFactoryMock);

        $this->groupCollectionFactoryMock
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);

        $this->addressGeneratorMock = $this->createMock(\Magento\Setup\Model\Address\AddressDataGenerator::class);

        $this->customerGenerator = new \Magento\Setup\Model\Customer\CustomerDataGenerator(
            $this->groupCollectionFactoryMock,
            $this->addressGeneratorMock,
            $this->config
        );
    }

    public function testEmail()
    {
        $customer = $this->customerGenerator->generate(42);

        $this->assertEquals('user_42@example.com', $customer['customer']['email']);
    }

    public function testAddressGeneration()
    {
        $this->addressGeneratorMock
            ->expects($this->exactly(10))
            ->method('generateAddress');

        $customer = $this->customerGenerator->generate(42);

        $this->assertCount($this->config['addresses-count'], $customer['addresses']);
    }

    public function testCustomerGroup()
    {
        $customer = $this->customerGenerator->generate(1);
        $this->assertEquals(1, $customer['customer']['group_id']);
    }

    public function testCustomerStructure()
    {
        $customer = $this->customerGenerator->generate(42);

        foreach ($this->customerStructure as $customerField) {
            $this->assertTrue(array_key_exists($customerField, $customer));
        }
    }
}
