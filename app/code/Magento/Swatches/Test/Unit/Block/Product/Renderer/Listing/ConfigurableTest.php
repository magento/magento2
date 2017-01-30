<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Block\Product\Renderer\Listing;

use Magento\Swatches\Block\Product\Renderer\Configurable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /** @var Configurable */
    private $configurable;

    /** @var \Magento\Framework\Stdlib\ArrayUtils|\PHPUnit_Framework_MockObject_MockObject */
    private $arrayUtils;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $jsonEncoder;

    /** @var \Magento\ConfigurableProduct\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $helper;

    /** @var \Magento\Swatches\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $swatchHelper;

    /** @var \Magento\Swatches\Helper\Media|\PHPUnit_Framework_MockObject_MockObject */
    private $swatchMediaHelper;

    /** @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject */
    private $catalogProduct;

    /** @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject */
    private $currentCustomer;

    /** @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $priceCurrency;

    /** @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeData|\PHPUnit_Framework_MockObject_MockObject */
    private $configurableAttributeData;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    private $product;

    /** @var \Magento\Catalog\Model\Product\Type\AbstractType|\PHPUnit_Framework_MockObject_MockObject */
    private $typeInstance;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeConfig;

    /** @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject */
    private $imageHelper;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject  */
    private $urlBuilder;

    public function setUp()
    {
        $this->arrayUtils = $this->getMock('\Magento\Framework\Stdlib\ArrayUtils', [], [], '', false);
        $this->jsonEncoder = $this->getMock('\Magento\Framework\Json\EncoderInterface', [], [], '', false);
        $this->helper = $this->getMock('\Magento\ConfigurableProduct\Helper\Data', [], [], '', false);
        $this->swatchHelper = $this->getMock('\Magento\Swatches\Helper\Data', [], [], '', false);
        $this->swatchMediaHelper = $this->getMock('\Magento\Swatches\Helper\Media', [], [], '', false);
        $this->catalogProduct = $this->getMock('\Magento\Catalog\Helper\Product', [], [], '', false);
        $this->currentCustomer = $this->getMock('\Magento\Customer\Helper\Session\CurrentCustomer', [], [], '', false);
        $this->priceCurrency = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface', [], [], '', false);
        $this->configurableAttributeData = $this->getMock(
            'Magento\ConfigurableProduct\Model\ConfigurableAttributeData',
            [],
            [],
            '',
            false
        );
        $this->product = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->typeInstance = $this->getMock('\Magento\Catalog\Model\Product\Type\AbstractType', [], [], '', false);
        $this->scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $this->imageHelper = $this->getMock('\Magento\Catalog\Helper\Image', [], [], '', false);
        $this->urlBuilder = $this->getMock('\Magento\Framework\UrlInterface');

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configurable = $objectManagerHelper->getObject(
            '\Magento\Swatches\Block\Product\Renderer\Listing\Configurable',
            [
                'scopeConfig' => $this->scopeConfig,
                'imageHelper' => $this->imageHelper,
                'urlBuilder' => $this->urlBuilder,
                'arrayUtils' => $this->arrayUtils,
                'jsonEncoder' => $this->jsonEncoder,
                'helper' => $this->helper,
                'swatchHelper' => $this->swatchHelper,
                'swatchMediaHelper' => $this->swatchMediaHelper,
                'catalogProduct' => $this->catalogProduct,
                'currentCustomer' => $this->currentCustomer,
                'priceCurrency' => $this->priceCurrency,
                'configurableAttributeData' => $this->configurableAttributeData,
                'data' => [],
            ]
        );
    }

    /**
     * @covers Magento\Swatches\Block\Product\Renderer\Listing\Configurable::getSwatchAttributesData
     */
    public function testGetJsonSwatchConfigWithoutSwatches()
    {
        $this->prepareGetJsonSwatchConfig();
        $this->configurable->setProduct($this->product);
        $this->swatchHelper->expects($this->once())->method('getSwatchAttributesAsArray')
            ->with($this->product)
            ->willReturn([]);
        $this->swatchHelper->expects($this->once())->method('getSwatchesByOptionsId')
            ->willReturn([]);
        $this->jsonEncoder->expects($this->once())->method('encode')->with([]);
        $this->configurable->getJsonSwatchConfig();
    }

    /**
     * @covers Magento\Swatches\Block\Product\Renderer\Listing\Configurable::getSwatchAttributesData
     */
    public function testGetJsonSwatchNotUsedInProductListing()
    {
        $this->prepareGetJsonSwatchConfig();
        $this->configurable->setProduct($this->product);
        $this->swatchHelper->expects($this->once())->method('getSwatchAttributesAsArray')
            ->with($this->product)
            ->willReturn([
                1 => [
                    'options' => [1 => 'testA', 3 => 'testB'],
                    'use_product_image_for_swatch' => true,
                    'used_in_product_listing' => false,
                    'attribute_code' => 'code',
                ],
            ]);
        $this->swatchHelper->expects($this->once())->method('getSwatchesByOptionsId')
            ->willReturn([]);
        $this->jsonEncoder->expects($this->once())->method('encode')->with([]);
        $this->configurable->getJsonSwatchConfig();
    }

    /**
     * @covers Magento\Swatches\Block\Product\Renderer\Listing\Configurable::getSwatchAttributesData
     */
    public function testGetJsonSwatchUsedInProductListing()
    {
        $products = [
            1 => 'testA',
            3 => 'testB'
        ];
        $expected =
            [
                'type' => null,
                'value' => 'hello',
                'label' => $products[3]
            ];
        $this->prepareGetJsonSwatchConfig();
        $this->configurable->setProduct($this->product);
        $this->swatchHelper->expects($this->once())->method('getSwatchAttributesAsArray')
            ->with($this->product)
            ->willReturn([
                1 => [
                    'options' => $products,
                    'use_product_image_for_swatch' => true,
                    'used_in_product_listing' => true,
                    'attribute_code' => 'code',
                ],
            ]);
        $this->swatchHelper->expects($this->once())->method('getSwatchesByOptionsId')
            ->with([1, 3])
            ->willReturn([
                3 => ['type' => $expected['type'], 'value' => $expected['value']]
            ]);
        $this->jsonEncoder->expects($this->once())->method('encode');
        $this->configurable->getJsonSwatchConfig();
    }

    private function prepareGetJsonSwatchConfig()
    {
        $product1 = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $product1->expects($this->atLeastOnce())->method('isSaleable')->willReturn(true);
        $product1->expects($this->any())->method('getData')->with('code')->willReturn(1);

        $product2 = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $product2->expects($this->atLeastOnce())->method('isSaleable')->willReturn(true);
        $product2->expects($this->any())->method('getData')->with('code')->willReturn(3);

        $simpleProducts = [$product1, $product2];
        $configurableType = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );
        $configurableType->expects($this->atLeastOnce())->method('getUsedProducts')->with($this->product, null)
            ->willReturn($simpleProducts);
        $this->product->expects($this->any())->method('getTypeInstance')->willReturn($configurableType);

        $productAttribute1 = $this->getMock('\Magento\Eav\Model\Entity\Attribute\AbstractAttribute', [], [], '', false);
        $productAttribute1->expects($this->any())->method('getId')->willReturn(1);
        $productAttribute1->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $attribute1 = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            ['getProductAttribute'],
            [],
            '',
            false
        );
        $attribute1->expects($this->any())->method('getProductAttribute')->willReturn($productAttribute1);

        $this->helper->expects($this->any())->method('getAllowAttributes')->with($this->product)
            ->willReturn([$attribute1]);
    }
}
