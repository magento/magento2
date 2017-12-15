<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Page\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\Framework\View\Page\Config\Generator;

/**
 * Test for page config renderer model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMergeServiceMock = $this->getMockBuilder(\Magento\Framework\View\Asset\MergeService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);

        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->stringMock = $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();

        $this->assetsCollection = $this->getMockBuilder(\Magento\Framework\View\Asset\GroupedCollection::class)
            ->setMethods(['getGroups'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetInterfaceMock = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\AssetInterface::class);

        $this->titleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->setMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->renderer = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Page\Config\Renderer::class,
            [
                'pageConfig' => $this->pageConfigMock,
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
            'og:video:secure_url' => 'secure_url'
        ];
        $metadataValueCharset = 'newCharsetValue';

        $expected = '<meta charset="newCharsetValue"/>' . "\n"
            . '<meta name="metadataName" content="metadataValue"/>' . "\n"
            . '<meta http-equiv="Content-Type" content="content_type_value"/>' . "\n"
            . '<meta http-equiv="X-UA-Compatible" content="x_ua_compatible_value"/>' . "\n"
            . '<meta property="og:video:secure_url" content="secure_url"/>' . "\n";

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
            ->willReturn($this->titleMock);

        $this->titleMock->expects($this->once())
            ->method('get')
            ->willReturn($title);

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);

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
     * @param $groupOne
     * @param $groupTwo
     * @param $expectedResult
     * @dataProvider dataProviderRenderAssets
     */
    public function testRenderAssets($groupOne, $groupTwo, $expectedResult)
    {
        $assetUrl = 'url';
        $assetNoRoutUrl = 'no_route_url';

        $exception = new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('my message'));

        $assetMockOne = $this->createMock(\Magento\Framework\View\Asset\AssetInterface::class);
        $assetMockOne->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturn($assetUrl);
        $assetMockOne->expects($this->atLeastOnce())->method('getContentType')->willReturn($groupOne['type']);

        $groupAssetsOne = [$assetMockOne, $assetMockOne];

        $groupMockOne = $this->getMockBuilder(\Magento\Framework\View\Asset\PropertyGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupMockOne->expects($this->once())
            ->method('getAll')
            ->willReturn($groupAssetsOne);
        $groupMockOne->expects($this->any())
            ->method('getProperty')
            ->willReturnMap([
                [GroupedCollection::PROPERTY_CAN_MERGE, true],
                [GroupedCollection::PROPERTY_CONTENT_TYPE, $groupOne['type']],
                ['attributes', $groupOne['attributes']],
                ['ie_condition', $groupOne['condition']],
            ]);

        $assetMockTwo = $this->createMock(\Magento\Framework\View\Asset\AssetInterface::class);
        $assetMockTwo->expects($this->once())
            ->method('getUrl')
            ->willThrowException($exception);
        $assetMockTwo->expects($this->atLeastOnce())->method('getContentType')->willReturn($groupTwo['type']);

        $groupAssetsTwo = [$assetMockTwo];

        $groupMockTwo = $this->getMockBuilder(\Magento\Framework\View\Asset\PropertyGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupMockTwo->expects($this->once())
            ->method('getAll')
            ->willReturn($groupAssetsTwo);
        $groupMockTwo->expects($this->any())
            ->method('getProperty')
            ->willReturnMap([
                [GroupedCollection::PROPERTY_CAN_MERGE, true],
                [GroupedCollection::PROPERTY_CONTENT_TYPE, $groupTwo['type']],
                ['attributes', $groupTwo['attributes']],
                ['ie_condition', $groupTwo['condition']],
            ]);

        $this->pageConfigMock->expects($this->once())
            ->method('getAssetCollection')
            ->willReturn($this->assetsCollection);

        $this->assetsCollection->expects($this->once())
            ->method('getGroups')
            ->willReturn([$groupMockOne, $groupMockTwo]);

        $this->assetMergeServiceMock->expects($this->exactly(1))
            ->method('getMergedAssets')
            ->willReturnArgument(0);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('', ['_direct' => 'core/index/notFound'])
            ->willReturn($assetNoRoutUrl);

        $this->assertEquals(
            $expectedResult,
            $this->renderer->renderAssets($this->renderer->getAvailableResultGroups())
        );
    }

    /**
     * @return array
     */
    public function dataProviderRenderAssets()
    {
        return [
            [
                ['type' => 'css', 'attributes' => '', 'condition' => null],
                ['type' => 'js', 'attributes' => 'attr="value"', 'condition' => null],
                '<link  rel="stylesheet" type="text/css"  media="all" href="url" />' . "\n"
                    . '<link  rel="stylesheet" type="text/css"  media="all" href="url" />' . "\n"
                    . '<script  type="text/javascript"  attr="value" src="no_route_url"></script>' . "\n"
            ],
            [
                ['type' => 'js', 'attributes' => ['attr' => 'value'], 'condition' => 'lt IE 7'],
                ['type' => 'css', 'attributes' => 'attr="value"', 'condition' => null],
                '<link  rel="stylesheet" type="text/css"  attr="value" href="no_route_url" />' . "\n"
                    . '<!--[if lt IE 7]>' . "\n"
                    . '<script  type="text/javascript"  attr="value" src="url"></script>' . "\n"
                    . '<script  type="text/javascript"  attr="value" src="url"></script>' . "\n"
                    . '<![endif]-->' . "\n"
            ],
            [
                ['type' => 'ico', 'attributes' => 'attr="value"', 'condition' => null],
                ['type' => 'css', 'attributes' => '', 'condition' => null],
                '<link  rel="stylesheet" type="text/css"  media="all" href="no_route_url" />' . "\n"
                    . '<link  attr="value" href="url" />' . "\n"
                    . '<link  attr="value" href="url" />' . "\n"
            ],
            [
                ['type' => 'js', 'attributes' => '', 'condition' => null],
                ['type' => 'ico', 'attributes' => ['attr' => 'value'], 'condition' => null],
                '<link  attr="value" href="no_route_url" />' . "\n"
                    . '<script  type="text/javascript"  src="url"></script>' . "\n"
                    . '<script  type="text/javascript"  src="url"></script>' . "\n"
            ],
            [
                ['type' => 'non', 'attributes' => ['attr' => 'value'], 'condition' => null],
                ['type' => 'ico', 'attributes' => '', 'condition' => null],
                '<link  href="no_route_url" />' . "\n"
                    . '<link  attr="value" href="url" />' . "\n"
                    . '<link  attr="value" href="url" />' . "\n"
            ],
        ];
    }
}
