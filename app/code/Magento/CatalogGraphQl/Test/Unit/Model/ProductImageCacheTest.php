<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model;

use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Catalog\Model\Product\Image as ProductImage;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\CatalogGraphQl\Model\ProductImageCache;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image\Placeholder as PlaceholderProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@see ProductImageCache}.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductImageCacheTest extends TestCase
{
    private const IMAGE_TYPE = 'small_image';
    private const IMAGE_PATH = '/m/a/magento_image.jpg';
    private const IMAGE_URL = 'https://example.test/media/catalog/product/cache/hash/m/a/magento_image.jpg';
    private const PLACEHOLDER_URL = 'https://example.test/static/placeholder/small_image.jpg';

    /**
     * @var ImageFactory|MockObject
     */
    private $productImageFactoryMock;

    /**
     * @var ParamsBuilder|MockObject
     */
    private $paramsBuilderMock;

    /**
     * @var PlaceholderProvider|MockObject
     */
    private $placeholderProviderMock;

    /**
     * @var CatalogMediaConfig|MockObject
     */
    private $catalogMediaConfigMock;

    /**
     * @var ProductImageCache
     */
    private $model;

    protected function setUp(): void
    {
        $this->productImageFactoryMock = $this->createMock(ImageFactory::class);
        $this->paramsBuilderMock = $this->createMock(ParamsBuilder::class);
        $this->placeholderProviderMock = $this->createMock(PlaceholderProvider::class);
        $this->catalogMediaConfigMock = $this->createMock(CatalogMediaConfig::class);

        $this->model = new ProductImageCache(
            $this->productImageFactoryMock,
            $this->paramsBuilderMock,
            $this->placeholderProviderMock,
            $this->catalogMediaConfigMock
        );
    }

    /**
     * Empty / null / no_selection paths must return a placeholder without creating an image model.
     *
     * @param string|null $imagePath
     */
    #[DataProvider('emptyImagePathDataProvider')]
    public function testGetUrlReturnsPlaceholderForEmptyImagePath(?string $imagePath): void
    {
        $this->productImageFactoryMock->expects($this->never())->method('create');
        $this->paramsBuilderMock->expects($this->never())->method('build');
        $this->catalogMediaConfigMock->expects($this->never())->method('getMediaUrlFormat');

        $this->placeholderProviderMock->expects($this->once())
            ->method('getPlaceholder')
            ->with(self::IMAGE_TYPE)
            ->willReturn(self::PLACEHOLDER_URL);

        $this->assertSame(
            self::PLACEHOLDER_URL,
            $this->model->getUrl(self::IMAGE_TYPE, $imagePath)
        );
    }

    /**
     * @return array<string, array{0: string|null}>
     */
    public static function emptyImagePathDataProvider(): array
    {
        return [
            'null' => [null],
            'empty_string' => [''],
            'no_selection' => ['no_selection'],
        ];
    }

    /**
     * When setBaseFile resolves to a placeholder, return placeholder and skip resize/save/watermark.
     */
    public function testGetUrlReturnsPlaceholderWhenBaseFileIsPlaceholder(): void
    {
        $params = $this->getBaseParams();
        $image = $this->createMock(ProductImage::class);

        $this->paramsBuilderMock->expects($this->once())
            ->method('build')
            ->with(
                [
                    'type' => self::IMAGE_TYPE,
                    'width' => null,
                    'height' => null,
                ]
            )
            ->willReturn($params);

        $this->productImageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($image);

        $this->expectConfigureImageWithoutWatermark($image, $params);

        $image->expects($this->once())
            ->method('setBaseFile')
            ->with(self::IMAGE_PATH);
        $image->expects($this->once())
            ->method('isBaseFilePlaceholder')
            ->willReturn(true);
        $image->expects($this->never())->method('isCached');
        $image->expects($this->never())->method('resize');
        $image->expects($this->never())->method('setWatermark');
        $image->expects($this->never())->method('saveFile');
        $image->expects($this->never())->method('getUrl');

        $this->catalogMediaConfigMock->expects($this->never())->method('getMediaUrlFormat');

        $this->placeholderProviderMock->expects($this->once())
            ->method('getPlaceholder')
            ->with(self::IMAGE_TYPE)
            ->willReturn(self::PLACEHOLDER_URL);

        $this->assertSame(
            self::PLACEHOLDER_URL,
            $this->model->getUrl(self::IMAGE_TYPE, self::IMAGE_PATH)
        );
    }

    /**
     * Cached HASH images only need getUrl(); no resize / watermark / save.
     */
    public function testGetUrlDoesNotGenerateWhenAlreadyCachedAndHashFormat(): void
    {
        $params = $this->getBaseParams();
        $image = $this->createMock(ProductImage::class);

        $this->paramsBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($params);

        $this->productImageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($image);

        $this->expectConfigureImageWithoutWatermark($image, $params);

        $image->expects($this->once())
            ->method('setBaseFile')
            ->with(self::IMAGE_PATH);
        $image->expects($this->once())
            ->method('isBaseFilePlaceholder')
            ->willReturn(false);
        $image->expects($this->once())
            ->method('isCached')
            ->willReturn(true);
        $image->expects($this->never())->method('resize');
        $image->expects($this->never())->method('setWatermark');
        $image->expects($this->never())->method('saveFile');
        $image->expects($this->once())
            ->method('getUrl')
            ->willReturn(self::IMAGE_URL);

        $this->catalogMediaConfigMock->expects($this->once())
            ->method('getMediaUrlFormat')
            ->willReturn(CatalogMediaConfig::HASH);

        $this->placeholderProviderMock->expects($this->never())->method('getPlaceholder');

        $this->assertSame(
            self::IMAGE_URL,
            $this->model->getUrl(self::IMAGE_TYPE, self::IMAGE_PATH)
        );
    }

    /**
     * Uncached HASH images with watermark params must configure watermark before setBaseFile,
     * then resize, apply watermark, and save.
     */
    public function testGetUrlGeneratesCacheFileWithWatermarkWhenNotCachedAndHashFormat(): void
    {
        $params = $this->getWatermarkParams();
        $image = $this->createMock(ProductImage::class);
        $callOrder = [];

        $this->paramsBuilderMock->expects($this->once())
            ->method('build')
            ->with(
                [
                    'type' => self::IMAGE_TYPE,
                    'width' => null,
                    'height' => null,
                ]
            )
            ->willReturn($params);

        $this->productImageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($image);

        $image->expects($this->once())
            ->method('setDestinationSubdir')
            ->with('small_image')
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setDestinationSubdir';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setWidth')
            ->with(null)
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setWidth';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setHeight')
            ->with(null)
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setHeight';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setKeepAspectRatio')
            ->with(true)
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setKeepFrame')
            ->with(true)
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setKeepTransparency')
            ->with(true)
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setConstrainOnly')
            ->with(true)
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setBackgroundColor')
            ->with([255, 255, 255])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setWatermarkFile')
            ->with('wm.png')
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setWatermarkFile';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setWatermarkPosition')
            ->with('center')
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setWatermarkPosition';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setWatermarkImageOpacity')
            ->with(70)
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setWatermarkImageOpacity';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setWatermarkWidth')
            ->with(200)
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setWatermarkWidth';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setWatermarkHeight')
            ->with(60)
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setWatermarkHeight';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setBaseFile')
            ->with(self::IMAGE_PATH)
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setBaseFile';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('isBaseFilePlaceholder')
            ->willReturn(false);
        $image->expects($this->once())
            ->method('isCached')
            ->willReturn(false);
        $image->expects($this->once())
            ->method('resize')
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'resize';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('setWatermark')
            ->with('wm.png')
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'setWatermark';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('saveFile')
            ->willReturnCallback(
                static function () use (&$callOrder, $image) {
                    $callOrder[] = 'saveFile';
                    return $image;
                }
            );
        $image->expects($this->once())
            ->method('getUrl')
            ->willReturn(self::IMAGE_URL);

        $this->catalogMediaConfigMock->expects($this->once())
            ->method('getMediaUrlFormat')
            ->willReturn(CatalogMediaConfig::HASH);

        $this->assertSame(
            self::IMAGE_URL,
            $this->model->getUrl(self::IMAGE_TYPE, self::IMAGE_PATH)
        );

        $configureMethods = [
            'setDestinationSubdir',
            'setWidth',
            'setHeight',
            'setWatermarkFile',
            'setWatermarkPosition',
            'setWatermarkImageOpacity',
            'setWatermarkWidth',
            'setWatermarkHeight',
        ];
        foreach ($configureMethods as $method) {
            $this->assertContains($method, $callOrder, sprintf('%s should have been called', $method));
            $this->assertLessThan(
                array_search('setBaseFile', $callOrder, true),
                array_search($method, $callOrder, true),
                sprintf('%s must be called before setBaseFile', $method)
            );
        }

        $this->assertLessThan(
            array_search('setWatermark', $callOrder, true),
            array_search('resize', $callOrder, true),
            'resize must run before setWatermark'
        );
        $this->assertLessThan(
            array_search('saveFile', $callOrder, true),
            array_search('setWatermark', $callOrder, true),
            'setWatermark must run before saveFile'
        );
        $this->assertLessThan(
            array_search('resize', $callOrder, true),
            array_search('setBaseFile', $callOrder, true),
            'setBaseFile must run before resize'
        );
    }

    /**
     * Uncached HASH images without watermark must resize and save, but never call setWatermark.
     */
    public function testGetUrlGeneratesCacheFileWithoutWatermarkWhenNotCachedAndHashFormat(): void
    {
        $params = $this->getBaseParams();
        $image = $this->createMock(ProductImage::class);

        $this->paramsBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($params);

        $this->productImageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($image);

        $this->expectConfigureImageWithoutWatermark($image, $params);

        $image->expects($this->once())
            ->method('setBaseFile')
            ->with(self::IMAGE_PATH);
        $image->expects($this->once())
            ->method('isBaseFilePlaceholder')
            ->willReturn(false);
        $image->expects($this->once())
            ->method('isCached')
            ->willReturn(false);
        $image->expects($this->once())
            ->method('resize')
            ->willReturnSelf();
        $image->expects($this->never())->method('setWatermark');
        $image->expects($this->never())->method('setWatermarkFile');
        $image->expects($this->once())
            ->method('saveFile')
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('getUrl')
            ->willReturn(self::IMAGE_URL);

        $this->catalogMediaConfigMock->expects($this->once())
            ->method('getMediaUrlFormat')
            ->willReturn(CatalogMediaConfig::HASH);

        $this->assertSame(
            self::IMAGE_URL,
            $this->model->getUrl(self::IMAGE_TYPE, self::IMAGE_PATH)
        );
    }

    /**
     * IMAGE_OPTIMIZATION_PARAMETERS format must never materialize cache files.
     */
    public function testGetUrlDoesNotGenerateWhenImageOptimizationParametersFormat(): void
    {
        $params = $this->getWatermarkParams();
        $image = $this->createMock(ProductImage::class);

        $this->paramsBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($params);

        $this->productImageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($image);

        $image->expects($this->once())
            ->method('setDestinationSubdir')
            ->with('small_image')
            ->willReturnSelf();
        $image->expects($this->once())->method('setWidth')->with(null)->willReturnSelf();
        $image->expects($this->once())->method('setHeight')->with(null)->willReturnSelf();
        $image->expects($this->once())->method('setKeepAspectRatio')->with(true)->willReturnSelf();
        $image->expects($this->once())->method('setKeepFrame')->with(true)->willReturnSelf();
        $image->expects($this->once())->method('setKeepTransparency')->with(true)->willReturnSelf();
        $image->expects($this->once())->method('setConstrainOnly')->with(true)->willReturnSelf();
        $image->expects($this->once())->method('setBackgroundColor')->with([255, 255, 255])->willReturnSelf();
        $image->expects($this->once())->method('setWatermarkFile')->with('wm.png')->willReturnSelf();
        $image->expects($this->once())->method('setWatermarkPosition')->with('center')->willReturnSelf();
        $image->expects($this->once())->method('setWatermarkImageOpacity')->with(70)->willReturnSelf();
        $image->expects($this->once())->method('setWatermarkWidth')->with(200)->willReturnSelf();
        $image->expects($this->once())->method('setWatermarkHeight')->with(60)->willReturnSelf();

        $image->expects($this->once())
            ->method('setBaseFile')
            ->with(self::IMAGE_PATH);
        $image->expects($this->once())
            ->method('isBaseFilePlaceholder')
            ->willReturn(false);
        // shouldGenerateCacheFile is false, so isCached is never consulted
        $image->expects($this->never())->method('isCached');
        $image->expects($this->never())->method('resize');
        $image->expects($this->never())->method('setWatermark');
        $image->expects($this->never())->method('saveFile');
        $image->expects($this->once())
            ->method('getUrl')
            ->willReturn(self::IMAGE_URL);

        $this->catalogMediaConfigMock->expects($this->once())
            ->method('getMediaUrlFormat')
            ->willReturn(CatalogMediaConfig::IMAGE_OPTIMIZATION_PARAMETERS);

        $this->assertSame(
            self::IMAGE_URL,
            $this->model->getUrl(self::IMAGE_TYPE, self::IMAGE_PATH)
        );
    }

    /**
     * Placeholder URLs are memoized per image type; _resetState clears that cache.
     */
    public function testResetStateClearsPlaceholderCache(): void
    {
        $this->productImageFactoryMock->expects($this->never())->method('create');

        $this->placeholderProviderMock->expects($this->exactly(2))
            ->method('getPlaceholder')
            ->with(self::IMAGE_TYPE)
            ->willReturn(self::PLACEHOLDER_URL);

        $this->assertSame(
            self::PLACEHOLDER_URL,
            $this->model->getUrl(self::IMAGE_TYPE, null)
        );
        // Second call for the same type hits the in-memory cache
        $this->assertSame(
            self::PLACEHOLDER_URL,
            $this->model->getUrl(self::IMAGE_TYPE, '')
        );

        $this->model->_resetState();

        // After reset, provider is consulted again
        $this->assertSame(
            self::PLACEHOLDER_URL,
            $this->model->getUrl(self::IMAGE_TYPE, 'no_selection')
        );
    }

    /**
     * Placeholder cache is keyed by image type.
     */
    public function testPlaceholderCacheIsPerImageType(): void
    {
        $this->placeholderProviderMock->expects($this->exactly(2))
            ->method('getPlaceholder')
            ->willReturnCallback(
                static function (string $imageType): string {
                    return 'https://example.test/placeholder/' . $imageType . '.jpg';
                }
            );

        $this->assertSame(
            'https://example.test/placeholder/small_image.jpg',
            $this->model->getUrl('small_image', null)
        );
        $this->assertSame(
            'https://example.test/placeholder/thumbnail.jpg',
            $this->model->getUrl('thumbnail', null)
        );
        // Same types again — still only two provider calls total (from expects above)
        $this->assertSame(
            'https://example.test/placeholder/small_image.jpg',
            $this->model->getUrl('small_image', '')
        );
        $this->assertSame(
            'https://example.test/placeholder/thumbnail.jpg',
            $this->model->getUrl('thumbnail', 'no_selection')
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getBaseParams(): array
    {
        return [
            'image_type' => 'small_image',
            'image_width' => null,
            'image_height' => null,
            'keep_aspect_ratio' => true,
            'keep_frame' => true,
            'keep_transparency' => true,
            'constrain_only' => true,
            'background' => [255, 255, 255],
            'watermark_file' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getWatermarkParams(): array
    {
        return [
            'image_type' => 'small_image',
            'image_width' => null,
            'image_height' => null,
            'keep_aspect_ratio' => true,
            'keep_frame' => true,
            'keep_transparency' => true,
            'constrain_only' => true,
            'background' => [255, 255, 255],
            'watermark_file' => 'wm.png',
            'watermark_position' => 'center',
            'watermark_image_opacity' => '70',
            'watermark_width' => '200',
            'watermark_height' => '60',
        ];
    }

    /**
     * @param ProductImage|MockObject $image
     * @param array<string, mixed> $params
     */
    private function expectConfigureImageWithoutWatermark(MockObject $image, array $params): void
    {
        $image->expects($this->once())
            ->method('setDestinationSubdir')
            ->with($params['image_type'])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setWidth')
            ->with($params['image_width'])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setHeight')
            ->with($params['image_height'])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setKeepAspectRatio')
            ->with($params['keep_aspect_ratio'])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setKeepFrame')
            ->with($params['keep_frame'])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setKeepTransparency')
            ->with($params['keep_transparency'])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setConstrainOnly')
            ->with($params['constrain_only'])
            ->willReturnSelf();
        $image->expects($this->once())
            ->method('setBackgroundColor')
            ->with($params['background'])
            ->willReturnSelf();
        $image->expects($this->never())->method('setWatermarkFile');
        $image->expects($this->never())->method('setWatermarkPosition');
        $image->expects($this->never())->method('setWatermarkImageOpacity');
        $image->expects($this->never())->method('setWatermarkWidth');
        $image->expects($this->never())->method('setWatermarkHeight');
    }
}
