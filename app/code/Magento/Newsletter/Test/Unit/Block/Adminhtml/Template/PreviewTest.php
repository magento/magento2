<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Block\Adminhtml\Template;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session;
use Magento\Email\Model\AbstractTemplate;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filter\Input\MaliciousCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Newsletter\Block\Adminhtml\Template\Preview;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Template;
use Magento\Newsletter\Model\TemplateFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @covers \Magento\Newsletter\Block\Adminhtml\Template\Preview
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreviewTest extends TestCase
{
    /**
     * Constant for test string
     */
    private const PROCESSED_TEMPLATE_TEXT = 'Processed Template';

    /** @var Preview */
    private $preview;

    /** @var ObjectManager */
    private $objectManagerHelper;

    /** @var Template|MockObject */
    private $templateMock;

    /** @var MaliciousCode|MockObject */
    protected $maliciousCode;

    /** @var SubscriberFactory|MockObject */
    private $subscriberFactoryMock;

    /** @var RequestInterface|MockObject */
    private $requestMock;

    /** @var State|MockObject */
    private $appStateMock;

    /** @var ManagerInterface|MockObject */
    private $eventManagerMock;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var Session|MockObject */
    protected $backendSessionMock;
    
    /** @var Escaper|MockObject */
    private $escaperMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->backendSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['hasPreviewData'])
            ->disableOriginalConstructor()
            ->getMock();
        
         $this->templateMock = $this->getMockBuilder(Template::class)
            ->addMethods(['setTemplateType', 'setTemplateText', 'setTemplateStyles'])
            ->onlyMethods(['isPlain', 'emulateDesign', 'revertDesign', 'getProcessedTemplate', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->method('escapeHtml')
            ->willReturnCallback(fn($string) => $string);

        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $design = $this->getMockForAbstractClass(DesignInterface::class);
        $appState = $this->getMockBuilder(State::class)
            ->setConstructorArgs([$scopeConfig])
            ->onlyMethods(['emulateAreaCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $appState->expects($this->any())
            ->method('emulateAreaCode')
            ->with(
                AbstractTemplate::DEFAULT_DESIGN_AREA,
                [$this->templateMock, 'getProcessedTemplate']
            )
            ->willReturn($this->templateMock->getProcessedTemplate());
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getEventManager', 'getScopeConfig', 'getDesignPackage',
                'getStoreManager', 'getAppState', 'getBackendSession', 'getEscaper']
        );
        $context->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getDesignPackage')->willReturn($design);
        $context->expects($this->any())->method('getStoreManager')->willReturn($this->storeManager);
        $context->expects($this->once())->method('getAppState')->willReturn($appState);
        $context->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
        $context->expects($this->once())->method('getBackendSession')->willReturn($this->backendSessionMock);

        $this->maliciousCode = $this->createPartialMock(MaliciousCode::class, ['filter']);
        $templateFactory = $this->createPartialMock(TemplateFactory::class, ['create']);
        $templateFactory->expects($this->once())->method('create')->willReturn($this->templateMock);

        $this->subscriberFactoryMock = $this->createPartialMock(SubscriberFactory::class, ['create']);
        $this->objectManagerHelper->prepareObjectManager();
        $this->preview = $this->objectManagerHelper->getObject(
            Preview::class,
            [
                'context' => $context,
                'templateFactory' => $templateFactory,
                'subscriberFactory' => $this->subscriberFactoryMock,
                'maliciousCode' => $this->maliciousCode,
            ]
        );
    }

    /**
     * Test for toHtml method
     */
    public function testToHtml()
    {
        $this->templateMock->expects($this->once())->method('revertDesign');
        $this->maliciousCode->expects($this->once())
            ->method('filter')
            ->willReturn(self::PROCESSED_TEMPLATE_TEXT);

        $this->assertEquals(self::PROCESSED_TEMPLATE_TEXT, $this->preview->toHtml());
    }

    /**
     * Test for toHtml method for new template
     */
    public function testToHtmlForNewTemplate()
    {
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['type', null, 1],
                ['text', null, self::PROCESSED_TEMPLATE_TEXT],
                ['styles', null, '.class-name{color:red;}'],
            ]
        );
        $this->templateMock->expects($this->once())->method('setTemplateType')->with(1)->willReturnSelf();
        $this->templateMock->expects($this->once())->method('setTemplateText')->with(self::PROCESSED_TEMPLATE_TEXT)
            ->willReturnSelf();
        $this->templateMock->expects($this->once())->method('setTemplateStyles')->with('.class-name{color:red;}')
            ->willReturnSelf();
        $this->templateMock->expects($this->once())->method('isPlain')->willReturn(true);
        $this->backendSessionMock->expects($this->any())->method('hasPreviewData')->willReturn(false);
        $this->maliciousCode->expects($this->once())
            ->method('filter')
            ->willReturn(self::PROCESSED_TEMPLATE_TEXT);
        $this->assertEquals('<pre>Processed Template</pre>', $this->preview->toHtml());
    }

    /**
     * Test for toHtml method with subscriber
     */
    public function testToHtmlWithSubscriber()
    {
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['id', null, 2],
                ['store', null, 1],
                ['subscriber', null, 3]
            ]
        );
        $subscriber = $this->createMock(Subscriber::class);
        $this->subscriberFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($subscriber);
        $subscriber->expects($this->exactly(2))
            ->method('getUnsubscriptionLink')
            ->willReturn('http://example.com/newsletter/subscriber/unsubscribe/');
        $this->templateMock->expects($this->atLeastOnce())->method('revertDesign');
        $this->appStateMock->expects($this->any())->method('emulateAreaCode')
            ->with(
                Template::DEFAULT_DESIGN_AREA,
                [
                    $this->templateMock,
                    'getProcessedTemplate'
                ],
                [
                    [
                        'subscriber' => $subscriber,
                        'subscriber_data' => [
                            'unsubscription_link' => $subscriber->getUnsubscriptionLink()
                        ]
                    ]
                ]
            )
            ->willReturn(self::PROCESSED_TEMPLATE_TEXT);
        $this->maliciousCode->expects($this->once())
            ->method('filter')
            ->willReturn(self::PROCESSED_TEMPLATE_TEXT);

        $this->assertEquals(self::PROCESSED_TEMPLATE_TEXT, $this->preview->toHtml());
    }
}
