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
 * Test for \Magento\Newsletter\Block\Adminhtml\Template\Preview
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreviewTest extends TestCase
{
    /** @var Preview */
    protected $preview;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Newsletter\Model\Template|MockObject */
    protected $template;

    /** @var SubscriberFactory|MockObject */
    protected $subscriberFactory;

    /** @var State|MockObject */
    protected $appState;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var RequestInterface|MockObject */
    protected $request;

    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->appState = $this->createMock(State::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->template = $this->createPartialMock(
            Template::class,
            [
                'setTemplateType',
                'setTemplateText',
                'setTemplateStyles',
                'isPlain',
                'emulateDesign',
                'revertDesign',
                'getProcessedTemplate',
                'load'
            ]
        );
        $templateFactory = $this->createPartialMock(TemplateFactory::class, ['create']);
        $templateFactory->expects($this->once())->method('create')->willReturn($this->template);
        $this->subscriberFactory = $this->createPartialMock(
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
                'appState' => $this->appState,
                'storeManager' => $this->storeManager,
                'request' => $this->request,
                'templateFactory' => $templateFactory,
                'subscriberFactory' => $this->subscriberFactory,
                'escaper' => $escaper
            ]
        );
    }

    public function testToHtml()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['id', null, 1],
                ['store', null, 1]
            ]
        );

        $this->template->expects($this->atLeastOnce())->method('emulateDesign')->with(1);
        $this->template->expects($this->atLeastOnce())->method('revertDesign');

        $this->appState->expects($this->atLeastOnce())->method('emulateAreaCode')
            ->with(
                Template::DEFAULT_DESIGN_AREA,
                [$this->template, 'getProcessedTemplate'],
                [['subscriber' => null]]
            )
            ->willReturn('Processed Template');

        $this->assertEquals('Processed Template', $this->preview->toHtml());
    }

    public function testToHtmlForNewTemplate()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['type', null, TemplateTypesInterface::TYPE_TEXT],
                ['text', null, 'Processed Template'],
                ['styles', null, '.class-name{color:red;}']
            ]
        );

        $this->template->expects($this->once())->method('setTemplateType')->with(TemplateTypesInterface::TYPE_TEXT)
            ->willReturnSelf();
        $this->template->expects($this->once())->method('setTemplateText')->with('Processed Template')
            ->willReturnSelf();
        $this->template->expects($this->once())->method('setTemplateStyles')->with('.class-name{color:red;}')
            ->willReturnSelf();
        $this->template->expects($this->atLeastOnce())->method('isPlain')->willReturn(true);
        $this->template->expects($this->atLeastOnce())->method('emulateDesign')->with(1);
        $this->template->expects($this->atLeastOnce())->method('revertDesign');

        $store = $this->createMock(Store::class);
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(1);

        $this->storeManager->expects($this->atLeastOnce())->method('getStores')->willReturn([$store]);

        $this->appState->expects($this->atLeastOnce())->method('emulateAreaCode')
            ->with(
                Template::DEFAULT_DESIGN_AREA,
                [
                    $this->template,
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
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['id', null, 2],
                ['store', null, 1],
                ['subscriber', null, 3]
            ]
        );
        $subscriber = $this->createMock(Subscriber::class);
        $subscriber->expects($this->atLeastOnce())->method('load')->with(3)->willReturnSelf();
        $this->subscriberFactory->expects($this->atLeastOnce())->method('create')->willReturn($subscriber);

        $this->template->expects($this->atLeastOnce())->method('emulateDesign')->with(1);
        $this->template->expects($this->atLeastOnce())->method('revertDesign');

        $this->appState->expects($this->atLeastOnce())->method('emulateAreaCode')
            ->with(
                Template::DEFAULT_DESIGN_AREA,
                [
                    $this->template,
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
