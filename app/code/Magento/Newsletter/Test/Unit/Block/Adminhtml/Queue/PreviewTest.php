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
     * @var \Magento\Newsletter\Model\Template|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $template;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subscriber;

    /**
     * @var \Magento\Newsletter\Model\Queue|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $queue;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Newsletter\Block\Adminhtml\Queue\Preview
     */
    protected $preview;

    protected function setUp(): void
    {
        $context = $this->createMock(\Magento\Backend\Block\Template\Context::class);
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $context->expects($this->once())->method('getEventManager')->willReturn($eventManager);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $context->expects($this->once())->method('getScopeConfig')->willReturn($scopeConfig);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->storeManager = $this->createPartialMock(
            \Magento\Store\Model\StoreManager::class,
            ['getStores', 'getDefaultStoreView']
        );
        $context->expects($this->once())->method('getStoreManager')->willReturn($this->storeManager);
        $appState = $this->createMock(\Magento\Framework\App\State::class);
        $context->expects($this->once())->method('getAppState')->willReturn($appState);

        $backendSession = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())->method('getBackendSession')->willReturn($backendSession);

        $templateFactory = $this->createPartialMock(\Magento\Newsletter\Model\TemplateFactory::class, ['create']);
        $this->template = $this->createPartialMock(
            \Magento\Newsletter\Model\Template::class,
            [
                'isPlain',
                'setId',
                'setTemplateType',
                'setTemplateText',
                'setTemplateStyles',
            ]
        );
        $templateFactory->expects($this->once())->method('create')->willReturn($this->template);

        $subscriberFactory = $this->createPartialMock(\Magento\Newsletter\Model\SubscriberFactory::class, ['create']);
        $this->subscriber = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $subscriberFactory->expects($this->once())->method('create')->willReturn($this->subscriber);

        $queueFactory = $this->createPartialMock(\Magento\Newsletter\Model\QueueFactory::class, ['create']);
        $this->queue = $this->createPartialMock(
            \Magento\Newsletter\Model\Queue::class,
            [
                'load',
                'getTemplateId',
                'getNewsletterType',
                'getNewsletterText',
                'getNewsletterStyles',
            ]
        );
        $queueFactory->expects($this->any())->method('create')->willReturn($this->queue);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $escaper = $this->objectManager->getObject(\Magento\Framework\Escaper::class);
        $context->expects($this->once())->method('getEscaper')->willReturn($escaper);

        $this->preview = $this->objectManager->getObject(
            \Magento\Newsletter\Block\Adminhtml\Queue\Preview::class,
            [
                'context' => $context,
                'templateFactory' => $templateFactory,
                'subscriberFactory' => $subscriberFactory,
                'queueFactory' => $queueFactory
            ]
        );
    }

    public function testToHtmlEmpty()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId']);
        $this->storeManager->expects($this->once())->method('getDefaultStoreView')->willReturn($store);
        $result = $this->preview->toHtml();
        $this->assertEquals('', $result);
    }

    public function testToHtmlWithId()
    {
        $templateId = 1;
        $newsletterType = 2;
        $newsletterText = 'newsletter text';
        $newsletterStyle = 'style';
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['id', null, 1],
                ['store_id', null, 0],
            ]
        );
        $this->queue->expects($this->once())->method('load')->willReturnSelf();
        $this->queue->expects($this->once())->method('getTemplateId')->willReturn($templateId);
        $this->queue->expects($this->once())->method('getNewsletterType')->willReturn($newsletterType);
        $this->queue->expects($this->once())->method('getNewsletterText')->willReturn($newsletterText);
        $this->queue->expects($this->once())->method('getNewsletterStyles')->willReturn($newsletterStyle);
        $this->template->expects($this->any())->method('isPlain')->willReturn(true);
        $this->template->expects($this->once())->method('setId')->willReturn($templateId);
        $this->template->expects($this->once())->method('setTemplateType')->willReturn($newsletterType);
        $this->template->expects($this->once())->method('setTemplateText')->willReturn($newsletterText);
        $this->template->expects($this->once())->method('setTemplateStyles')->willReturn($newsletterStyle);
        /** @var \Magento\Store\Model\Store $store */
        $this->storeManager->expects($this->once())->method('getDefaultStoreView')->willReturn(null);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId']);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn([0 => $store]);
        $result = $this->preview->toHtml();
        $this->assertEquals('<pre></pre>', $result);
    }
}
