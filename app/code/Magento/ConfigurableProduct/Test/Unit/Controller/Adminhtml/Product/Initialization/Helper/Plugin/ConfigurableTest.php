<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Configurable;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\Catalog\Test\Unit\Helper\ProductExtensionTestHelper;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    /**
     * @var VariationHandler|MockObject
     */
    private $variationHandler;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var Factory|MockObject
     */
    private $optionFactory;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Helper|MockObject
     */
    private $subject;

    /**
     * @var Configurable
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->variationHandler = $this->createPartialMock(
            VariationHandler::class,
            ['generateSimpleProducts', 'prepareAttributeSet']
        );

        $this->request = $this->createPartialMock(Http::class, ['getParam', 'getPost']);

        $this->optionFactory = $this->createPartialMock(Factory::class, ['create']);

        $this->product = $this->createMock(Product::class);

        $this->subject = $this->createMock(Helper::class);

        $this->plugin = new Configurable(
            $this->variationHandler,
            $this->request,
            $this->optionFactory
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterInitializeWithAttributesAndVariations()
    {
        $attributes = [
            ['attribute_id' => 90, 'values' => [
                ['value_index' => 12], ['value_index' => 13]
            ]]
        ];
        $valueMap = [
            ['new-variations-attribute-set-id', null, 24],
            ['associated_product_ids_serialized', '[]', []],
            ['product', [], ['configurable_attributes_data' => $attributes]],
        ];
        $simpleProductsIds = [1, 2, 3];
        $simpleProducts = [
            [
                'newProduct' => false,
                'variationKey' => 'simple1'
            ],
            [
                'newProduct' => true,
                'variationKey' => 'simple2',
                'status' => 'simple2_status',
                'sku' => 'simple2_sku',
                'name' => 'simple2_name',
                'price' => '3.33',
                'configurable_attribute' => 'simple2_configurable_attribute',
                'weight' => '5.55',
                'media_gallery' => 'simple2_media_gallery',
                'swatch_image' => 'simple2_swatch_image',
                'small_image' => 'simple2_small_image',
                'thumbnail' => 'simple2_thumbnail',
                'image' => 'simple2_image'
            ],
            [
                'newProduct' => true,
                'variationKey' => 'simple3',
                'qty' => '3'
            ]
        ];
        $variationMatrix = [
            'simple2' => [
                'status' => 'simple2_status',
                'sku' => 'simple2_sku',
                'name' => 'simple2_name',
                'price' => '3.33',
                'configurable_attribute' => 'simple2_configurable_attribute',
                'weight' => '5.55',
                'media_gallery' => 'simple2_media_gallery',
                'swatch_image' => 'simple2_swatch_image',
                'small_image' => 'simple2_small_image',
                'thumbnail' => 'simple2_thumbnail',
                'image' => 'simple2_image'
            ],
            'simple3' => [
                'quantity_and_stock_status' => ['qty' => '3']
            ]
        ];
        $paramValueMap = [
            ['configurable-matrix-serialized', "[]", json_encode($simpleProducts)],
            ['attributes', null, $attributes],
        ];

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableProduct::TYPE_CODE);

        $this->request->expects(static::any())
            ->method('getPost')
            ->willReturnMap($valueMap);

        $this->request->expects(static::any())
            ->method('getParam')
            ->willReturnMap($paramValueMap);

        $extensionAttributes = new ProductExtensionTestHelper();
        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionFactory->expects(static::once())
            ->method('create')
            ->with($attributes)
            ->willReturn($attributes);

        $this->variationHandler->expects(static::once())
            ->method('prepareAttributeSet')
            ->with($this->product);

        $this->variationHandler->expects(static::once())
            ->method('generateSimpleProducts')
            ->with($this->product, $variationMatrix)
            ->willReturn($simpleProductsIds);

        $this->product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $this->plugin->afterInitialize($this->subject, $this->product);
    }

    public function testAfterInitializeWithAttributesAndWithoutVariations()
    {
        $attributes = [
            ['attribute_id' => 90, 'values' => [
                ['value_index' => 12], ['value_index' => 13]
            ]]
        ];
        $valueMap = [
            ['new-variations-attribute-set-id', null, 24],
            ['associated_product_ids_serialized', "[]", "[]"],
            ['product', [], ['configurable_attributes_data' => $attributes]],
        ];
        $paramValueMap = [
            ['configurable-matrix-serialized', "[]", "[]"],
            ['attributes', null, $attributes],
        ];

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableProduct::TYPE_CODE);

        $this->request->expects(static::any())
            ->method('getPost')
            ->willReturnMap($valueMap);

        $this->request->expects(static::any())
            ->method('getParam')
            ->willReturnMap($paramValueMap);

        $extensionAttributes = new ProductExtensionTestHelper();
        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionFactory->expects(static::once())
            ->method('create')
            ->with($attributes)
            ->willReturn($attributes);

        $this->variationHandler->expects(static::never())
            ->method('prepareAttributeSet');

        $this->variationHandler->expects(static::never())
            ->method('generateSimpleProducts');

        // Helper directly implements setConfigurableProductLinks

        $this->product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $this->plugin->afterInitialize($this->subject, $this->product);
    }

    public function testAfterInitializeIfAttributesEmpty()
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableProduct::TYPE_CODE);
        $this->request->expects(static::once())
            ->method('getParam')
            ->with('attributes')
            ->willReturn([]);
        $this->product->expects(static::never())
            ->method('getExtensionAttributes');
        $this->request->expects(static::once())
            ->method('getPost');
        $this->variationHandler->expects(static::never())
            ->method('prepareAttributeSet');
        $this->variationHandler->expects(static::never())
            ->method('generateSimpleProducts');
        $this->plugin->afterInitialize($this->subject, $this->product);
    }

    public function testAfterInitializeForNotConfigurableProduct()
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('non-configurable');
        $this->product->expects(static::never())
            ->method('getExtensionAttributes');
        $this->request->expects(static::once())
            ->method('getPost');
        $this->variationHandler->expects(static::never())
            ->method('prepareAttributeSet');
        $this->variationHandler->expects(static::never())
            ->method('generateSimpleProducts');
        $this->plugin->afterInitialize($this->subject, $this->product);
    }
}
