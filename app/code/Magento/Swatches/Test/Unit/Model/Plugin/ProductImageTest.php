<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model\Plugin;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\Plugin\ProductImage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductImageTest extends TestCase
{
    /** @var Data|MockObject */
    protected $swatchesHelperMock;

    /** @var AttributeFactory|MockObject */
    protected $attributeFactoryMock;

    /** @var Config|MockObject */
    protected $eavConfigMock;

    /** @var Attribute|MockObject */
    protected $attributeMock;

    /** @var Http|MockObject */
    protected $requestMock;

    /** @var Product|MockObject */
    protected $productMock;

    /** @var ProductImage|ObjectManager */
    protected $pluginModel;

    protected function setUp(): void
    {
        $this->swatchesHelperMock = $this->createPartialMock(
            Data::class,
            ['loadVariationByFallback', 'isSwatchAttribute', 'isProductHasSwatch']
        );

        $this->attributeFactoryMock = $this->createPartialMock(
            AttributeFactory::class,
            ['create']
        );

        $this->eavConfigMock = $this->createMock(Config::class);

        $this->attributeMock = $this->createPartialMock(
            Attribute::class,
            ['loadByCode', 'getId', 'getUsedInProductListing', 'getIsFilterable', 'getData']
        );

        $this->requestMock = $this->createPartialMock(Http::class, ['getParams']);
        $this->productMock = $this->createMock(Product::class);

        $objectManager = new ObjectManager($this);

        $this->pluginModel = $objectManager->getObject(
            ProductImage::class,
            [
                'swatchesHelperData' => $this->swatchesHelperMock,
                'eavConfig' => $this->eavConfigMock,
                'request' => $this->requestMock,
            ]
        );
    }

    /**
     * @dataProvider dataForTest
     */
    public function testBeforeGetImage($expected)
    {
        $expected['product'] = $expected['product']($this);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('configurable');

        $this->requestMock
            ->expects($this->once())
            ->method('getParams')
            ->willReturn($expected['getParams']);

        $this->eavConfigMock
            ->method('getEntityAttributes')
            ->with('catalog_product')
            ->willReturn(['color' => $this->attributeMock]);

        $this->canReplaceImageWithSwatch($expected);
        $this->swatchesHelperMock
            ->expects($this->exactly($expected['loadVariationByFallback_count']))
            ->method('loadVariationByFallback')
            ->willReturn($expected['product']);
        $this->swatchesHelperMock
            ->method('isProductHasSwatch')
            ->with($this->productMock)
            ->willReturn(false);

        $productImageMock = $this->createMock(AbstractProduct::class);

        $result = $this->pluginModel->beforeGetImage($productImageMock, $this->productMock, $expected['page_handle']);
        $this->assertEquals([$this->productMock, $expected['page_handle'], []], $result);
    }

    /**
     * @param $expected
     */
    protected function getFilterArray($expected)
    {
        $this->eavConfigMock
            ->method('getEntityAttributeCodes')
            ->with('catalog_product')
            ->willReturn($expected['attribute_codes_array']);

        $this->eavConfigMock
            ->method('getAttribute')
            ->with('catalog_product', $expected['attribute_code'])
            ->willReturn($this->attributeMock);

        $this->attributeMock
            ->expects($this->exactly($expected['getId_count']))
            ->method('getId')
            ->willReturn($expected['getId']);
    }

    /**
     * @param $expected
     */
    protected function canReplaceImageWithSwatch($expected)
    {
        $this->swatchesHelperMock
            ->expects($this->once())
            ->method('isSwatchAttribute')
            ->with($this->attributeMock)
            ->willReturn($expected['isSwatchAttribute']);

        $this->attributeMock
            ->expects($this->exactly($expected['getUsedInProductListing_count']))
            ->method('getUsedInProductListing')
            ->willReturn($expected['getUsedInProductListing']);

        $this->attributeMock
            ->expects($this->exactly($expected['getIsFilterable_count']))
            ->method('getIsFilterable')
            ->willReturn($expected['getIsFilterable']);

        if ($expected['update_product_preview_image__count'] == 1) {
            $this->attributeMock
                ->method('getData')
                ->with('update_product_preview_image')
                ->willReturn($expected['update_product_preview_image']);
        }
    }

    protected function getMockForProductClass() {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())->method('getImage')->willReturn(false);
        return $productMock;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function dataForTest()
    {
        $productMock = static fn (self $testCase) => $testCase->getMockForProductClass();
        return [
            [
                [
                    'page_handle' => 'category_page_grid',
                    'getParams' => ['color' => 31],
                    'attribute_code' => 'color',
                    'getId_count' => 1,
                    'getId' => 332,
                    'isSwatchAttribute' => false,
                    'getUsedInProductListing' => true,
                    'getUsedInProductListing_count' => 1,
                    'getIsFilterable' => true,
                    'getIsFilterable_count' => 1,
                    'update_product_preview_image' =>true,
                    'update_product_preview_image__count' => 1,
                    'loadVariationByFallback_count' => 0,
                    'product' => $productMock,
                ],
            ],
            [
                [
                    'page_handle' => 'category_page_grid',
                    'getParams' => ['color' => 31],
                    'attribute_code' => 'color',
                    'getId_count' => 1,
                    'getId' => 332,
                    'isSwatchAttribute' => true,
                    'getUsedInProductListing' => true,
                    'getUsedInProductListing_count' => 1,
                    'getIsFilterable' => true,
                    'getIsFilterable_count' => 1,
                    'update_product_preview_image' =>true,
                    'update_product_preview_image__count' => 1,
                    'loadVariationByFallback_count' => 1,
                    'product' => $productMock,
                ],
            ],
            [
                [
                    'page_handle' => 'category_page_grid',
                    'getParams' => ['color' => 31],
                    'attribute_code' => 'color',
                    'getId_count' => 1,
                    'getId' => 332,
                    'isSwatchAttribute' => true,
                    'getUsedInProductListing' => true,
                    'getUsedInProductListing_count' => 1,
                    'getIsFilterable' => true,
                    'getIsFilterable_count' => 1,
                    'update_product_preview_image' =>false,
                    'update_product_preview_image__count' => 1,
                    'loadVariationByFallback_count' => 0,
                    'product' => $productMock,
                ],
            ],
        ];
    }
}
