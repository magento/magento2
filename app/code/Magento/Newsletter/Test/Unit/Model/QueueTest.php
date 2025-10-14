<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\LocalizedDateToUtcConverterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Model\ProblemFactory;
use Magento\Newsletter\Model\Queue;
use Magento\Newsletter\Model\Queue\TransportBuilder;
use Magento\Newsletter\Model\ResourceModel\Queue as QueueResourseModel;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\Template;
use Magento\Newsletter\Model\Template\Filter;
use Magento\Newsletter\Model\TemplateFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Newsletter\Model\Queue
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueueTest extends TestCase
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Filter|MockObject
     */
    private $templateFilterMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateMock;

    /**
     * @var TemplateFactory|MockObject
     */
    private $templateFactoryMock;

    /**
     * @var ProblemFactory|MockObject
     */
    private $problemFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private $subscribersCollectionMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $subscribersCollectionFactoryMock;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var QueueResourseModel|MockObject
     */
    private $queueResourseModelMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->templateFilterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->addMethods(['create'])
            ->getMock();
        $this->dateMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFactoryMock = $this->getMockBuilder(TemplateFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['load'])
            ->onlyMethods(['create'])
            ->getMock();
        $this->problemFactoryMock = $this->getMockBuilder(ProblemFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transportBuilderMock = $this->getMockBuilder(TransportBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                ['setTemplateData', 'setTemplateOptions', 'setTemplateVars', 'setFrom', 'addTo', 'getTransport']
            )
            ->getMock();
        $this->subscribersCollectionMock =
            $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->queueResourseModelMock = $this->getMockBuilder(QueueResourseModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscribersCollectionFactoryMock = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->subscribersCollectionFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->subscribersCollectionMock
        );

        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                TimezoneInterface::class,
                $this->createMock(TimezoneInterface::class)
            ],
            [
                LocalizedDateToUtcConverterInterface::class,
                $this->createMock(LocalizedDateToUtcConverterInterface::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);
        $this->queue = $this->objectManager->getObject(
            Queue::class,
            [
                'templateFilter' => $this->templateFilterMock,
                'date' => $this->dateMock,
                'templateFactory' => $this->templateFactoryMock,
                'problemFactory' => $this->problemFactoryMock,
                'subscriberCollectionFactory' => $this->subscribersCollectionFactoryMock,
                'transportBuilder' => $this->transportBuilderMock,
                'resource' => $this->queueResourseModelMock
            ]
        );
    }

    public function testSendPerSubscriber1()
    {
        $this->queue->setQueueStatus(2);
        $this->queue->setQueueStartAt(1);

        $this->assertEquals($this->queue, $this->queue->sendPerSubscriber());
    }

    public function testSendPerSubscriberZeroSize()
    {
        $this->queue->setQueueStatus(1);
        $this->queue->setQueueStartAt(1);
        $this->subscribersCollectionMock->expects($this->once())->method('getQueueJoinedFlag')->willReturn(false);
        $this->subscribersCollectionMock->expects($this->once())
            ->method('useQueue')
            ->with($this->queue)
            ->willReturnSelf();
        $this->subscribersCollectionMock->expects($this->once())->method('getSize')->willReturn(0);
        $this->dateMock->expects($this->once())->method('gmtDate')->willReturn('any_date');

        $this->assertEquals($this->queue, $this->queue->sendPerSubscriber());
    }

    public function testSendPerSubscriber2()
    {
        $this->queue->setQueueStatus(1);
        $this->queue->setQueueStartAt(1);
        $collection = $this->getMockBuilder(DataCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMock();
        $item = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStoreId', 'getSubscriberEmail'])
            ->onlyMethods(
                ['getSubscriberFullName', 'received', 'getUnsubscriptionLink']
            )
            ->getMock();
        $transport = $this->getMockForAbstractClass(TransportInterface::class);
        $this->subscribersCollectionMock->expects($this->once())->method('getQueueJoinedFlag')->willReturn(false);
        $this->subscribersCollectionMock->expects($this->once())
            ->method('useQueue')
            ->with($this->queue)
            ->willReturnSelf();
        $this->subscribersCollectionMock->expects($this->once())->method('getSize')->willReturn(5);
        $this->subscribersCollectionMock->expects($this->once())->method('useOnlyUnsent')->willReturnSelf();
        $this->subscribersCollectionMock->expects($this->once())->method('showCustomerInfo')->willReturnSelf();
        $this->subscribersCollectionMock->expects($this->once())->method('setPageSize')->willReturnSelf();
        $this->subscribersCollectionMock->expects($this->once())->method('setCurPage')->willReturnSelf();
        $this->subscribersCollectionMock->expects($this->once())->method('load')->willReturn($collection);
        $this->transportBuilderMock->expects($this->once())->method('setTemplateData')->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getItems')->willReturn([$item]);
        $item->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $item->expects($this->once())->method('getSubscriberEmail')->willReturn('email');
        $item->expects($this->once())->method('getSubscriberFullName')->willReturn('full_name');
        $item->expects($this->once())
            ->method('getUnsubscriptionLink')
            ->willReturn('http://example.com/newsletter/subscriber/unsubscribe/');
        $this->transportBuilderMock->expects($this->once())->method('setTemplateOptions')->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('setTemplateVars')->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('setFrom')->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('addTo')->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('getTransport')->willReturn($transport);
        $item->expects($this->once())->method('received')->with($this->queue)->willReturnSelf();

        $this->assertEquals($this->queue, $this->queue->sendPerSubscriber());
    }

    public function testGetDataForSave()
    {
        $result = [
            'template_id' => 'id',
            'queue_status' => 'status',
            'queue_start_at' => 'start_at',
            'queue_finish_at' => 'finish_at'
        ];
        $this->queue->setTemplateId('id');
        $this->queue->setQueueStatus('status');
        $this->queue->setQueueStartAt('start_at');
        $this->queue->setQueueFinishAt('finish_at');

        $this->assertEquals($result, $this->queue->getDataForSave());
    }

    public function testGetTemplate()
    {
        $template = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queue->setTemplateId(2);
        $this->templateFactoryMock->expects($this->once())->method('create')->willReturn($template);
        $template->expects($this->once())->method('load')->with(2)->willReturnSelf();

        $this->assertEquals($template, $this->queue->getTemplate());
    }

    public function testGetStores()
    {
        $stores = ['store'];
        $this->queueResourseModelMock->expects($this->once())->method('getStores')->willReturn($stores);

        $this->assertEquals($stores, $this->queue->getStores());
    }
}
