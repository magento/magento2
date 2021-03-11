<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Config\Source\Group;
use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupSourceLoggedInOnlyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $groupSource;

    /**
     * @var Group
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $groupServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $converterMock;

    protected function setUp(): void
    {
        $this->groupServiceMock = $this->getMockForAbstractClass(GroupManagementInterface::class);
        $this->converterMock = $this->createMock(DataObject::class);
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
