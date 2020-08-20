<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address\Validator;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Address\Validator\Customer as CustomerValidator;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Customer\Model\Address\Validator\Customer class.
 */
class CustomerTest extends TestCase
{
    /** @var AddressFactory|MockObject  */
    private $addressFactoryMock;

    /** @var CustomerValidator  */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->addressFactoryMock = $this->createMock(AddressFactory::class);
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            CustomerValidator::class,
            [
                'addressFactory' => $this->addressFactoryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testValidateNewCustomerWithNewCustomerAddress(): void
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->expects($this->once())->method('getId')->willReturn(null);
        $addressMock->expects($this->never())->method('getParentId');

        $this->assertEmpty($this->model->validate($addressMock));
    }

    /**
     * @return void
     */
    public function testValidateNewCustomerWithExistingCustomerAddress(): void
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $originalAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->expects($this->exactly(2))->method('getId')->willReturn(1);
        $addressMock->expects($this->once())->method('getParentId')->willReturn(null);
        $this->addressFactoryMock->expects($this->once())->method('create')->willReturn($originalAddressMock);
        $originalAddressMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($originalAddressMock);

        $this->assertEmpty($this->model->validate($addressMock));
    }

    /**
     * @return void
     */
    public function testValidateExistingCustomerWithNewCustomerAddress(): void
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->expects($this->once())->method('getId')->willReturn(null);
        $addressMock->expects($this->never())->method('getParentId');

        $this->assertEmpty($this->model->validate($addressMock));
    }

    /**
     * @return void
     */
    public function testValidateExistingCustomerWithRelevantCustomerAddress(): void
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $originalAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->expects($this->exactly(2))->method('getId')->willReturn(1);
        $this->addressFactoryMock->expects($this->once())->method('create')->willReturn($originalAddressMock);
        $originalAddressMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($originalAddressMock);

        $addressMock->expects($this->once())->method('getParentId')->willReturn(1);
        $originalAddressMock->expects($this->once())->method('getParentId')->willReturn(1);

        $this->assertEmpty($this->model->validate($addressMock));
    }

    /**
     * @return void
     */
    public function testValidateExistingCustomerAddressWithNotRelevantCustomer(): void
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $originalAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getParentId'])
            ->onlyMethods(['getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->expects($this->exactly(2))->method('getId')->willReturn(1);
        $this->addressFactoryMock->expects($this->once())->method('create')->willReturn($originalAddressMock);
        $originalAddressMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($originalAddressMock);

        $addressMock->expects($this->once())->method('getParentId')->willReturn(2);
        $originalAddressMock->expects($this->once())->method('getParentId')->willReturn(1);

        $this->assertEquals(
            [
                __(
                    'Provided customer ID "%customer_id" isn\'t related to current customer address.',
                    ['customer_id' => 2]
                )
            ],
            $this->model->validate($addressMock)
        );
    }
}
