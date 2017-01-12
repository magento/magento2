<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

/**
 * Unit test for registry \Magento\Customer\Model\GroupRegistry
 */
class GroupRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\GroupRegistry
     */
    private $unit;

    /**
     * @var \Magento\Customer\Model\CustomerGroupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupFactory;

    protected function setUp()
    {
        $this->groupFactory = $this->getMockBuilder(\Magento\Customer\Model\GroupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->unit = new \Magento\Customer\Model\GroupRegistry($this->groupFactory);
    }

    /**
     * Tests that the same instance is returned from multiple retrieve calls with the same parameter.
     *
     * @return void
     */
    public function testRetrieve()
    {
        $groupId = 1;
        $group = $this->getMockBuilder(\Magento\Customer\Model\Group::class)
            ->setMethods(['load', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $group->expects($this->once())
            ->method('load')
            ->with($groupId)
            ->will($this->returnValue($group));
        $group->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue($groupId));
        $this->groupFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($group));
        $actual = $this->unit->retrieve($groupId);
        $this->assertEquals($group, $actual);
        $actualCached = $this->unit->retrieve($groupId);
        $this->assertSame($group, $actualCached);
    }

    /**
     * Tests that attempting to retrieve a non-existing entity will result in an exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveException()
    {
        $groupId = 1;
        $group = $this->getMockBuilder(\Magento\Customer\Model\Group::class)
            ->setMethods(['load', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $group->expects($this->once())
            ->method('load')
            ->with($groupId)
            ->will($this->returnValue($group));
        $group->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->groupFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($group));
        $this->unit->retrieve($groupId);
    }

    /**
     * Tests that an instance removed from the registry will cause the registry to load the model again.
     *
     * @return void
     */
    public function testRemove()
    {
        $groupId = 1;
        $group = $this->getMockBuilder(\Magento\Customer\Model\Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $group->expects($this->exactly(2))
            ->method('load')
            ->with($groupId)
            ->will($this->returnValue($group));
        $group->expects($this->exactly(4))
            ->method('getId')
            ->will($this->returnValue($groupId));
        $this->groupFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($group));
        $actual = $this->unit->retrieve($groupId);
        $this->assertSame($group, $actual);
        $this->unit->remove($groupId);
        $actual = $this->unit->retrieve($groupId);
        $this->assertSame($group, $actual);
    }
}
