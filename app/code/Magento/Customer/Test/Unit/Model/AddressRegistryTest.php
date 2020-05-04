<?php declare(strict_types=1);
/**
 * Unit test for converter \Magento\Customer\Model\AddressRegistry
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\AddressRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressRegistryTest extends TestCase
{
    /**
     * @var AddressRegistry
     */
    private $unit;

    /**
     * @var AddressFactory|MockObject
     */
    private $addressFactory;

    protected function setUp(): void
    {
        $this->addressFactory = $this->getMockBuilder(AddressFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->unit = new AddressRegistry($this->addressFactory);
    }

    public function testRetrieve()
    {
        $addressId = 1;
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $address->expects($this->once())
            ->method('load')
            ->with($addressId)
            ->willReturn($address);
        $address->expects($this->once())
            ->method('getId')
            ->willReturn($addressId);
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->willReturn($address);
        $actual = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actual);
        $actualCached = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actualCached);
    }

    public function testRetrieveException()
    {
        $this->expectException(NoSuchEntityException::class);

        $addressId = 1;
        $address = $this->getMockBuilder(Address::class)
            ->setMethods(['load', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->once())
            ->method('load')
            ->with($addressId)
            ->willReturn($address);
        $address->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->willReturn($address);
        $this->unit->retrieve($addressId);
    }

    public function testRemove()
    {
        $addressId = 1;
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $address->expects($this->exactly(2))
            ->method('load')
            ->with($addressId)
            ->willReturn($address);
        $address->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($addressId);
        $this->addressFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($address);
        $actual = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actual);
        $this->unit->remove($addressId);
        $actual = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actual);
    }
}
