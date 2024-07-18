<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Framework\View\Page\Config
 */
namespace Magento\Framework\View\Test\Unit\Page;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\Remote;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Layout\BuilderInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\FaviconInterface;
use Magento\Framework\View\Page\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Framework\View\Page\Config
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Config
     */
    protected $model;

    /**
     * @var Repository|MockObject
     */
    protected $assetRepo;

    /**
     * @var GroupedCollection|MockObject
     */
    protected $pageAssets;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var FaviconInterface|MockObject
     */
    protected $favicon;

    /**
     * @var BuilderInterface|MockObject
     */
    protected $builder;

    /**
     * @var File|MockObject
     */
    protected $asset;

    /**
     * @var Remote|MockObject
     */
    protected $remoteAsset;

    /**
     * @var Title|MockObject
     */
    protected $title;

    /**
     * @var State|MockObject
     */
    protected $areaResolverMock;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $localeMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->assetRepo = $this->createMock(Repository::class);
        $this->pageAssets = $this->createMock(GroupedCollection::class);
        $this->scopeConfig =
            $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->favicon = $this->getMockForAbstractClass(FaviconInterface::class);
        $this->builder = $this->getMockForAbstractClass(BuilderInterface::class);
        $this->asset = $this->createMock(File::class);
        $this->remoteAsset = $this->createMock(Remote::class);
        $this->title = $this->createMock(Title::class);
        $this->localeMock =
            $this->getMockForAbstractClass(ResolverInterface::class, [], '', false);
        $this->localeMock->expects($this->any())
            ->method('getLocale')
            ->willReturn(Resolver::DEFAULT_LOCALE);
        $this->objectManager = new ObjectManager($this);
        $escaper = $this->objectManager->getObject(
            Escaper::class
        );
        $this->model = (new ObjectManager($this))
            ->getObject(
                Config::class,
                [
                    'assetRepo' => $this->assetRepo,
                    'pageAssets' => $this->pageAssets,
                    'scopeConfig' => $this->scopeConfig,
                    'favicon' => $this->favicon,
                    'localeResolver' => $this->localeMock,
                    'escaper' => $escaper
                ]
            );

        $this->areaResolverMock = $this->createMock(State::class);
        $areaResolverReflection = (new \ReflectionClass(get_class($this->model)))->getProperty('areaResolver');
        $areaResolverReflection->setAccessible(true);
        $areaResolverReflection->setValue($this->model, $this->areaResolverMock);
    }

    /**
     * @return void
     */
    public function testSetBuilder(): void
    {
        $this->assertInstanceOf(
            Config::class,
            $this->model->setBuilder($this->builder)
        );
    }

    /**
     * @return void
     */
    public function testBuild(): void
    {
        $this->model->setBuilder($this->builder);
        $this->builder->expects($this->once())->method('build')->willReturn(
            LayoutInterface::class
        );
        $this->model->publicBuild();
    }

    /**
     * @return void
     */
    public function testGetTitle(): void
    {
        $this->assertInstanceOf(Title::class, $this->model->getTitle());
    }

    /**
     * @return void
     */
    public function testMetadata(): void
    {
        $expectedMetadata = [
            'charset' => null,
            'media_type' => null,
            'content_type' => null,
            'description' => null,
            'keywords' => null,
            'robots' => null,
            'title' => null,
            'name' => 'test_value',
            'html_encoded' => '&lt;title&gt;&lt;span class=&quot;test&quot;&gt;Test&lt;/span&gt;&lt;/title&gt;'
        ];
        $this->model->setMetadata('name', 'test_value');
        $this->model->setMetadata('html_encoded', '<title><span class="test">Test</span></title>');
        $this->assertEquals($expectedMetadata, $this->model->getMetadata());
    }

    /**
     * @return void
     */
    public function testContentType(): void
    {
        $contentType = 'test_content_type';
        $this->model->setContentType($contentType);
        $this->assertEquals($contentType, $this->model->getContentType());
    }

    /**
     * @return void
     */
    public function testContentTypeEmpty(): void
    {
        $expectedData = null;
        $this->assertEquals($expectedData, $this->model->getContentType());
    }

    /**
     * @return void
     */
    public function testContentTypeAuto(): void
    {
        $expectedData = 'default_media_type; charset=default_charset';
        $this->model->setContentType('auto');
        $this->scopeConfig
            ->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == 'design/head/default_media_type' && $arg2 == 'store') {
                    return 'default_media_type';
                } elseif ($arg1 == 'design/head/default_charset' && $arg2 == 'store') {
                    return 'default_charset';
                }
            });

        $this->assertEquals($expectedData, $this->model->getContentType());
    }

    /**
     * @return void
     */
    public function testMediaType(): void
    {
        $mediaType = 'test_media_type';
        $this->model->setMediaType($mediaType);
        $this->assertEquals($mediaType, $this->model->getMediaType());
    }

    /**
     * @return void
     */
    public function testMediaTypeEmpty(): void
    {
        $expectedData = 'default_media_type';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('design/head/default_media_type', 'store')
            ->willReturn('default_media_type');
        $this->assertEquals($expectedData, $this->model->getMediaType());
    }

    /**
     * @return void
     */
    public function testCharset(): void
    {
        $charset = 'test_charset';
        $this->model->setCharset($charset);
        $this->assertEquals($charset, $this->model->getCharset());
    }

    /**
     * @return void
     */
    public function testCharsetEmpty(): void
    {
        $expectedData = 'default_charset';
        $this->scopeConfig->expects($this->once())->method('getValue')->with('design/head/default_charset', 'store')
            ->willReturn('default_charset');
        $this->assertEquals($expectedData, $this->model->getCharset());
    }

    /**
     * @return void
     */
    public function testDescription(): void
    {
        $description = 'test_description';
        $this->model->setDescription($description);
        $this->assertEquals($description, $this->model->getDescription());
    }

    /**
     * @return void
     */
    public function testDescriptionEmpty(): void
    {
        $expectedData = 'default_description';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('design/head/default_description', 'store')
            ->willReturn('default_description');
        $this->assertEquals($expectedData, $this->model->getDescription());
    }

    /**
     * @return void
     */
    public function testKeywords(): void
    {
        $keywords = 'test_keywords';
        $this->model->setKeywords($keywords);
        $this->assertEquals($keywords, $this->model->getKeywords());
    }

    /**
     * @return void
     */
    public function testKeywordsEmpty(): void
    {
        $expectedData = 'default_keywords';
        $this->scopeConfig->expects($this->once())->method('getValue')->with('design/head/default_keywords', 'store')
            ->willReturn('default_keywords');
        $this->assertEquals($expectedData, $this->model->getKeywords());
    }

    /**
     * @return void
     */
    public function testRobots(): void
    {
        $this->areaResolverMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');
        $robots = 'test_robots';
        $this->model->setRobots($robots);
        $this->assertEquals($robots, $this->model->getRobots());
    }

    /**
     * @return void
     */
    public function testRobotsEmpty(): void
    {
        $this->areaResolverMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');
        $expectedData = 'default_robots';
        $this->scopeConfig->expects($this->once())->method('getValue')->with(
            'design/search_engine_robots/default_robots',
            'store'
        )
            ->willReturn('default_robots');
        $this->assertEquals($expectedData, $this->model->getRobots());
    }

    /**
     * @return void
     */
    public function testRobotsAdminhtml(): void
    {
        $this->areaResolverMock->expects($this->once())->method('getAreaCode')->willReturn('adminhtml');
        $robots = 'test_robots';
        $this->model->setRobots($robots);
        $this->assertEquals('NOINDEX,NOFOLLOW', $this->model->getRobots());
    }

    /**
     * @return void
     */
    public function testGetAssetCollection(): void
    {
        $this->assertInstanceOf(
            GroupedCollection::class,
            $this->model->getAssetCollection()
        );
    }

    /**
     * @param string $file
     * @param array $properties
     * @param string|null $name
     * @param string $expectedName
     *
     * @return void
     * @dataProvider pageAssetDataProvider
     */
    public function testAddPageAsset($file, $properties, $name, $expectedName): void
    {
        $this->assetRepo->expects($this->once())->method('createAsset')->with($file)->willReturn(
            $this->asset
        );
        $this->pageAssets->expects($this->once())->method('add')->with($expectedName, $this->asset, $properties);
        $this->assertInstanceOf(
            Config::class,
            $this->model->addPageAsset($file, $properties, $name)
        );
    }

    /**
     * @return array
     */
    public static function pageAssetDataProvider(): array
    {
        return [
            [
                'test.php',
                ['one', 'two', 3],
                'test_name',
                'test_name'
            ],
            [
                'filename',
                [],
                null,
                'filename'
            ]
        ];
    }

    /**
     * @param string $url
     * @param string $contentType
     * @param array $properties
     * @param string|null $name
     * @param string $expectedName
     *
     * @return void
     * @dataProvider remotePageAssetDataProvider
     */
    public function testAddRemotePageAsset($url, $contentType, $properties, $name, $expectedName): void
    {
        $this->assetRepo->expects($this->once())->method('createRemoteAsset')->with($url, $contentType)->willReturn(
            $this->remoteAsset
        );
        $this->pageAssets->expects($this->once())->method('add')->with($expectedName, $this->remoteAsset, $properties);
        $this->assertInstanceOf(
            Config::class,
            $this->model->addRemotePageAsset($url, $contentType, $properties, $name)
        );
    }

    /**
     * @return array
     */
    public static function remotePageAssetDataProvider(): array
    {
        return [
            [
                'http://test.com',
                '<body><context>some content</context></body>',
                ['one', 'two', 3],
                'test_name',
                'test_name'
            ],
            [
                'http://test.com',
                '',
                [],
                null,
                'http://test.com'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testAddRss(): void
    {
        $title = 'test title';
        $href = 'http://test.com';
        $expected = ['attributes' => 'rel="alternate" type="application/rss+xml" title="test title"'];
        $this->assetRepo->expects($this->once())->method('createRemoteAsset')->with($href, 'unknown')->willReturn(
            $this->remoteAsset
        );
        $this->pageAssets->expects($this->once())->method('add')->with(
            'link/http://test.com',
            $this->remoteAsset,
            $expected
        );
        $this->assertInstanceOf(Config::class, $this->model->addRss($title, $href));
    }

    /**
     * @return void
     */
    public function testAddBodyClass(): void
    {
        $className = 'test class';
        $this->assertInstanceOf(Config::class, $this->model->addBodyClass($className));
        $this->assertEquals('test-class', $this->model->getElementAttribute('body', 'class'));
    }

    /**
     * @param string $elementType
     * @param string $attribute
     * @param string $value
     *
     * @return void
     * @dataProvider elementAttributeDataProvider
     */
    public function testElementAttribute($elementType, $attribute, $value): void
    {
        $this->model->setElementAttribute($elementType, $attribute, $value);
        $this->assertEquals($value, $this->model->getElementAttribute($elementType, $attribute));
    }

    /**
     * @return array
     */
    public static function elementAttributeDataProvider(): array
    {
        return [
            [
                'head',
                'class',
                'test'
            ],
            [
                'body',
                'class',
                'value'
            ],
            [
                Config::ELEMENT_TYPE_HTML,
                Config::HTML_ATTRIBUTE_LANG,
                str_replace('_', '-', Resolver::DEFAULT_LOCALE)
            ]
        ];
    }

    /**
     * @param string $elementType
     * @param string $attribute
     * @param string $value
     *
     * @return void
     * @dataProvider elementAttributeExceptionDataProvider
     */
    public function testElementAttributeException($elementType, $attribute, $value): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($elementType . " isn't allowed");
        $this->model->setElementAttribute($elementType, $attribute, $value);
    }

    /**
     * @return array
     */
    public static function elementAttributeExceptionDataProvider(): array
    {
        return [
            [
                'test',
                'class',
                'test'
            ],
            [
                '',
                '',
                ''
            ],
            [
                null,
                null,
                null
            ]
        ];
    }

    /**
     * @param string $elementType
     * @param string $attributes
     *
     * @return void
     * @dataProvider elementAttributesDataProvider
     */
    public function testElementAttributes($elementType, $attributes): void
    {
        foreach ($attributes as $attribute => $value) {
            $this->model->setElementAttribute($elementType, $attribute, $value);
        }
        $this->assertEquals($attributes, $this->model->getElementAttributes($elementType));
    }

    /**
     * @return array
     */
    public static function elementAttributesDataProvider(): array
    {
        return [
            [
                'html',
                [
                    'context' => 'value',
                    Config::HTML_ATTRIBUTE_LANG => str_replace('_', '-', Resolver::DEFAULT_LOCALE)
                ]
            ]
        ];
    }

    /**
     * @param string $handle
     *
     * @return void
     * @dataProvider pageLayoutDataProvider
     */
    public function testPageLayout($handle): void
    {
        $this->model->setPageLayout($handle);
        $this->assertEquals($handle, $this->model->getPageLayout());
    }

    /**
     * @return array
     */
    public static function pageLayoutDataProvider(): array
    {
        return [
            [
                'test'
            ],
            [
                ''
            ],
            [
                null
            ],
            [
                [
                    'test'
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetFaviconFile(): void
    {
        $expected = 'test';
        $this->favicon->expects($this->once())->method('getFaviconFile')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getFaviconFile());
    }

    /**
     * @return void
     */
    public function testGetDefaultFavicon(): void
    {
        $this->favicon->expects($this->once())->method('getDefaultFavicon');
        $this->model->getDefaultFavicon();
    }

    /**
     * @param bool $isAvailable
     * @param string $result
     *
     * @return void
     * @dataProvider getIncludesDataProvider
     */
    public function testGetIncludes($isAvailable, $result): void
    {
        $model = (new ObjectManager($this))
            ->getObject(
                Config::class,
                [
                    'assetRepo' => $this->assetRepo,
                    'pageAssets' => $this->pageAssets,
                    'scopeConfig' => $this->scopeConfig,
                    'favicon' => $this->favicon,
                    'localeResolver' => $this->localeMock,
                    'isIncludesAvailable' => $isAvailable
                ]
            );

        $this->scopeConfig->expects($isAvailable ? $this->once() : $this->never())
            ->method('getValue')
            ->with('design/head/includes', 'store')
            ->willReturn($result);
        $this->assertEquals($result, $model->getIncludes());
    }

    /**
     * @return array
     */
    public static function getIncludesDataProvider(): array
    {
        return [
            [
                true,
                '<script type="text/javascript">
                    Fieldset.addToPrefix(1);
                </script>'
            ],
            [false, null]
        ];
    }
}
