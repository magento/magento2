<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Block\Product\Renderer\Listing;

use \Magento\Swatches\Block\Product\Renderer\Listing\Configurable;
use Magento\Swatches\Model\SwatchAttributesProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
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

    /** @var SwatchAttributesProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $swatchAttributesProvider;

    /** @var \Magento\Catalog\Block\Product\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var \Magento\Framework\View\Element\Template\File\Resolver|\PHPUnit_Framework_MockObject_MockObject */
    private $resolver;

    /** @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject */
    private $eventManager;

    /** @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $cacheState;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $directory;

    /** @var \Magento\Framework\View\Element\Template\File\Validator|\PHPUnit_Framework_MockObject_MockObject */
    private $validator;

    /** @var  \Magento\Framework\View\TemplateEnginePool|\PHPUnit_Framework_MockObject_MockObject */
    private $templateEnginePool;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->arrayUtils = $this->getMock(\Magento\Framework\Stdlib\ArrayUtils::class, [], [], '', false);
        $this->jsonEncoder = $this->getMock(\Magento\Framework\Json\EncoderInterface::class, [], [], '', false);
        $this->helper = $this->getMock(\Magento\ConfigurableProduct\Helper\Data::class, [], [], '', false);
        $this->swatchHelper = $this->getMock(\Magento\Swatches\Helper\Data::class, [], [], '', false);
        $this->swatchMediaHelper = $this->getMock(\Magento\Swatches\Helper\Media::class, [], [], '', false);
        $this->catalogProduct = $this->getMock(\Magento\Catalog\Helper\Product::class, [], [], '', false);
        $this->currentCustomer = $this->getMock(
            \Magento\Customer\Helper\Session\CurrentCustomer::class,
            [],
            [],
            '',
            false
        );
        $this->priceCurrency = $this->getMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class,
            [],
            [],
            '',
            false
        );
        $this->configurableAttributeData = $this->getMock(
            \Magento\ConfigurableProduct\Model\ConfigurableAttributeData::class,
            [],
            [],
            '',
            false
        );
        $this->product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->typeInstance = $this->getMock(
            \Magento\Catalog\Model\Product\Type\AbstractType::class,
            [],
            [],
            '',
            false
        );
        $this->scopeConfig = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            [],
            '',
            false
        );
        $this->imageHelper = $this->getMock(\Magento\Catalog\Helper\Image::class, [], [], '', false);
        $this->urlBuilder = $this->getMock(\Magento\Framework\UrlInterface::class);

        $this->swatchAttributesProvider = self::getMockBuilder(SwatchAttributesProvider::class)
            ->setMethods(['provide'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = self::getMockBuilder(\Magento\Catalog\Block\Product\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManager =  self::getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = self::getMockBuilder(\Magento\Framework\View\Element\Template\File\Resolver::class)
            ->setMethods(['getTemplateFileName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheState = self::getMockBuilder(\Magento\Framework\App\Cache\StateInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->directory = self::getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validator = self::getMockBuilder(\Magento\Framework\View\Element\Template\File\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateEnginePool = self::getMockBuilder(
            \Magento\Framework\View\TemplateEnginePool::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())->method('getResolver')->willReturn($this->resolver);
        $this->contextMock->expects($this->once())->method('getEventManager')->willReturn($this->eventManager);
        $this->contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->contextMock->expects($this->once())->method('getCacheState')->willReturn($this->cacheState);
        $this->contextMock->expects($this->once())->method('getValidator')->willReturn($this->validator);
        $this->contextMock->expects($this->once())->method('getEnginePool')->willReturn($this->templateEnginePool);

        $this->configurable = $objectManagerHelper->getObject(
            \Magento\Swatches\Block\Product\Renderer\Listing\Configurable::class,
            [
                'context' => $this->contextMock,
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
                'swatchAttributesProvider' => $this->swatchAttributesProvider,
                'data' => [],
            ]
        );

        $objectManagerHelper->setBackwardCompatibleProperty($this->configurable, 'directory', $this->directory);
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

    /**
     * @return void
     */
    private function prepareGetJsonSwatchConfig()
    {
        $product1 = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product1->expects($this->any())->method('getData')->with('code')->willReturn(1);

        $product2 = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product2->expects($this->any())->method('getData')->with('code')->willReturn(3);

        $simpleProducts = [$product1, $product2];
        $configurableType = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            [],
            [],
            '',
            false
        );
        $configurableType->expects($this->atLeastOnce())->method('getSalableUsedProducts')
            ->with($this->product, null)
            ->willReturn($simpleProducts);
        $this->product->expects($this->any())->method('getTypeInstance')->willReturn($configurableType);

        $productAttribute1 = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            [],
            [],
            '',
            false
        );
        $productAttribute1->expects($this->any())->method('getId')->willReturn(1);
        $productAttribute1->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $attribute1 = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class,
            ['getProductAttribute'],
            [],
            '',
            false
        );
        $attribute1->expects($this->any())->method('getProductAttribute')->willReturn($productAttribute1);

        $this->helper->expects($this->any())->method('getAllowAttributes')->with($this->product)
            ->willReturn([$attribute1]);
    }

    /**
     * @return void
     */
    public function testToHtmlNoSwatches()
    {
        $this->swatchAttributesProvider->expects(self::atLeastOnce())
            ->method('provide')
            ->with($this->product)
            ->willReturn([]);

        $this->configurable->setProduct($this->product);

        self::assertEmpty($this->configurable->toHtml());
    }

    /**
     * @return void
     */
    public function testToHtmlSwatches()
    {
        $attribute = self::getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->swatchAttributesProvider->expects(self::atLeastOnce())
            ->method('provide')
            ->with($this->product)
            ->willReturn([$attribute]);

        $engine = self::getMockBuilder(\Magento\Framework\View\TemplateEngineInterface::class)
            ->getMockForAbstractClass();

        $engine->expects(self::atLeastOnce())
            ->method('render')
            ->with($this->configurable, 'product/listing/renderer.phtml')
            ->willReturn('<li>Swatches listing</li>');

        $this->templateEnginePool->expects(self::atLeastOnce())
            ->method('get')
            ->withAnyParameters()
            ->willReturn($engine);

        $this->configurable->setProduct($this->product);
        $this->configurable->setTemplate('product/listing/renderer.phtml');
        $this->configurable->setArea('frontend');

        $this->resolver->expects(self::atLeastOnce())
            ->method('getTemplateFileName')
            ->willReturn('product/listing/renderer.phtml');

        $this->directory->expects(self::atLeastOnce())
            ->method('getRelativePath')
            ->with('product/listing/renderer.phtml')
            ->willReturn('product/listing/renderer.phtml');

        $this->validator->expects(self::atLeastOnce())
            ->method('isValid')
            ->with('product/listing/renderer.phtml')
            ->willReturn(true);

        $html = $this->configurable->toHtml();

        self::assertNotEmpty($html);
        self::assertEquals('<li>Swatches listing</li>', $html);
    }
}
