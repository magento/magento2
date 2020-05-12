<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Block\Adminhtml\Template;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Newsletter\Block\Adminhtml\Template\Preview;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Template;
use Magento\Newsletter\Model\TemplateFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Newsletter\Block\Adminhtml\Template\Preview
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreviewTest extends TestCase
{
    /** @var Preview */
    private $preview;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var Template|MockObject */
    private $templateMock;

    /** @var SubscriberFactory|MockObject */
    private $subscriberFactoryMock;

    /** @var State|MockObject */
    private $appStateMock;

    /** @var StoreManagerInterface|MockObject */
    private $storeManagerMock;

    /** @var RequestInterface|MockObject */
    private $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->templateMock = $this->getMockBuilder(Template::class)
            ->addMethods(['setTemplateType', 'setTemplateText', 'setTemplateStyles'])
            ->onlyMethods(['isPlain', 'emulateDesign', 'revertDesign', 'getProcessedTemplate', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $templateFactory = $this->createPartialMock(TemplateFactory::class, ['create']);
        $templateFactory->expects($this->once())->method('create')->willReturn($this->templateMock);
        $this->subscriberFactoryMock = $this->createPartialMock(
            SubscriberFactory::class,
            ['create']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $escaper = $this->objectManagerHelper->getObject(
            Escaper::class
        );
        $this->preview = $this->objectManagerHelper->getObject(
            Preview::class,
            [
                'appState' => $this->appStateMock,
                'storeManager' => $this->storeManagerMock,
                'request' => $this->requestMock,
                'templateFactory' => $templateFactory,
                'subscriberFactory' => $this->subscriberFactoryMock,
                'escaper' => $escaper
            ]
        );
    }

    public function testToHtml()
    {
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['id', null, 1],
                ['store', null, 1]
            ]
        );

        $this->templateMock->expects($this->atLeastOnce())->method('emulateDesign')->with(1);
        $this->templateMock->expects($this->atLeastOnce())->method('revertDesign');

        $this->appStateMock->expects($this->atLeastOnce())->method('emulateAreaCode')
            ->with(
                Template::DEFAULT_DESIGN_AREA,
                [$this->templateMock, 'getProcessedTemplate'],
                [['subscriber' => null]]
            )
            ->willReturn('Processed Template');

        $this->assertEquals('Processed Template', $this->preview->toHtml());
    }

    public function testToHtmlForNewTemplate()
    {
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['type', null, TemplateTypesInterface::TYPE_TEXT],
                ['text', null, 'Processed Template'],
                ['styles', null, '.class-name{color:red;}']
            ]
        );

        $this->templateMock->expects($this->once())->method('setTemplateType')->with(TemplateTypesInterface::TYPE_TEXT)
            ->willReturnSelf();
        $this->templateMock->expects($this->once())->method('setTemplateText')->with('Processed Template')
            ->willReturnSelf();
        $this->templateMock->expects($this->once())->method('setTemplateStyles')->with('.class-name{color:red;}')
            ->willReturnSelf();
        $this->templateMock->expects($this->atLeastOnce())->method('isPlain')->willReturn(true);
        $this->templateMock->expects($this->atLeastOnce())->method('emulateDesign')->with(1);
        $this->templateMock->expects($this->atLeastOnce())->method('revertDesign');

        $store = $this->createMock(Store::class);
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(1);

        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStores')->willReturn([$store]);

        $this->appStateMock->expects($this->atLeastOnce())->method('emulateAreaCode')
            ->with(
                Template::DEFAULT_DESIGN_AREA,
                [
                    $this->templateMock,
                    'getProcessedTemplate'
                ],
                [
                    [
                        'subscriber' => null
                    ]
                ]
            )
            ->willReturn('Processed Template');

        $this->assertEquals('<pre>Processed Template</pre>', $this->preview->toHtml());
    }

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
        $subscriber->expects($this->atLeastOnce())->method('load')->with(3)->willReturnSelf();
        $this->subscriberFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($subscriber);

        $this->templateMock->expects($this->atLeastOnce())->method('emulateDesign')->with(1);
        $this->templateMock->expects($this->atLeastOnce())->method('revertDesign');

        $this->appStateMock->expects($this->atLeastOnce())->method('emulateAreaCode')
            ->with(
                Template::DEFAULT_DESIGN_AREA,
                [
                    $this->templateMock,
                    'getProcessedTemplate'
                ],
                [
                    [
                        'subscriber' => $subscriber
                    ]
                ]
            )
            ->willReturn('Processed Template');

        $this->assertEquals('Processed Template', $this->preview->toHtml());
    }
}
