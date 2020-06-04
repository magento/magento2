<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Page\Config\Generator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout\Generator\Context;
use Magento\Framework\View\Layout\Reader\Context as ReaderContext;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Generator\Head;
use Magento\Framework\View\Page\Config\Structure;
use Magento\Framework\View\Page\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for page config generator model
 */
class HeadTest extends TestCase
{
    /**
     * @var Head
     */
    protected $headGenerator;

    /**
     * @var \Magento\Framework\View\Page\Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlMock;

    /**
     * @var Title|MockObject
     */
    protected $title;

    protected function setUp(): void
    {
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->title = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->headGenerator = $objectManagerHelper->getObject(
            Head::class,
            [
                'pageConfig' => $this->pageConfigMock,
                'url' => $this->urlMock,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProcess()
    {
        $generatorContextMock = $this->createMock(Context::class);
        $this->title->expects($this->any())->method('set')->with()->willReturnSelf();
        $structureMock = $this->createMock(Structure::class);
        $readerContextMock = $this->createMock(ReaderContext::class);
        $readerContextMock->expects($this->any())->method('getPageConfigStructure')->willReturn($structureMock);

        $structureMock->expects($this->once())->method('processRemoveAssets');
        $structureMock->expects($this->once())->method('processRemoveElementAttributes');
        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with('customcss/render/css')
            ->willReturn('http://magento.dev/customcss/render/css');

        $assets = [
            'remoteCss' => [
                'src' => 'file-url-css',
                'src_type' => 'url',
                'content_type' => 'css',
                'media' => 'all',
            ],
            'remoteCssOrderedLast' => [
                'src' => 'file-url-css-last',
                'src_type' => 'url',
                'content_type' => 'css',
                'media' => 'all',
                'order' => 30,
            ],
            'remoteCssOrderedFirst' => [
                'src' => 'file-url-css-first',
                'src_type' => 'url',
                'content_type' => 'css',
                'media' => 'all',
                'order' => 10,
            ],
            'remoteLink' => [
                'src' => 'file-url-link',
                'src_type' => 'url',
                'media' => 'all',
            ],
            'controllerCss' => [
                'src' => 'customcss/render/css',
                'src_type' => 'controller',
                'content_type' => 'css',
                'media' => 'all',
            ],
            'name' => [
                'src' => 'file-path',
                'ie_condition' => 'lt IE 7',
                'content_type' => 'css',
                'media' => 'print',
            ],
        ];

        $this->pageConfigMock->expects($this->at(0))
            ->method('addRemotePageAsset')
            ->with('file-url-css', 'css', ['attributes' => ['media' => 'all']]);
        $this->pageConfigMock->expects($this->at(1))
            ->method('addRemotePageAsset')
            ->with('file-url-css-last', 'css', ['attributes' => ['media' => 'all' ] , 'order' => 30]);
        $this->pageConfigMock->expects($this->at(2))
            ->method('addRemotePageAsset')
            ->with('file-url-css-first', 'css', ['attributes' => ['media' => 'all'] , 'order' => 10]);
        $this->pageConfigMock->expects($this->at(3))
            ->method('addRemotePageAsset')
            ->with('file-url-link', Head::VIRTUAL_CONTENT_TYPE_LINK, ['attributes' => ['media' => 'all']]);
        $this->pageConfigMock->expects($this->at(4))
            ->method('addRemotePageAsset')
            ->with('http://magento.dev/customcss/render/css', 'css', ['attributes' => ['media' => 'all']]);
        $this->pageConfigMock->expects($this->once())
            ->method('addPageAsset')
            ->with('name', ['attributes' => ['media' => 'print'], 'ie_condition' => 'lt IE 7']);
        $structureMock->expects($this->once())
            ->method('getAssets')
            ->willReturn($assets);

        $title = 'Page title';
        $structureMock->expects($this->atLeastOnce())
            ->method('getTitle')
            ->willReturn($title);
        $this->pageConfigMock->expects($this->any())->method('getTitle')->willReturn($this->title);

        $metadata = ['name1' => 'content1', 'name2' => 'content2'];
        $structureMock->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);
        $this->pageConfigMock->expects($this->exactly(2))
            ->method('setMetadata')
            ->withConsecutive(['name1', 'content1'], ['name2', 'content2']);

        $elementAttributes = [
            PageConfig::ELEMENT_TYPE_BODY => [
                'body_attr_1' => 'body_value_1',
                'body_attr_2' => 'body_value_2',
            ],
            PageConfig::ELEMENT_TYPE_HTML => [
                'html_attr_1' => 'html_attr_1',
            ],
        ];
        $structureMock->expects($this->once())
            ->method('getElementAttributes')
            ->willReturn($elementAttributes);
        $this->pageConfigMock->expects($this->exactly(3))
            ->method('setElementAttribute')
            ->withConsecutive(
                [PageConfig::ELEMENT_TYPE_BODY, 'body_attr_1', 'body_value_1'],
                [PageConfig::ELEMENT_TYPE_BODY, 'body_attr_2', 'body_value_2'],
                [PageConfig::ELEMENT_TYPE_HTML, 'html_attr_1', 'html_attr_1']
            );

        $result = $this->headGenerator->process($readerContextMock, $generatorContextMock);
        $this->assertEquals($this->headGenerator, $result);
    }
}
