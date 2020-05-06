<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Customer;

use Magento\Setup\Model\Address\AddressDataGenerator;
use Magento\Setup\Model\Customer\CustomerDataGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

class CustomerDataGeneratorTest extends TestCase
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
     * @var AddressDataGenerator|MockObject
     */
    private $addressGeneratorMock;

    /**
     * @var CustomerDataGenerator
     */
    private $customerGenerator;

    /**
     * @var CollectionFactory|MockObject
     */
    private $groupCollectionFactoryMock;

    protected function setUp(): void
    {
        $this->groupCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(
                ['getAllIds']
            )
            ->onlyMethods(['create'])
            ->getMock();

        $this->groupCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->groupCollectionFactoryMock);

        $this->groupCollectionFactoryMock
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);

        $this->addressGeneratorMock = $this->createMock(AddressDataGenerator::class);

        $this->customerGenerator = new CustomerDataGenerator(
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
            $this->assertArrayHasKey($customerField, $customer);
        }
    }
}
