<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Configurable;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\ConfigurableProduct\Test\Unit\Model\Product\ProductExtensionAttributes;
use Magento\Framework\App\Request\Http;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ConfigurableTest
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Magento\ConfigurableProduct\Model\Product\VariationHandler|MockObject
     */
    private $variationHandler;

    /**
     * @var Magento\Framework\App\Request\Http|MockObject
     */
    private $request;

    /**
     * @var Magento\ConfigurableProduct\Helper\Product\Options\Factory|MockObject
     */
    private $optionFactory;

    /**
     * @var Magento\Catalog\Model\Product|MockObject
     */
    private $product;

    /**
     * @var Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper|MockObject
     */
    private $subject;

    /**
     * @var Configurable
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->variationHandler = $this->getMockBuilder(VariationHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateSimpleProducts', 'prepareAttributeSet'])
            ->getMock();

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam', 'getPost'])
            ->getMock();

        $this->optionFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getTypeId', 'setAttributeSetId', 'getExtensionAttributes', 'setNewVariationsAttributeSetId',
                'setCanSaveConfigurableAttributes', 'setExtensionAttributes'
            ])
            ->getMock();

        $this->subject = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ['configurable-matrix-serialized', '[]', json_encode($simpleProducts)],
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

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMockForAbstractClass();
        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionFactory->expects(static::once())
            ->method('create')
            ->with($attributes)
            ->willReturn($attributes);

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductOptions')
            ->with($attributes);

        $this->variationHandler->expects(static::once())
            ->method('prepareAttributeSet')
            ->with($this->product);

        $this->variationHandler->expects(static::once())
            ->method('generateSimpleProducts')
            ->with($this->product, $variationMatrix)
            ->willReturn($simpleProductsIds);

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductLinks')
            ->with($simpleProductsIds);

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
            ['associated_product_ids_serialized', '[]', []],
            ['product', [], ['configurable_attributes_data' => $attributes]],
        ];
        $paramValueMap = [
            ['configurable-matrix-serialized', '[]', []],
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

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMockForAbstractClass();
        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionFactory->expects(static::once())
            ->method('create')
            ->with($attributes)
            ->willReturn($attributes);

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductOptions')
            ->with($attributes);

        $this->variationHandler->expects(static::never())
            ->method('prepareAttributeSet');

        $this->variationHandler->expects(static::never())
            ->method('generateSimpleProducts');

        $extensionAttributes->expects(static::once())
            ->method('setConfigurableProductLinks');

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
