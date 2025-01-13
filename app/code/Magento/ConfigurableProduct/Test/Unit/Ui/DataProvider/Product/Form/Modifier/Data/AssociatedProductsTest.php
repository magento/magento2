<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Currency;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssociatedProductsTest extends TestCase
{
    /**
     * @var LocatorInterface|MockObject
     */
    private LocatorInterface $locator;

    /**
     * @var ConfigurableType|MockObject
     */
    private ConfigurableType $configurableType;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private StockRegistryInterface $stockRegistry;

    /**
     * @var VariationMatrix|MockObject
     */
    private VariationMatrix $variationMatrix;

    /**
     * @var UrlInterface|MockObject
     */
    private UrlInterface $urlBuilder;

    /**
     * @var CurrencyInterface|MockObject
     */
    private CurrencyInterface $localeCurrency;

    /**
     * @var JsonHelper|MockObject
     */
    private JsonHelper $jsonHelper;

    /**
     * @var ImageHelper|MockObject
     */
    private ImageHelper $imageHelper;

    /**
     * @var Escaper|MockObject
     */
    private Escaper $escaper;

    /**
     * @inhertidoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = $this->createMock(LocatorInterface::class);
        $this->configurableType = $this->createMock(ConfigurableType::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->stockRegistry = $this->createMock(StockRegistryInterface::class);
        $this->variationMatrix = $this->createMock(VariationMatrix::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->localeCurrency = $this->createMock(CurrencyInterface::class);
        $this->jsonHelper = $this->createMock(JsonHelper::class);
        $this->imageHelper = $this->createMock(ImageHelper::class);
        $this->escaper = $this->createMock(Escaper::class);
    }

    /**
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetProductMatrix(): void
    {
        $productId = 1;
        $product = $this->createMock(Product::class);
        $product->expects($this->any())
            ->method('__call')
            ->with('getAssociatedProductIds')
            ->willReturn([$productId]);
        $product->expects($this->any())->method('getData')->with('attribute_code')->willReturn('attribute_value');
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $product->expects($this->any())->method('getSku')->willReturn('sku');
        $product->expects($this->any())->method('getName')->willReturn('name');
        $product->expects($this->any())->method('getPrice')->willReturn(100.00);
        $product->expects($this->any())->method('getWeight')->willReturn(1);
        $product->expects($this->any())->method('getStatus')->willReturn(1);
        $baseCurrency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $baseCurrency->expects($this->once())->method('getCurrencySymbol')->willReturn('$');
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($baseCurrency);
        $product->expects($this->once())->method('getStore')->willReturn($store);

        $stockItem = $this->createMock(StockItemInterface::class);
        $stockItem->expects($this->once())->method('getQty')->willReturn(1);
        $this->stockRegistry->expects($this->once())->method('getStockItem')->willReturn($stockItem);
        $this->productRepository->expects($this->exactly(2))
            ->method('getById')
            ->with($productId)
            ->willReturn($product);
        $this->locator->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);
        $attribute = $this->createMock(AbstractAttribute::class);
        $attribute->expects($this->any())->method('getAttributeCode')->willReturn('attribute_code');
        $attribute->expects($this->any())->method('getAttributeId')->willReturn('1');
        $option = $this->createMock(AttributeOptionInterface::class);
        $option->expects($this->any())->method('getValue')->willReturn('attribute_value');
        $option->expects($this->any())->method('getLabel')->willReturn('attribute_label');
        $attribute->expects($this->any())->method('getOptions')->willReturn([$option]);
        $this->configurableType->expects($this->exactly(2))
            ->method('getUsedProductAttributes')
            ->with($product)
            ->willReturn([$attribute]);
        $this->configurableType->expects($this->once())->method('getConfigurableAttributesAsArray')
            ->willReturn(
                [
                    1 => [
                        'id' => 1,
                        'label' => 'attribute_label',
                        'use_default' => '0',
                        'position' => '0',
                        'values' => [
                            0 => [
                                'value_index' => 'attribute_value',
                                'label' => 'attribute_label',
                                'product_super_attribute_id' => '10',
                                'default_label' => 'attribute_label',
                                'store_label' => 'attribute_label',
                                'use_default_value' => true
                            ]
                        ],
                        'attribute_id' => '1',
                        'attribute_code' => 'attribute_code',
                        'frontend_label' => 'attribute_label',
                        'store_label' => 'attribute_label',
                        'options' => [
                            0 => [
                                'label' => 'attribute_label',
                                'value' => 'attribute_value'
                            ]
                        ]
                    ]
                ]
            );
        $image = $this->createMock(Image::class);
        $image->expects($this->once())->method('getUrl')->willReturn('image_url');
        $this->imageHelper->expects($this->once())
            ->method('init')
            ->with($product, 'product_thumbnail_image')
            ->willReturn($image);
        $this->locator->expects($this->once())->method('getBaseCurrencyCode')->willReturn('USD');
        $this->locator->expects($this->once())->method('getStore')->willReturn($store);
        $currency = $this->createMock(Currency::class);
        $currency->expects($this->once())->method('toCurrency')->with(100.00)->willReturn('100.00$');
        $this->localeCurrency->expects($this->once())
            ->method('getCurrency')
            ->with('USD')
            ->willReturn($currency);
        $this->jsonHelper->expects($this->once())
            ->method('jsonEncode')
            ->with(['attribute_code' => 'attribute_value'])
            ->willReturn('{"attribute_code":"attribute_value"}');

        $associatedProducts = new AssociatedProducts(
            $this->locator,
            $this->urlBuilder,
            $this->configurableType,
            $this->productRepository,
            $this->stockRegistry,
            $this->variationMatrix,
            $this->localeCurrency,
            $this->jsonHelper,
            $this->imageHelper,
            $this->escaper
        );

        $expected = [
            0 => [
                'id' => 1,
                'product_link' => '<a href="" target="_blank"></a>',
                'sku' => 'sku',
                'name' => 'name',
                'qty' => 1,
                'price' => 100.0,
                'price_string' => '100.00$',
                'price_currency' => '$',
                'configurable_attribute' => '{"attribute_code":"attribute_value"}',
                'weight' => 1,
                'status' => 1,
                'variationKey' => 'attribute_value',
                'canEdit' => 0,
                'newProduct' => 0,
                'attributes' => ': attribute_label',
                'thumbnail_image' => 'image_url',
            ]
        ];
        $this->assertSame($expected, $associatedProducts->getProductMatrix());
    }
}
