<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Newsletter\Model\Observer;
use Magento\Newsletter\Model\ResourceModel\Queue\Collection;
use Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Newsletter\Model\Observer
 */
class ObserverTest extends TestCase
{
    /**
     * @var Observer
     */
    private $model;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->model = $objectManager->getObject(
            Observer::class,
            [
                'queueCollectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    /**
     * Test scheduledSend() method
     */
    public function testScheduledSend()
    {
        $collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('setPageSize')->with(3)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $collectionMock->expects($this->once())->method('addOnlyForSendingFilter')->willReturnSelf();
        $collectionMock->expects($this->once())->method('load')->willReturnSelf();
        $collectionMock->expects($this->once())->method('walk')->with('sendPerSubscriber', [20]);

        $this->model->scheduledSend();
    }
}
