<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class StatusTest
 */
class StatusTest extends \PHPUnit\Framework\TestCase
{
    public function testPrepareDataSource()
    {
        $itemName = 'itemName';
        $oldItemValue = 'oldItemValue';
        $newItemValue = 'newItemValue';
        $itemMapping = [$oldItemValue => $newItemValue];
        $dataSource = [
            'data' => [
                'items' => [
                    [$itemName => $oldItemValue]
                ]
            ]
        ];
        $collection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Status\Collection::class);
        $collection->expects($this->once())
            ->method('toOptionHash')
            ->willReturn($itemMapping);

        $collectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create']
        );
        $collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);
        $model = $objectManager->getObject(
            \Magento\Sales\Ui\Component\Listing\Column\Status::class,
            ['collectionFactory' => $collectionFactoryMock, 'context' => $contextMock]
        );
        $model->setData('name', $itemName);
        $dataSource = $model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
