<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Block\Adminhtml\Page\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Block\Adminhtml\Page\Widget\Chooser;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Widget\Block\Adminhtml\Widget\Chooser as WidgetChooser;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers \Magento\Cms\Block\Adminhtml\Page\Widget\Chooser
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChooserTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Chooser
     */
    protected $this;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Random|MockObject
     */
    protected $mathRandomMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var Page|MockObject
     */
    protected $cmsPageMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var AbstractElement|MockObject
     */
    protected $elementMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $chooserMock;

    protected function setUp(): void
    {
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'escapeHtml',
                ]
            )
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->onlyMethods(
                [
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->elementMock = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['getValue', 'getId', 'setData']
        );
        $this->cmsPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getTitle',
                    'load',
                    'getId',
                ]
            )
            ->getMock();
        $this->chooserMock = $this->createPartialMockWithReflection(
            BlockInterface::class,
            [
                'toHtml',
                'setElement',
                'setConfig',
                'setFieldsetId',
                'setSourceUrl',
                'setUniqId',
                'setLabel'
            ]
        );

        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'layout'     => $this->layoutMock,
                'mathRandom' => $this->mathRandomMock,
                'urlBuilder' => $this->urlBuilderMock,
                'escaper'    => $this->escaper,
            ]
        );
        $this->this = $objectManager->getObject(
            Chooser::class,
            [
                'context'     => $this->context,
                'pageFactory' => $this->pageFactoryMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Block\Adminhtml\Block\Widget\Chooser::prepareElementHtml
     * @param string $elementValue
     * @param integer|null $cmsPageId
     */
    #[DataProvider('prepareElementHtmlDataProvider')]
    public function testPrepareElementHtml($elementValue, $cmsPageId)
    {
        //$elementValue = 12345;
        //$cmsPageId    = 1;
        $elementId    = 1;
        $uniqId       = '126hj4h3j73hk7b347jhkl37gb34';
        $sourceUrl    = 'cms/page_widget/chooser/126hj4h3j73hk7b347jhkl37gb34';
        $config       = ['key1' => 'value1'];
        $fieldsetId   = 2;
        $html         = 'some html';
        $title        = 'some "><img src=y onerror=prompt(document.domain)>; title';
        $titleEscaped = 'some &quot;&gt;&lt;img src=y onerror=prompt(document.domain)&gt;; title';

        $this->this->setConfig($config);
        $this->this->setFieldsetId($fieldsetId);

        $this->elementMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($elementId);
        $this->mathRandomMock->expects($this->atLeastOnce())
            ->method('getUniqueHash')
            ->with($elementId)
            ->willReturn($uniqId);
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('cms/page_widget/chooser', ['uniq_id' => $uniqId])
            ->willReturn($sourceUrl);
        $this->layoutMock->expects($this->atLeastOnce())
            ->method('createBlock')
            ->with(WidgetChooser::class)
            ->willReturn($this->chooserMock);
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setElement')
            ->with($this->elementMock)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setConfig')
            ->with($config)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setFieldsetId')
            ->with($fieldsetId)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setSourceUrl')
            ->with($sourceUrl)
            ->willReturnSelf();
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('setUniqId')
            ->with($uniqId)
            ->willReturnSelf();
        $this->elementMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($elementValue);
        $this->pageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->cmsPageMock);
        $this->cmsPageMock->expects($this->any())
            ->method('load')
            ->with((int)$elementValue)
            ->willReturnSelf();
        $this->cmsPageMock->expects($this->any())
            ->method('getId')
            ->willReturn($cmsPageId);
        $this->cmsPageMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);
        $this->chooserMock->expects($this->atLeastOnce())
            ->method('toHtml')
            ->willReturn($html);
        if (!empty($elementValue) && !empty($cmsPageId)) {
            $this->escaper->expects(($this->atLeastOnce()))
                ->method('escapeHtml')
                ->willReturn($titleEscaped);
            $this->chooserMock->expects($this->atLeastOnce())
                ->method('setLabel')
                ->with($titleEscaped)
                ->willReturnSelf();
        }
        $this->elementMock->expects($this->atLeastOnce())
            ->method('setData')
            ->with('after_element_html', $html)
            ->willReturnSelf();

        $this->assertEquals($this->elementMock, $this->this->prepareElementHtml($this->elementMock));
    }

    /**
     * @return array
     */
    public static function prepareElementHtmlDataProvider()
    {
        return [
            'elementValue NOT EMPTY, modelBlockId NOT EMPTY' => [
                'some value', // $elementValue
                1,            // $cmsPageId
            ],
            'elementValue NOT EMPTY, modelBlockId IS EMPTY' => [
                'some value', // $elementValue
                null,         // $cmsPageId
            ],
            'elementValue IS EMPTY, modelBlockId NEVER REACHED' => [
                '',  // $elementValue
                1,   // $cmsPageId
            ]
        ];
    }

    /**
     * @covers \Magento\Cms\Block\Adminhtml\Page\Widget\Chooser::getGridUrl
     */
    public function testGetGridUrl()
    {
        $url = 'some url';

        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('cms/page_widget/chooser', ['_current' => true])
            ->willReturn($url);

        $this->assertEquals($url, $this->this->getGridUrl());
    }
}
