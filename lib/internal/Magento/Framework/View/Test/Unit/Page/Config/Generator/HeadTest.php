<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Page\Config\Generator;

use Magento\Framework\View\Page\Config\Generator\Head;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\Generator\Context;
use Magento\Framework\View\Page\Config\Structure;
use Magento\Framework\View\Layout\Reader\Context as ReaderContext;

/**
 * Test for page config generator model
 */
class HeadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Head
     */
    protected $headGenerator;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $title;

    protected function setUp()
    {
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->title = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->headGenerator = $objectManagerHelper->getObject(
            \Magento\Framework\View\Page\Config\Generator\Head::class,
            [
                'pageConfig' => $this->pageConfigMock,
                'url' => $this->urlMock,
            ]
        );
    }

    public function testProcess()
    {
        $generatorContextMock = $this->createMock(Context::class);
        $this->title->expects($this->any())->method('set')->with()->will($this->returnSelf());
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
            ->with('file-url-link', Head::VIRTUAL_CONTENT_TYPE_LINK, ['attributes' => ['media' => 'all']]);
        $this->pageConfigMock->expects($this->at(2))
            ->method('addRemotePageAsset')
            ->with('http://magento.dev/customcss/render/css', 'css', ['attributes' => ['media' => 'all']]);
        $this->pageConfigMock->expects($this->once())
            ->method('addPageAsset')
            ->with('name', ['attributes' => ['media' => 'print'], 'ie_condition' => 'lt IE 7']);
        $structureMock->expects($this->once())
            ->method('getAssets')
            ->will($this->returnValue($assets));

        $title = 'Page title';
        $structureMock->expects($this->atLeastOnce())
            ->method('getTitle')
            ->will($this->returnValue($title));
        $this->pageConfigMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->title));

        $metadata = ['name1' => 'content1', 'name2' => 'content2'];
        $structureMock->expects($this->once())
            ->method('getMetadata')
            ->will($this->returnValue($metadata));
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
            ->will($this->returnValue($elementAttributes));
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
