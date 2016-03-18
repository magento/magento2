<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Queue
     */
    protected $queue;

    /**
     * @var \Magento\Newsletter\Model\Template\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateFilter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $date;

    /**
     * @var \Magento\Newsletter\Model\TemplateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateFactory;

    /**
     * @var \Magento\Newsletter\Model\ProblemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $problemFactory;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscribersCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscribersCollectionFactory;

    /**
     * @var \Magento\Newsletter\Model\Queue\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->templateFilter = $this->getMockBuilder('\Magento\Newsletter\Model\Template\Filter')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->date = $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFactory = $this->getMockBuilder('\Magento\Newsletter\Model\TemplateFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load'])
            ->getMock();
        $this->problemFactory = $this->getMockBuilder('\Magento\Newsletter\Model\ProblemFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transportBuilder = $this->getMockBuilder('\Magento\Newsletter\Model\Queue\TransportBuilder')
            ->disableOriginalConstructor()
            ->setMethods(
                ['setTemplateData', 'setTemplateOptions', 'setTemplateVars', 'setFrom', 'addTo', 'getTransport']
            )
            ->getMock();
        $this->subscribersCollection =
            $this->getMockBuilder('\Magento\Newsletter\Model\ResourceModel\Subscriber\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder('\Magento\Newsletter\Model\ResourceModel\Queue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscribersCollectionFactory = $this->getMockBuilder(
            '\Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subscribersCollectionFactory->expects($this->any())->method('create')->willReturn(
            $this->subscribersCollection
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->queue = $this->objectManager->getObject(
            '\Magento\Newsletter\Model\Queue',
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
        $collection = $this->getMockBuilder('\Magento\Framework\Data\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $item = $this->getMockBuilder('\Magento\Newsletter\Model\Subscriber')
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getSubscriberEmail', 'getSubscriberFullName', 'received'])
            ->getMock();
        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface');
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
        $template = $this->getMockBuilder('\Magento\Newsletter\Model\Template')
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
