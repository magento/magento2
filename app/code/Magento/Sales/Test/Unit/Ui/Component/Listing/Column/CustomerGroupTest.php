<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Sales\Ui\Component\Listing\Column\CustomerGroup;

/**
 * Class CustomerGroupTest
 */
class CustomerGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerGroup
     */
    protected $model;

    /**
     * @var GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepository;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
        $this->groupRepository = $this->getMockForAbstractClass(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $this->model = $objectManager->getObject(
            \Magento\Sales\Ui\Component\Listing\Column\CustomerGroup::class,
            ['groupRepository' => $this->groupRepository, 'context' => $contextMock]
        );
    }

    public function testPrepareDataSource()
    {
        $itemName = 'itemName';
        $oldItemValue = 'oldItemValue';
        $newItemValue = 'newItemValue';
        $dataSource = [
            'data' => [
                'items' => [
                    [$itemName => $oldItemValue]
                ]
            ]
        ];

        $group = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\GroupInterface::class);
        $group->expects($this->once())
            ->method('getCode')
            ->willReturn($newItemValue);
        $this->groupRepository->expects($this->once())
            ->method('getById')
            ->with($oldItemValue)
            ->willReturn($group);

        $this->model->setData('name', $itemName);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
