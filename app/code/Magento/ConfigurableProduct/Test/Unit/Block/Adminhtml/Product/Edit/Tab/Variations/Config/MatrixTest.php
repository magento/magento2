<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Block\Component\StepsWizard;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MatrixTest extends TestCase
{
    /**
     * @var Configurable|MockObject
     */
    private Configurable $_configurableType;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private StockRegistryInterface $stockRegistry;

    /**
     * @var VariationMatrix|MockObject
     */
    private VariationMatrix $variationMatrix;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Image|MockObject
     */
    private Image $image;

    /**
     * @var CurrencyInterface|MockObject
     */
    private CurrencyInterface $localeCurrency;

    /**
     * @var LocatorInterface|MockObject
     */
    private LocatorInterface $locator;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @inhertidoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->_configurableType = $this->createMock(Configurable::class);
        $this->stockRegistry = $this->createMock(StockRegistryInterface::class);
        $this->variationMatrix = $this->createMock(VariationMatrix::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->image = $this->createMock(Image::class);
        $this->localeCurrency = $this->createMock(CurrencyInterface::class);
        $this->locator = $this->createMock(LocatorInterface::class);
        $this->context = $this->createMock(Context::class);
    }

    /**
     * Run test getProductStockQty method
     *
     * @return void
     * @throws Exception
     */
    public function testGetProductStockQty(): void
    {
        $productId = 10;
        $websiteId = 99;
        $qty = 100.00;

        $productMock = $this->createPartialMock(Product::class, ['getId', 'getStore']);
        $storeMock = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $stockItemMock = $this->createMock(StockItemInterface::class);

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->willReturn($stockItemMock);
        $stockItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);

        $data = [
            'jsonHelper' => $this->createMock(JsonHelper::class),
            'directoryHelper' => $this->createMock(DirectoryHelper::class)
        ];
        $matrix = new Matrix(
            $this->context,
            $this->_configurableType,
            $this->stockRegistry,
            $this->variationMatrix,
            $this->productRepository,
            $this->image,
            $this->localeCurrency,
            $this->locator,
            $data
        );

        $this->assertEquals($qty, $matrix->getProductStockQty($productMock));
    }

    /**
     * @dataProvider getVariationWizardDataProvider
     * @param string $wizardBlockName
     * @param string $wizardHtml
     * @throws Exception
     * @return void
     */
    public function testGetVariationWizard($wizardBlockName, $wizardHtml): void
    {
        $initData = ['some-key' => 'some-value'];
        $wizardName = 'variation-steps-wizard';
        $blockConfig = [
            'config' => [
                'nameStepWizard' => $wizardName
            ]
        ];

        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $wizardBlock = $this->createMock(StepsWizard::class);
        $layout->expects($this->any())->method('getChildName')->with(null, $wizardName)
            ->willReturn($wizardBlockName);
        $layout->expects($this->any())->method('getBlock')->with($wizardBlockName)->willReturn($wizardBlock);
        $wizardBlock->expects($this->any())->method('setInitData')->with($initData);
        $wizardBlock->expects($this->any())->method('toHtml')->willReturn($wizardHtml);

        $data = [
            'jsonHelper' => $this->createMock(JsonHelper::class),
            'directoryHelper' => $this->createMock(DirectoryHelper::class)
        ];
        $matrix = new Matrix(
            $this->context,
            $this->_configurableType,
            $this->stockRegistry,
            $this->variationMatrix,
            $this->productRepository,
            $this->image,
            $this->localeCurrency,
            $this->locator,
            $data
        );

        $matrix->setLayout($layout);
        $matrix->setData($blockConfig);

        $this->assertEquals($wizardHtml, $matrix->getVariationWizard($initData));
    }

    /**
     * @return array
     */
    public static function getVariationWizardDataProvider(): array
    {
        return [['WizardBlockName', 'WizardHtml'], ['', '']];
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
        $store = $this->createMock(Store::class);
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
        $this->_configurableType->expects($this->exactly(2))
            ->method('getUsedProductAttributes')
            ->with($product)
            ->willReturn([$attribute]);
        $this->_configurableType->expects($this->once())->method('getConfigurableAttributesAsArray')
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
        $this->image->expects($this->once())
            ->method('init')
            ->with($product, 'product_thumbnail_image')
            ->willReturn($image);
        $request = $this->createMock(RequestInterface::class);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);

        $data = [
            'jsonHelper' => $this->createMock(JsonHelper::class),
            'directoryHelper' => $this->createMock(DirectoryHelper::class)
        ];
        $matrix = new Matrix(
            $this->context,
            $this->_configurableType,
            $this->stockRegistry,
            $this->variationMatrix,
            $this->productRepository,
            $this->image,
            $this->localeCurrency,
            $this->locator,
            $data
        );
        $expected = [
            0 => [
                'productId' => 1,
                'images' => [
                    'preview' => 'image_url'
                ],
                'sku' => 'sku',
                'name' => 'name',
                'quantity' => 1,
                'price' => 100.0,
                'options' => [
                    0 => [
                        'attribute_code' => 'attribute_code',
                        'attribute_label' => null,
                        'id' => 'attribute_value',
                        'label' => 'attribute_label',
                        'value' => 'attribute_value',
                        '__disableTmpl' => true
                    ]
                ],
                'weight' => 1,
                'status' => 1,
                '__disableTmpl' => true
            ]
        ];
        $this->assertSame($expected, $matrix->getProductMatrix());
    }
}
