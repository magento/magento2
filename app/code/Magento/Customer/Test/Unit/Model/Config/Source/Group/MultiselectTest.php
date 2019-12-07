<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Config\Source\Group;

use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;

class MultiselectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Config\Source\Group\Multiselect
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

    /**
     * @var GroupSourceLoggedInOnlyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupSourceLoggedInOnly;

    protected function setUp()
    {
        $this->groupServiceMock = $this->createMock(\Magento\Customer\Api\GroupManagementInterface::class);
        $this->converterMock = $this->createMock(\Magento\Framework\Convert\DataObject::class);
        $this->groupSourceLoggedInOnly = $this->getMockBuilder(GroupSourceLoggedInOnlyInterface::class)->getMock();
        $this->model = new \Magento\Customer\Model\Config\Source\Group\Multiselect(
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
