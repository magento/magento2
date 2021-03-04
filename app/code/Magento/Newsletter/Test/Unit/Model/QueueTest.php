<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Queue
     */
    protected $queue;

    /**
     * @var \Magento\Newsletter\Model\Template\Filter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $templateFilter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $date;

    /**
     * @var \Magento\Newsletter\Model\TemplateFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $templateFactory;

    /**
     * @var \Magento\Newsletter\Model\ProblemFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $problemFactory;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subscribersCollection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subscribersCollectionFactory;

    /**
     * @var \Magento\Newsletter\Model\Queue\TransportBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Queue|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->templateFilter = $this->getMockBuilder(\Magento\Newsletter\Model\Template\Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->date = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFactory = $this->getMockBuilder(\Magento\Newsletter\Model\TemplateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load'])
            ->getMock();
        $this->problemFactory = $this->getMockBuilder(\Magento\Newsletter\Model\ProblemFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transportBuilder = $this->getMockBuilder(\Magento\Newsletter\Model\Queue\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setTemplateData', 'setTemplateOptions', 'setTemplateVars', 'setFrom', 'addTo', 'getTransport']
            )
            ->getMock();
        $this->subscribersCollection =
            $this->getMockBuilder(\Magento\Newsletter\Model\ResourceModel\Subscriber\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Newsletter\Model\ResourceModel\Queue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscribersCollectionFactory = $this->getMockBuilder(
            \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subscribersCollectionFactory->expects($this->any())->method('create')->willReturn(
            $this->subscribersCollection
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->queue = $this->objectManager->getObject(
            \Magento\Newsletter\Model\Queue::class,
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
        $item = $this->getMockBuilder(\Magento\Newsletter\Model\Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getSubscriberEmail', 'getSubscriberFullName', 'received'])
            ->getMock();
        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);
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
        $template = $this->getMockBuilder(\Magento\Newsletter\Model\Template::class)
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
