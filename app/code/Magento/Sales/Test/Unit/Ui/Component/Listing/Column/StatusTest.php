<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $collection = $this->getMock('Magento\Sales\Model\ResourceModel\Order\Status\Collection', [], [], '', false);
        $collection->expects($this->once())
            ->method('toOptionHash')
            ->willReturn($itemMapping);

        $collectionFactoryMock = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
        $model = $objectManager->getObject(
            'Magento\Sales\Ui\Component\Listing\Column\Status',
            ['collectionFactory' => $collectionFactoryMock, 'context' => $contextMock]
        );
        $model->setData('name', $itemName);
        $dataSource = $model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
