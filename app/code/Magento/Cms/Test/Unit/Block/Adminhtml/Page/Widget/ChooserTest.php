<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Block\Adminhtml\Page\Widget;

/**
 * @covers \Magento\Cms\Block\Adminhtml\Page\Widget\Chooser
 */
class ChooserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Block\Adminhtml\Page\Widget\Chooser
     */
    protected $this;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandomMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaper;

    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsPageMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Cms\Model\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $elementMock;

    /**
     * @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $chooserMock;

    protected function setUp()
    {
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mathRandomMock = $this->getMockBuilder(\Magento\Framework\Math\Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'escapeHtml',
                ]
            )
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
            ->setMethods(
                [
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->elementMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getValue',
                    'setData',
                ]
            )
            ->getMock();
        $this->cmsPageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getTitle',
                    'load',
                    'getId',
                ]
            )
            ->getMock();
        $this->chooserMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setElement',
                    'setConfig',
                    'setFieldsetId',
                    'setSourceUrl',
                    'setUniqId',
                    'setLabel',
                    'toHtml',
                ]
            )
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            \Magento\Backend\Block\Template\Context::class,
            [
                'layout'     => $this->layoutMock,
                'mathRandom' => $this->mathRandomMock,
                'urlBuilder' => $this->urlBuilderMock,
                'escaper'    => $this->escaper,
            ]
        );
        $this->this = $objectManager->getObject(
            \Magento\Cms\Block\Adminhtml\Page\Widget\Chooser::class,
            [
                'context'     => $this->context,
                'pageFactory' => $this->pageFactoryMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Block\Adminhtml\Block\Widget\Chooser::prepareElementHtml
     *
     * @param string $elementValue
     * @param integer|null $cmsPageId
     *
     * @dataProvider prepareElementHtmlDataProvider
     */
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
            ->with(\Magento\Widget\Block\Adminhtml\Widget\Chooser::class)
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
    public function prepareElementHtmlDataProvider()
    {
        return [
            'elementValue NOT EMPTY, modelBlockId NOT EMPTY' => [
                'elementValue' => 'some value',
                'cmsPageId' => 1,
            ],
            'elementValue NOT EMPTY, modelBlockId IS EMPTY' => [
                'elementValue' => 'some value',
                'cmsPageId' => null,
            ],
            'elementValue IS EMPTY, modelBlockId NEVER REACHED' => [
                'elementValue' => '',
                'cmsPageId' => 1,
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
