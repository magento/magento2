<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Page\Config;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\MergeService;
use Magento\Framework\View\Asset\PropertyGroup;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Generator\Head;
use Magento\Framework\View\Page\Config\Metadata\MsApplicationTileImage;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\Framework\View\Page\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for page config renderer model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends TestCase
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var AssetInterface|MockObject
     */
    protected $assetInterfaceMock;

    /**
     * @var MergeService|MockObject
     */
    protected $assetMergeServiceMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var StringUtils|MockObject
     */
    protected $stringMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var MsApplicationTileImage|MockObject
     */
    protected $msApplicationTileImageMock;

    /**
     * @var GroupedCollection|MockObject
     */
    protected $assetsCollection;

    /**
     * @var Title|MockObject
     */
    protected $titleMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMergeServiceMock = $this->getMockBuilder(MergeService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);

        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->stringMock = $this->getMockBuilder(StringUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->msApplicationTileImageMock = $this->getMockBuilder(MsApplicationTileImage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetsCollection = $this->getMockBuilder(GroupedCollection::class)
            ->setMethods(['getGroups'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetInterfaceMock = $this->getMockForAbstractClass(AssetInterface::class);

        $this->titleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['set', 'get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->renderer = $this->objectManagerHelper->getObject(
            Renderer::class,
            [
                'pageConfig' => $this->pageConfigMock,
                'assetMergeService' => $this->assetMergeServiceMock,
                'urlBuilder' => $this->urlBuilderMock,
                'escaper' => $this->escaperMock,
                'string' => $this->stringMock,
                'logger' => $this->loggerMock,
                'msApplicationTileImage' => $this->msApplicationTileImageMock
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
            'og:video:secure_url' => 'secure_url',
            'msapplication-TileImage' => 'https://site.domain/ms-tile.jpg'
        ];
        $metadataValueCharset = 'newCharsetValue';

        $expected = '<meta charset="newCharsetValue"/>' . "\n"
            . '<meta name="metadataName" content="metadataValue"/>' . "\n"
            . '<meta http-equiv="Content-Type" content="content_type_value"/>' . "\n"
            . '<meta http-equiv="X-UA-Compatible" content="x_ua_compatible_value"/>' . "\n"
            . '<meta property="og:video:secure_url" content="secure_url"/>' . "\n"
            . '<meta name="msapplication-TileImage" content="https://site.domain/ms-tile.jpg"/>' . "\n";

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
            ->willReturn($metadata);

        $this->msApplicationTileImageMock
            ->expects($this->once())
            ->method('getUrl')
            ->with('https://site.domain/ms-tile.jpg')
            ->willReturn('https://site.domain/ms-tile.jpg');

        $this->assertEquals($expected, $this->renderer->renderMetadata());
    }

    /**
     * Test renderMetadata when it has 'msapplication-TileImage' meta passed
     */
    public function testRenderMetadataWithMsApplicationTileImageAsset()
    {
        $metadata = [
            'msapplication-TileImage' => 'images/ms-tile.jpg'
        ];
        $expectedMetaUrl = 'https://site.domain/images/ms-tile.jpg';
        $expected = '<meta name="msapplication-TileImage" content="' . $expectedMetaUrl . '"/>' . "\n";

        $this->pageConfigMock
            ->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->msApplicationTileImageMock
            ->expects($this->once())
            ->method('getUrl')
            ->with('images/ms-tile.jpg')
            ->willReturn($expectedMetaUrl);

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
                    Head::VIRTUAL_CONTENT_TYPE_LINK,
                    ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
                    'icon',
                ],
                [
                    $filePath,
                    Head::VIRTUAL_CONTENT_TYPE_LINK,
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

        $exception = new LocalizedException(new Phrase('my message'));

        $assetMockOne = $this->getMockForAbstractClass(AssetInterface::class);
        $assetMockOne->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturn($assetUrl);
        $assetMockOne->expects($this->atLeastOnce())->method('getContentType')->willReturn($groupOne['type']);

        $groupAssetsOne = [$assetMockOne, $assetMockOne];

        $groupMockOne = $this->getMockBuilder(PropertyGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupMockOne->expects($this->once())
            ->method('getAll')
            ->willReturn($groupAssetsOne);
        $groupMockOne->expects($this->any())
            ->method('getProperty')
            ->willReturnMap(
                [
                    [GroupedCollection::PROPERTY_CAN_MERGE, true],
                    [GroupedCollection::PROPERTY_CONTENT_TYPE, $groupOne['type']],
                    ['attributes', $groupOne['attributes']],
                    ['ie_condition', $groupOne['condition']],
                ]
            );

        $assetMockTwo = $this->getMockForAbstractClass(AssetInterface::class);
        $assetMockTwo->expects($this->once())
            ->method('getUrl')
            ->willThrowException($exception);
        $assetMockTwo->expects($this->atLeastOnce())->method('getContentType')->willReturn($groupTwo['type']);

        $groupAssetsTwo = [$assetMockTwo];

        $groupMockTwo = $this->getMockBuilder(PropertyGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupMockTwo->expects($this->once())
            ->method('getAll')
            ->willReturn($groupAssetsTwo);
        $groupMockTwo->expects($this->any())
            ->method('getProperty')
            ->willReturnMap(
                [
                    [GroupedCollection::PROPERTY_CAN_MERGE, true],
                    [GroupedCollection::PROPERTY_CONTENT_TYPE, $groupTwo['type']],
                    ['attributes', $groupTwo['attributes']],
                    ['ie_condition', $groupTwo['condition']],
                ]
            );

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

    public function testRenderAssetWithNoContentType() : void
    {
        $type = '';

        $assetMockOne = $this->getMockForAbstractClass(AssetInterface::class);
        $assetMockOne->expects($this->exactly(1))
            ->method('getUrl')
            ->willReturn('url');

        $assetMockOne->expects($this->atLeastOnce())->method('getContentType')->willReturn($type);

        $groupAssetsOne = [$assetMockOne];

        $groupMockOne = $this->getMockBuilder(PropertyGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupMockOne->expects($this->once())
            ->method('getAll')
            ->willReturn($groupAssetsOne);
        $groupMockOne->expects($this->any())
            ->method('getProperty')
            ->willReturnMap(
                [
                    [GroupedCollection::PROPERTY_CAN_MERGE, true],
                    [GroupedCollection::PROPERTY_CONTENT_TYPE, $type],
                    ['attributes', 'rel="some-rel"'],
                    ['ie_condition', null],
                ]
            );

        $this->pageConfigMock->expects($this->once())
            ->method('getAssetCollection')
            ->willReturn($this->assetsCollection);

        $this->assetsCollection->expects($this->once())
            ->method('getGroups')
            ->willReturn([$groupMockOne]);

        $this->assertEquals(
            '<link  rel="some-rel" href="url" />' . "\n",
            $this->renderer->renderAssets($this->renderer->getAvailableResultGroups())
        );
    }
}
