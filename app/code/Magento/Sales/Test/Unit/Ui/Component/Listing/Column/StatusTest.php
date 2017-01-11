<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Ui\Component\Listing\Column\Status;

/**
 * Class StatusTest
 */
class StatusTest extends \PHPUnit_Framework_TestCase
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
        $collection = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\Collection::class,
            [],
            [],
            '',
            false
        );
        $collection->expects($this->once())
            ->method('toOptionHash')
            ->willReturn($itemMapping);

        $collectionFactoryMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
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
        $contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
        $model = $objectManager->getObject(
            \Magento\Sales\Ui\Component\Listing\Column\Status::class,
            ['collectionFactory' => $collectionFactoryMock, 'context' => $contextMock]
        );
        $model->setData('name', $itemName);
        $dataSource = $model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
