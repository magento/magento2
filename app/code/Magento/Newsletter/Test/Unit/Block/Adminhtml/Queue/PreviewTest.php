<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Block\Adminhtml\Queue;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Newsletter\Model\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $template;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriber;

    /**
     * @var \Magento\Newsletter\Model\Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queue;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Newsletter\Block\Adminhtml\Queue\Preview
     */
    protected $preview;

    protected function setUp()
    {
        $context = $this->createMock(\Magento\Backend\Block\Template\Context::class);
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $context->expects($this->once())->method('getEventManager')->will($this->returnValue($eventManager));
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $context->expects($this->once())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->request));
        $this->storeManager = $this->createPartialMock(
            \Magento\Store\Model\StoreManager::class,
            ['getStores', 'getDefaultStoreView']
        );
        $context->expects($this->once())->method('getStoreManager')->will($this->returnValue($this->storeManager));
        $appState = $this->createMock(\Magento\Framework\App\State::class);
        $context->expects($this->once())->method('getAppState')->will($this->returnValue($appState));

        $backendSession = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())->method('getBackendSession')->willReturn($backendSession);

        $templateFactory = $this->createPartialMock(\Magento\Newsletter\Model\TemplateFactory::class, ['create']);
        $this->template = $this->createMock(\Magento\Newsletter\Model\Template::class);
        $templateFactory->expects($this->once())->method('create')->will($this->returnValue($this->template));

        $subscriberFactory = $this->createPartialMock(\Magento\Newsletter\Model\SubscriberFactory::class, ['create']);
        $this->subscriber = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $subscriberFactory->expects($this->once())->method('create')->will($this->returnValue($this->subscriber));

        $queueFactory = $this->createPartialMock(\Magento\Newsletter\Model\QueueFactory::class, ['create']);
        $this->queue = $this->createPartialMock(\Magento\Newsletter\Model\Queue::class, ['load']);
        $queueFactory->expects($this->any())->method('create')->will($this->returnValue($this->queue));

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->preview = $this->objectManager->getObject(
            \Magento\Newsletter\Block\Adminhtml\Queue\Preview::class,
            [
                'context' => $context,
                'templateFactory' => $templateFactory,
                'subscriberFactory' => $subscriberFactory,
                'queueFactory' => $queueFactory,
            ]
        );
    }

    public function testToHtmlEmpty()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId']);
        $this->storeManager->expects($this->once())->method('getDefaultStoreView')->will($this->returnValue($store));
        $result = $this->preview->toHtml();
        $this->assertEquals('', $result);
    }

    public function testToHtmlWithId()
    {
        $this->request->expects($this->any())->method('getParam')->will($this->returnValueMap(
            [
                ['id', null, 1],
                ['store_id', null, 0]
            ]
        ));
        $this->queue->expects($this->once())->method('load')->will($this->returnSelf());
        $this->template->expects($this->any())->method('isPlain')->will($this->returnValue(true));
        /** @var \Magento\Store\Model\Store $store */
        $this->storeManager->expects($this->once())->method('getDefaultStoreView')->will($this->returnValue(null));
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId']);
        $this->storeManager->expects($this->once())->method('getStores')->will($this->returnValue([0 => $store]));
        $result = $this->preview->toHtml();
        $this->assertEquals('<pre></pre>', $result);
    }
}
