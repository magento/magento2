<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model;

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

    public function setUp()
    {
        $this->groupFactory = $this->getMockBuilder('\Magento\Customer\Model\GroupFactory')
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
        $group = $this->getMockBuilder('Magento\Customer\Model\Group')
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
        $group = $this->getMockBuilder('Magento\Customer\Model\Group')
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
        $group = $this->getMockBuilder('Magento\Customer\Model\Group')
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
