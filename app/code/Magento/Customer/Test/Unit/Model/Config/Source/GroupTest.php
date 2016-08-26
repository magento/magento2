<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Config\Source\Group;
use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GroupSourceLoggedInOnlyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupSource;

    /**
     * @var Group
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    protected function setUp()
    {
        $this->groupServiceMock = $this->getMock(GroupManagementInterface::class);
        $this->converterMock = $this->getMock(DataObject::class, [], [], '', false);
        $this->groupSource = $this->getMockBuilder(GroupSourceLoggedInOnlyInterface::class)
            ->getMockForAbstractClass();
        $this->model = (new ObjectManager($this))->getObject(
            Group::class,
            [
                'groupManagement' => $this->groupServiceMock,
                'converter' => $this->converterMock,
                'groupSourceForLoggedInCustomers' => $this->groupSource,
            ]
        );
    }

    public function testToOptionArray()
    {
        $expectedValue = ['General', 'Retail'];
        $this->groupServiceMock->expects($this->never())->method('getLoggedInGroups');
        $this->converterMock->expects($this->never())->method('toOptionArray');

        $this->groupSource->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($expectedValue);

        array_unshift($expectedValue, ['value' => '', 'label' => __('-- Please Select --')]);
        $this->assertEquals($expectedValue, $this->model->toOptionArray());
    }
}
