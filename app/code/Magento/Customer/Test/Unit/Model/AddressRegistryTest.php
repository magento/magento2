<?php
/**
 * Unit test for converter \Magento\Customer\Model\AddressRegistry
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

class AddressRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    private $unit;

    /**
     * @var \Magento\Customer\Model\AddressFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressFactory;

    protected function setUp()
    {
        $this->addressFactory = $this->getMockBuilder(\Magento\Customer\Model\AddressFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->unit = new \Magento\Customer\Model\AddressRegistry($this->addressFactory);
    }

    public function testRetrieve()
    {
        $addressId = 1;
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $address->expects($this->once())
            ->method('load')
            ->with($addressId)
            ->will($this->returnValue($address));
        $address->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($addressId));
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($address));
        $actual = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actual);
        $actualCached = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actualCached);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveException()
    {
        $addressId = 1;
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->setMethods(['load', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->once())
            ->method('load')
            ->with($addressId)
            ->will($this->returnValue($address));
        $address->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($address));
        $this->unit->retrieve($addressId);
    }

    public function testRemove()
    {
        $addressId = 1;
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $address->expects($this->exactly(2))
            ->method('load')
            ->with($addressId)
            ->will($this->returnValue($address));
        $address->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue($addressId));
        $this->addressFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($address));
        $actual = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actual);
        $this->unit->remove($addressId);
        $actual = $this->unit->retrieve($addressId);
        $this->assertEquals($address, $actual);
    }
}
