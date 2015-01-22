<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page\Config;

use Magento\Framework\View\Asset\GroupedCollection;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for page config renderer model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Asset\MinifyService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetMinifyServiceMock;

    /**
     * @var \Magento\Framework\View\Asset\AssetInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetInterfaceMock;

    /**
     * @var \Magento\Framework\View\Asset\MergeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetMergeServiceMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetsCollection;

    /**
     * @var \Magento\Framework\View\Asset\PropertyGroup|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $propertyGroupMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMinifyServiceMock = $this->getMockBuilder('Magento\Framework\View\Asset\MinifyService')
            ->setMethods(['getAssets'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMergeServiceMock = $this->getMockBuilder('Magento\Framework\View\Asset\MergeService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this->getMockForAbstractClass('Magento\Framework\UrlInterface');

        $this->escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->stringMock = $this->getMockBuilder('Magento\Framework\Stdlib\String')
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMock();

        $this->assetsCollection = $this->getMockBuilder('Magento\Framework\View\Asset\GroupedCollection')
            ->setMethods(['getGroups'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->propertyGroupMock = $this->getMockBuilder('Magento\Framework\View\Asset\PropertyGroup')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetInterfaceMock = $this->getMockForAbstractClass('Magento\Framework\View\Asset\AssetInterface');

        $this->titleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->setMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->renderer = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Page\Config\Renderer',
            [
                'pageConfig' => $this->pageConfigMock,
                'assetMinifyService' => $this->assetMinifyServiceMock,
                'assetMergeService' => $this->assetMergeServiceMock,
                'urlBuilder' => $this->urlBuilderMock,
                'escaper' => $this->escaperMock,
                'string' => $this->stringMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testRenderElementAttributes()
    {
        $elementType = 'elementType';
        $attributes = ['attr1' => 'value1', 'attr2' => 'value2'];
        $expected = 'attr1="value1" attr2="value2"';

        $this->pageConfigMock->expects($this->once())
            ->method('getElementAttributes')
            ->with($elementType)
            ->willReturn($attributes);

        $this->assertEquals($expected, $this->renderer->renderElementAttributes($elementType));
    }

    public function testRenderMetadata()
    {
        $metadata = [
            'charset' => 'charsetValue',
            'metadataName' => 'metadataValue',
            'content_type' => 'content_type_value',
            'x_ua_compatible' => 'x_ua_compatible_value',
            'media_type' => 'media_type_value',
        ];
        $metadataValueCharset = 'newCharsetValue';

        $expected = '<meta charset="newCharsetValue"/>' . "\n"
            . '<meta name="metadataName" content="metadataValue"/>' . "\n"
            . '<meta http-equiv="Content-Type" content="content_type_value"/>' . "\n"
            . '<meta http-equiv="X-UA-Compatible" content="x_ua_compatible_value"/>' . "\n";

        $this->stringMock->expects($this->at(0))
            ->method('upperCaseWords')
            ->with('charset', '_', '')
            ->willReturn('Charset');

        $this->pageConfigMock->expects($this->once())
            ->method('getCharset')
            ->willReturn($metadataValueCharset);

        $this->pageConfigMock
            ->expects($this->once())
            ->method('getMetadata')
            ->will($this->returnValue($metadata));

        $this->assertEquals($expected, $this->renderer->renderMetadata());
    }

    public function testRenderTitle()
    {
        $title = 'some_title';
        $expected = "<title>some_title</title>" . "\n";

        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));

        $this->titleMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($title));

        $this->assertEquals($expected, $this->renderer->renderTitle());
    }

    public function testPrepareFavicon()
    {
        $filePath = 'file';
        $this->pageConfigMock->expects($this->exactly(3))
            ->method('getFaviconFile')
            ->willReturn($filePath);

        $this->pageConfigMock->expects($this->exactly(2))
            ->method('addRemotePageAsset')
            ->withConsecutive(
                [
                    $filePath,
                    Generator\Head::VIRTUAL_CONTENT_TYPE_LINK,
                    ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
                    'icon',
                ],
                [
                    $filePath,
                    Generator\Head::VIRTUAL_CONTENT_TYPE_LINK,
                    ['attributes' => ['rel' => 'shortcut icon', 'type' => 'image/x-icon']],
                    'shortcut-icon'
                ]
            );

        $this->renderer->prepareFavicon();
    }

    public function testPrepareFaviconDefault()
    {
        $defaultFilePath = 'default_file';
        $this->pageConfigMock->expects($this->once())
            ->method('getFaviconFile')
            ->willReturn(false);
        $this->pageConfigMock->expects($this->exactly(2))
            ->method('getDefaultFavicon')
            ->willReturn($defaultFilePath);

        $this->pageConfigMock->expects($this->exactly(2))
            ->method('addPageAsset')
            ->withConsecutive(
                [
                    $defaultFilePath,
                    ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
                    'icon',
                ],
                [
                    $defaultFilePath,
                    ['attributes' => ['rel' => 'shortcut icon', 'type' => 'image/x-icon']],
                    'shortcut-icon'
                ]
            );
        $this->renderer->prepareFavicon();
    }

    /**
     * @param $contentType
     * @param $attributes
     * @param $ieCondition
     * @param $expectedResult
     * @dataProvider dataProviderRenderAsset
     */
    public function testRenderAsset($contentType, $attributes, $ieCondition, $expectedResult)
    {
        $assetUrl = 'url';
        $assetNoRoutUrl = 'no_route_url';

        $exception = new \Magento\Framework\Exception('my message');

        $assetMock1 = $this->getMock('Magento\Framework\View\Asset\AssetInterface');
        $assetMock1->expects($this->once())
            ->method('getUrl')
            ->willReturn($assetUrl);

        $assetMock2 = $this->getMock('Magento\Framework\View\Asset\AssetInterface');
        $assetMock2->expects($this->once())
            ->method('getUrl')
            ->willThrowException($exception);

        $groupAssets = [$assetMock1, $assetMock2];

        $this->pageConfigMock->expects($this->once())
            ->method('getAssetCollection')
            ->willReturn($this->assetsCollection);

        $this->assetsCollection->expects($this->once())
            ->method('getGroups')
            ->willReturn([$this->propertyGroupMock]);

        $this->propertyGroupMock->expects($this->once())
            ->method('getAll')
            ->willReturn($groupAssets);
        $this->propertyGroupMock->expects($this->any())
            ->method('getProperty')
            ->willReturnMap([
                [GroupedCollection::PROPERTY_CAN_MERGE, true],
                [GroupedCollection::PROPERTY_CONTENT_TYPE, $contentType],
                ['attributes', $attributes],
                ['ie_condition', $ieCondition],
            ]);

        $this->assetMinifyServiceMock
            ->expects($this->once())
            ->method('getAssets')
            ->with($groupAssets)
            ->willReturn($groupAssets);

        $this->assetMergeServiceMock->expects($this->once())
            ->method('getMergedAssets')
            ->with($groupAssets, $contentType)
            ->willReturnArgument(0);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('', ['_direct' => 'core/index/notFound'])
            ->willReturn($assetNoRoutUrl);

        $this->assertEquals($expectedResult, $this->renderer->renderAssets());
    }

    /**
     * @return array
     */
    public function dataProviderRenderAsset()
    {
        $css = '<link  rel="stylesheet" type="text/css"  media="all" href="url" />' . "\n"
            . '<link  rel="stylesheet" type="text/css"  media="all" href="no_route_url" />' . "\n";

        $cssWithAttr = '<link  rel="stylesheet" type="text/css"  attr="value" href="url" />' . "\n"
            . '<link  rel="stylesheet" type="text/css"  attr="value" href="no_route_url" />' . "\n";

        $js = '<script  type="text/javascript"  attr="value" src="url"></script>' . "\n"
            . '<script  type="text/javascript"  attr="value" src="no_route_url"></script>' . "\n";

        $jsWithIfIe = '<!--[if lt IE 7]>' . "\n"
            . '<script  type="text/javascript"  attr="value" src="url"></script>' . "\n"
            . '<script  type="text/javascript"  attr="value" src="no_route_url"></script>' . "\n"
            . '<![endif]-->' . "\n";

        return [
            ['css', '', null, $css],
            ['css', 'attr="value"', null, $cssWithAttr],
            ['js', ['attr' => 'value'], null, $js],
            ['js', ['attr' => 'value'], 'lt IE 7', $jsWithIfIe]
        ];
    }
}
