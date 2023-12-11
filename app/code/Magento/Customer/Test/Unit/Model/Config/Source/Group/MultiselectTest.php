<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Config\Source\Group;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Config\Source\Group\Multiselect;
use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Framework\Convert\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultiselectTest extends TestCase
{
    /**
     * @var Multiselect
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

    /**
     * @var GroupSourceLoggedInOnlyInterface|MockObject
     */
    private $groupSourceLoggedInOnly;

    protected function setUp(): void
    {
        $this->groupServiceMock = $this->getMockForAbstractClass(GroupManagementInterface::class);
        $this->converterMock = $this->createMock(DataObject::class);
        $this->groupSourceLoggedInOnly = $this->getMockBuilder(GroupSourceLoggedInOnlyInterface::class)
            ->getMock();
        $this->model = new Multiselect(
            $this->groupServiceMock,
            $this->converterMock,
            $this->groupSourceLoggedInOnly
        );
    }

    public function testToOptionArray()
    {
        $expectedValue = ['General', 'Retail'];
        $this->groupServiceMock->expects($this->never())->method('getLoggedInGroups');
        $this->converterMock->expects($this->never())->method('toOptionArray');
        $this->groupSourceLoggedInOnly->expects($this->once())->method('toOptionArray')->willReturn($expectedValue);
        $this->assertEquals($expectedValue, $this->model->toOptionArray());
    }
}
