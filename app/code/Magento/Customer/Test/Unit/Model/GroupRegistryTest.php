<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\GroupRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for registry \Magento\Customer\Model\GroupRegistry
 */
class GroupRegistryTest extends TestCase
{
    /**
     * @var GroupRegistry
     */
    private $unit;

    /**
     * @var \Magento\Customer\Model\CustomerGroupFactory|MockObject
     */
    private $groupFactory;

    protected function setUp(): void
    {
        $this->groupFactory = $this->getMockBuilder(GroupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->unit = new GroupRegistry($this->groupFactory);
    }

    /**
     * Tests that the same instance is returned from multiple retrieve calls with the same parameter.
     *
     * @return void
     */
    public function testRetrieve()
    {
        $groupId = 1;
        $group = $this->getMockBuilder(Group::class)
            ->setMethods(['load', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $group->expects($this->once())
            ->method('load')
            ->with($groupId)
            ->willReturn($group);
        $group->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($groupId);
        $this->groupFactory->expects($this->once())
            ->method('create')
            ->willReturn($group);
        $actual = $this->unit->retrieve($groupId);
        $this->assertEquals($group, $actual);
        $actualCached = $this->unit->retrieve($groupId);
        $this->assertSame($group, $actualCached);
    }

    /**
     * Tests that attempting to retrieve a non-existing entity will result in an exception.
     *
     * @return void
     */
    public function testRetrieveException()
    {
        $this->expectException(NoSuchEntityException::class);

        $groupId = 1;
        $group = $this->getMockBuilder(Group::class)
            ->setMethods(['load', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $group->expects($this->once())
            ->method('load')
            ->with($groupId)
            ->willReturn($group);
        $group->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->groupFactory->expects($this->once())
            ->method('create')
            ->willReturn($group);
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
        $group = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $group->expects($this->exactly(2))
            ->method('load')
            ->with($groupId)
            ->willReturn($group);
        $group->expects($this->exactly(4))
            ->method('getId')
            ->willReturn($groupId);
        $this->groupFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($group);
        $actual = $this->unit->retrieve($groupId);
        $this->assertSame($group, $actual);
        $this->unit->remove($groupId);
        $actual = $this->unit->retrieve($groupId);
        $this->assertSame($group, $actual);
    }
}
