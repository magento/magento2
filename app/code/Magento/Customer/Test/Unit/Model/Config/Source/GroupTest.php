<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Config\Source\Group;
use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var GroupSourceLoggedInOnlyInterface|MockObject
     */
    private $groupSource;

    /**
     * @var Group
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $groupServiceMock;

    /**
     * @var MockObject
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
