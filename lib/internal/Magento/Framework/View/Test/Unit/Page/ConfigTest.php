<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Page\Config
 */
namespace Magento\Framework\View\Test\Unit\Page;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Page\Config;

/**
 * @covers \Magento\Framework\View\Page\Config
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /**
     * @var Config
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageAssets;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\Page\FaviconInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $favicon;

    /**
     * @var \Magento\Framework\View\Layout\BuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $builder;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $asset;

    /**
     * @var \Magento\Framework\View\Asset\Remote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $remoteAsset;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $title;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $areaResolverMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeMock;

    protected function setUp(): void
    {
        $this->assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->pageAssets = $this->createMock(\Magento\Framework\View\Asset\GroupedCollection::class);
        $this->scopeConfig =
            $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->favicon = $this->createMock(\Magento\Framework\View\Page\FaviconInterface::class);
        $this->builder = $this->createMock(\Magento\Framework\View\Layout\BuilderInterface::class);
        $this->asset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $this->remoteAsset = $this->createMock(\Magento\Framework\View\Asset\Remote::class);
        $this->title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $this->localeMock =
            $this->getMockForAbstractClass(\Magento\Framework\Locale\ResolverInterface::class, [], '', false);
        $this->localeMock->expects($this->any())
            ->method('getLocale')
            ->willReturn(Resolver::DEFAULT_LOCALE);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $escaper = $this->objectManager->getObject(
            \Magento\Framework\Escaper::class
        );
        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\View\Page\Config::class,
                [
                    'assetRepo' => $this->assetRepo,
                    'pageAssets' => $this->pageAssets,
                    'scopeConfig' => $this->scopeConfig,
                    'favicon' => $this->favicon,
                    'localeResolver' => $this->localeMock,
                    'escaper' => $escaper
                ]
            );

        $this->areaResolverMock = $this->createMock(\Magento\Framework\App\State::class);
        $areaResolverReflection = (new \ReflectionClass(get_class($this->model)))->getProperty('areaResolver');
        $areaResolverReflection->setAccessible(true);
        $areaResolverReflection->setValue($this->model, $this->areaResolverMock);
    }

    public function testSetBuilder()
    {
        $this->assertInstanceOf(
            \Magento\Framework\View\Page\Config::class,
            $this->model->setBuilder($this->builder)
        );
    }

    public function testBuild()
    {
        $this->model->setBuilder($this->builder);
        $this->builder->expects($this->once())->method('build')->willReturn(
            \Magento\Framework\View\LayoutInterface::class
        );
        $this->model->publicBuild();
    }

    public function testGetTitle()
    {
        $this->assertInstanceOf(\Magento\Framework\View\Page\Title::class, $this->model->getTitle());
    }

    public function testMetadata()
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
            'html_encoded' => '&lt;title&gt;&lt;span class=&quot;test&quot;&gt;Test&lt;/span&gt;&lt;/title&gt;',
        ];
        $this->model->setMetadata('name', 'test_value');
        $this->model->setMetadata('html_encoded', '<title><span class="test">Test</span></title>');
        $this->assertEquals($expectedMetadata, $this->model->getMetadata());
    }

    public function testContentType()
    {
        $contentType = 'test_content_type';
        $this->model->setContentType($contentType);
        $this->assertEquals($contentType, $this->model->getContentType());
    }

    public function testContentTypeEmpty()
    {
        $expectedData = null;
        $this->assertEquals($expectedData, $this->model->getContentType());
    }

    public function testContentTypeAuto()
    {
        $expectedData = 'default_media_type; charset=default_charset';
        $this->model->setContentType('auto');
        $this->scopeConfig->expects($this->at(0))->method('getValue')->with('design/head/default_media_type', 'store')
            ->willReturn('default_media_type');
        $this->scopeConfig->expects($this->at(1))->method('getValue')->with('design/head/default_charset', 'store')
            ->willReturn('default_charset');
        $this->assertEquals($expectedData, $this->model->getContentType());
    }

    public function testMediaType()
    {
        $mediaType = 'test_media_type';
        $this->model->setMediaType($mediaType);
        $this->assertEquals($mediaType, $this->model->getMediaType());
    }

    public function testMediaTypeEmpty()
    {
        $expectedData = 'default_media_type';
        $this->scopeConfig->expects($this->once())->method('getValue')->with('design/head/default_media_type', 'store')
            ->willReturn('default_media_type');
        $this->assertEquals($expectedData, $this->model->getMediaType());
    }

    public function testCharset()
    {
        $charset = 'test_charset';
        $this->model->setCharset($charset);
        $this->assertEquals($charset, $this->model->getCharset());
    }

    public function testCharsetEmpty()
    {
        $expectedData = 'default_charset';
        $this->scopeConfig->expects($this->once())->method('getValue')->with('design/head/default_charset', 'store')
            ->willReturn('default_charset');
        $this->assertEquals($expectedData, $this->model->getCharset());
    }

    public function testDescription()
    {
        $description = 'test_description';
        $this->model->setDescription($description);
        $this->assertEquals($description, $this->model->getDescription());
    }

    public function testDescriptionEmpty()
    {
        $expectedData = 'default_description';
        $this->scopeConfig->expects($this->once())->method('getValue')->with('design/head/default_description', 'store')
            ->willReturn('default_description');
        $this->assertEquals($expectedData, $this->model->getDescription());
    }

    public function testKeywords()
    {
        $keywords = 'test_keywords';
        $this->model->setKeywords($keywords);
        $this->assertEquals($keywords, $this->model->getKeywords());
    }

    public function testKeywordsEmpty()
    {
        $expectedData = 'default_keywords';
        $this->scopeConfig->expects($this->once())->method('getValue')->with('design/head/default_keywords', 'store')
            ->willReturn('default_keywords');
        $this->assertEquals($expectedData, $this->model->getKeywords());
    }

    public function testRobots()
    {
        $this->areaResolverMock->expects($this->once())->method('getAreaCode')->willReturn('frontend');
        $robots = 'test_robots';
        $this->model->setRobots($robots);
        $this->assertEquals($robots, $this->model->getRobots());
    }

    public function testRobotsEmpty()
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

    public function testRobotsAdminhtml()
    {
        $this->areaResolverMock->expects($this->once())->method('getAreaCode')->willReturn('adminhtml');
        $robots = 'test_robots';
        $this->model->setRobots($robots);
        $this->assertEquals('NOINDEX,NOFOLLOW', $this->model->getRobots());
    }

    public function testGetAssetCollection()
    {
        $this->assertInstanceOf(
            \Magento\Framework\View\Asset\GroupedCollection::class,
            $this->model->getAssetCollection()
        );
    }

    /**
     * @param string $file
     * @param array $properties
     * @param string|null $name
     * @param string $expectedName
     *
     * @dataProvider pageAssetDataProvider
     */
    public function testAddPageAsset($file, $properties, $name, $expectedName)
    {
        $this->assetRepo->expects($this->once())->method('createAsset')->with($file)->willReturn(
            $this->asset
        );
        $this->pageAssets->expects($this->once())->method('add')->with($expectedName, $this->asset, $properties);
        $this->assertInstanceOf(
            \Magento\Framework\View\Page\Config::class,
            $this->model->addPageAsset($file, $properties, $name)
        );
    }

    /**
     * @return array
     */
    public function pageAssetDataProvider()
    {
        return [
            [
                'test.php',
                ['one', 'two', 3],
                'test_name',
                'test_name',
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
     * @dataProvider remotePageAssetDataProvider
     */
    public function testAddRemotePageAsset($url, $contentType, $properties, $name, $expectedName)
    {
        $this->assetRepo->expects($this->once())->method('createRemoteAsset')->with($url, $contentType)->willReturn(
            $this->remoteAsset
        );
        $this->pageAssets->expects($this->once())->method('add')->with($expectedName, $this->remoteAsset, $properties);
        $this->assertInstanceOf(
            \Magento\Framework\View\Page\Config::class,
            $this->model->addRemotePageAsset($url, $contentType, $properties, $name)
        );
    }

    /**
     * @return array
     */
    public function remotePageAssetDataProvider()
    {
        return [
            [
                'http://test.com',
                '<body><context>some content</context></body>',
                ['one', 'two', 3],
                'test_name',
                'test_name',
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

    public function testAddRss()
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
        $this->assertInstanceOf(\Magento\Framework\View\Page\Config::class, $this->model->addRss($title, $href));
    }

    public function testAddBodyClass()
    {
        $className = 'test class';
        $this->assertInstanceOf(\Magento\Framework\View\Page\Config::class, $this->model->addBodyClass($className));
        $this->assertEquals('test-class', $this->model->getElementAttribute('body', 'class'));
    }

    /**
     * @param string $elementType
     * @param string $attribute
     * @param string $value
     *
     * @dataProvider elementAttributeDataProvider
     */
    public function testElementAttribute($elementType, $attribute, $value)
    {
        $this->model->setElementAttribute($elementType, $attribute, $value);
        $this->assertEquals($value, $this->model->getElementAttribute($elementType, $attribute));
    }

    /**
     * @return array
     */
    public function elementAttributeDataProvider()
    {
        return [
            [
                'head',
                'class',
                'test',
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
            ],
        ];
    }

    /**
     * @param string $elementType
     * @param string $attribute
     * @param string $value
     *
     * @dataProvider elementAttributeExceptionDataProvider
     */
    public function testElementAttributeException($elementType, $attribute, $value)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage($elementType . " isn't allowed");
        $this->model->setElementAttribute($elementType, $attribute, $value);
    }

    /**
     * @return array
     */
    public function elementAttributeExceptionDataProvider()
    {
        return [
            [
                'test',
                'class',
                'test',
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
     * @dataProvider elementAttributesDataProvider
     */
    public function testElementAttributes($elementType, $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->model->setElementAttribute($elementType, $attribute, $value);
        }
        $this->assertEquals($attributes, $this->model->getElementAttributes($elementType));
    }

    /**
     * @return array
     */
    public function elementAttributesDataProvider()
    {
        return [
            [
                'html',
                [
                    'context' => 'value',
                    Config::HTML_ATTRIBUTE_LANG => str_replace('_', '-', Resolver::DEFAULT_LOCALE)
                ],
            ],
        ];
    }

    /**
     * @param string $handle
     *
     * @dataProvider pageLayoutDataProvider
     */
    public function testPageLayout($handle)
    {
        $this->model->setPageLayout($handle);
        $this->assertEquals($handle, $this->model->getPageLayout());
    }

    /**
     * @return array
     */
    public function pageLayoutDataProvider()
    {
        return [
            [
                'test',
            ],
            [
                ''
            ],
            [
                null
            ],
            [
                [
                    'test',
                ]
            ]
        ];
    }

    public function testGetFaviconFile()
    {
        $expected = 'test';
        $this->favicon->expects($this->once())->method('getFaviconFile')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getFaviconFile());
    }

    public function testGetDefaultFavicon()
    {
        $this->favicon->expects($this->once())->method('getDefaultFavicon');
        $this->model->getDefaultFavicon();
    }

    /**
     * @param bool $isAvailable
     * @param string $result
     * @dataProvider getIncludesDataProvider
     */
    public function testGetIncludes($isAvailable, $result)
    {
        $model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\View\Page\Config::class,
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
    public function getIncludesDataProvider()
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
