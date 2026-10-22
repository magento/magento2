<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\ProductImageCache;
use Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery\Url;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /**
     * @var ProductImageCache|MockObject
     */
    private $productImageCacheMock;

    /**
     * @var Url
     */
    private $resolver;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $infoMock;

    protected function setUp(): void
    {
        $this->productImageCacheMock = $this->createMock(ProductImageCache::class);
        $this->resolver = new Url($this->productImageCacheMock);
        $this->fieldMock = $this->createMock(Field::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->infoMock = $this->createMock(ResolveInfo::class);
    }

    public function testResolveByImageType(): void
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->once())
            ->method('getData')
            ->with('small_image')
            ->willReturn('/m/a/magento_image.jpg');

        $this->productImageCacheMock->expects($this->once())
            ->method('getUrl')
            ->with('small_image', '/m/a/magento_image.jpg')
            ->willReturn('https://example.test/media/catalog/product/cache/abc/m/a/magento_image.jpg');

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->infoMock,
            [
                'model' => $product,
                'image_type' => 'small_image',
            ]
        );

        $this->assertSame(
            'https://example.test/media/catalog/product/cache/abc/m/a/magento_image.jpg',
            $result
        );
    }

    public function testResolveByFileUsesImageType(): void
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->never())->method('getData');

        $this->productImageCacheMock->expects($this->once())
            ->method('getUrl')
            ->with('image', '/m/a/magento_image.jpg')
            ->willReturn('https://example.test/media/catalog/product/cache/abc/m/a/magento_image.jpg');

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->infoMock,
            [
                'model' => $product,
                'file' => '/m/a/magento_image.jpg',
            ]
        );

        $this->assertStringContainsString('magento_image.jpg', $result);
    }

    public function testResolveThrowsWhenImageTypeAndFileMissing(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"image_type" value should be specified');

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->infoMock,
            ['model' => $this->createMock(Product::class)]
        );
    }

    public function testResolveThrowsWhenModelMissing(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->infoMock,
            ['image_type' => 'image']
        );
    }

    public function testResetStateDelegatesToProductImageCache(): void
    {
        $this->productImageCacheMock->expects($this->once())->method('_resetState');
        $this->resolver->_resetState();
    }
}
