<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Model\ProblemFactory;
use Magento\Newsletter\Model\Queue;
use Magento\Newsletter\Model\Queue\TransportBuilder;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\Template;
use Magento\Newsletter\Model\Template\Filter;
use Magento\Newsletter\Model\TemplateFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueueTest extends TestCase
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Filter|MockObject
     */
    protected $templateFilter;

    /**
     * @var DateTime|MockObject
     */
    protected $date;

    /**
     * @var TemplateFactory|MockObject
     */
    protected $templateFactory;

    /**
     * @var ProblemFactory|MockObject
     */
    protected $problemFactory;

    /**
     * @var Collection|MockObject
     */
    protected $subscribersCollection;

    /**
     * @var MockObject
     */
    protected $subscribersCollectionFactory;

    /**
     * @var TransportBuilder|MockObject
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Queue|MockObject
     */
    protected $resource;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->templateFilter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->date = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFactory = $this->getMockBuilder(TemplateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load'])
            ->getMock();
        $this->problemFactory = $this->getMockBuilder(ProblemFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transportBuilder = $this->getMockBuilder(TransportBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setTemplateData', 'setTemplateOptions', 'setTemplateVars', 'setFrom', 'addTo', 'getTransport']
            )
            ->getMock();
        $this->subscribersCollection =
            $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Newsletter\Model\ResourceModel\Queue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscribersCollectionFactory = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subscribersCollectionFactory->expects($this->any())->method('create')->willReturn(
            $this->subscribersCollection
        );

        $this->objectManager = new ObjectManager($this);

        $this->queue = $this->objectManager->getObject(
            Queue::class,
            [
                'templateFilter' => $this->templateFilter,
                'date' => $this->date,
                'templateFactory' => $this->templateFactory,
                'problemFactory' => $this->problemFactory,
                'subscriberCollectionFactory' => $this->subscribersCollectionFactory,
                'transportBuilder' => $this->transportBuilder,
                'resource' => $this->resource
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
        $this->subscribersCollection->expects($this->once())->method('getQueueJoinedFlag')->willReturn(false);
        $this->subscribersCollection->expects($this->once())->method('useQueue')->with($this->queue)->willReturnSelf();
        $this->subscribersCollection->expects($this->once())->method('getSize')->willReturn(0);
        $this->date->expects($this->once())->method('gmtDate')->willReturn('any_date');

        $this->assertEquals($this->queue, $this->queue->sendPerSubscriber());
    }

    public function testSendPerSubscriber2()
    {
        $this->queue->setQueueStatus(1);
        $this->queue->setQueueStartAt(1);
        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $item = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getSubscriberEmail', 'getSubscriberFullName', 'received'])
            ->getMock();
        $transport = $this->createMock(TransportInterface::class);
        $this->subscribersCollection->expects($this->once())->method('getQueueJoinedFlag')->willReturn(false);
        $this->subscribersCollection->expects($this->once())->method('useQueue')->with($this->queue)->willReturnSelf();
        $this->subscribersCollection->expects($this->once())->method('getSize')->willReturn(5);
        $this->subscribersCollection->expects($this->once())->method('useOnlyUnsent')->willReturnSelf();
        $this->subscribersCollection->expects($this->once())->method('showCustomerInfo')->willReturnSelf();
        $this->subscribersCollection->expects($this->once())->method('setPageSize')->willReturnSelf();
        $this->subscribersCollection->expects($this->once())->method('setCurPage')->willReturnSelf();
        $this->subscribersCollection->expects($this->once())->method('load')->willReturn($collection);
        $this->transportBuilder->expects($this->once())->method('setTemplateData')->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getItems')->willReturn([$item]);
        $item->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $item->expects($this->once())->method('getSubscriberEmail')->willReturn('email');
        $item->expects($this->once())->method('getSubscriberFullName')->willReturn('full_name');
        $this->transportBuilder->expects($this->once())->method('setTemplateOptions')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('setTemplateVars')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('setFrom')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('addTo')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('getTransport')->willReturn($transport);
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
        $this->templateFactory->expects($this->once())->method('create')->willReturn($template);
        $template->expects($this->once())->method('load')->with(2)->willReturnSelf();

        $this->assertEquals($template, $this->queue->getTemplate());
    }

    public function testGetStores()
    {
        $stores = ['store'];
        $this->resource->expects($this->once())->method('getStores')->willReturn($stores);

        $this->assertEquals($stores, $this->queue->getStores());
    }
}
