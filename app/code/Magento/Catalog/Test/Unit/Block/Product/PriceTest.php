<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Price;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\Escaper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Catalog\Block\Product\Price
 */
class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    private Price $block;

    /**
     * @var CartHelper|MockObject
     */
    private $cartHelperMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private $jsonEncoderMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var StringUtils|MockObject
     */
    private $stringMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var Random|MockObject
     */
    private $mathRandomMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->cartHelperMock = $this->createMock(CartHelper::class);
        $this->jsonEncoderMock = $this->createMock(EncoderInterface::class);
        $this->stringMock = $this->createMock(StringUtils::class);
        $this->mathRandomMock = $this->createMock(Random::class);
        $this->registryMock = $this->createMock(Registry::class);
        $catalogDataMock = $this->createMock(CatalogData::class);

        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);

        $this->block = $objectManager->getObject(
            Price::class,
            [
                'context' => $this->contextMock,
                'cartHelper' => $this->cartHelperMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'string' => $this->stringMock,
                'mathRandom' => $this->mathRandomMock,
                'registry' => $this->registryMock,
                'catalogData' => $catalogDataMock
            ]
        );
    }

    public function testGetIdentities(): void
    {
        $productTags = ['catalog_product_1'];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')->willReturn($productTags);
        $this->block->setProduct($product);
        $this->assertSame($productTags, $this->block->getIdentities());
    }

    /**
     * Unit test for getDisplayMinimalPrice() method
     *
     * @return void
     */
    public function testGetDisplayMinimalPrice(): void
    {
        $expectedPrice = 123.45;
        $this->block->setData('display_minimal_price', $expectedPrice);
        $this->assertSame($expectedPrice, $this->block->getDisplayMinimalPrice());
    }

    /**
     * Unit test for getDisplayMinimalPrice() method
     *
     * @return void
     */
    public function testGetDisplayMinimalPriceReturnsEmpty(): void
    {
        $this->assertEmpty($this->block->getDisplayMinimalPrice());
    }

    /**
     * Unit test for getIdSuffix() method when no ID suffix is set
     *
     * @return void
     */
    public function testGetIdSuffixReturnEmpty(): void
    {
        $this->assertEmpty($this->block->getIdSuffix());
    }

    /**
     * Unit test for getIdSuffix() method when ID suffix is set
     *
     * @return void
     */
    public function testGetIdSuffixReturnsExpectedValue(): void
    {
        $expectedSuffix = '_suffix';
        $this->block->setIdSuffix($expectedSuffix);
        $this->assertSame($expectedSuffix, $this->block->getIdSuffix());
    }

    /**
     * Unit test for getAddToCartUrl() method
     *
     * @return void
     */
    public function testGetAddToCartUrl(): void
    {
        $expectedUrl = 'http://example.com/addtocart';
        $productMock = $this->createMock(Product::class);
        $this->cartHelperMock->expects($this->once())->method('getAddUrl')->willReturn($expectedUrl);
        $this->assertSame($expectedUrl, $this->block->getAddToCartUrl($productMock));
    }

    /**
     * Unit test for getRealPriceJs() method
     *
     * @return void
     */
    public function testGetRealPriceJs(): void
    {
        $priceHtml = '<span>100.00</span>';
        $expectedJsonEncodedValue = '"<span>100.00<\/span>"';

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getRealPriceHtml'])
            ->getMock();

        $productMock->expects($this->once())->method('getRealPriceHtml')->willReturn($priceHtml);
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($priceHtml)
            ->willReturn($expectedJsonEncodedValue);

        $this->assertSame($expectedJsonEncodedValue, $this->block->getRealPriceJs($productMock));
    }

    /**
     * Unit test for prepareSku() method
     *
     * @return void
     */
    public function testPrepareSku(): void
    {
        $sku = 'test-product';
        $this->stringMock->expects($this->once())->method('splitInjection')->willReturn($sku);
        $this->escaperMock->expects($this->once())->method('escapeHtml')->willReturn($sku);
        $this->assertSame($sku, $this->block->prepareSku($sku));
    }

    /**
     * Unit test for getRandomString() method
     *
     * @return void
     */
    public function testGetMathRandomString(): void
    {
        $randomString = 'abc123xyz';
        $this->mathRandomMock->expects($this->once())->method('getRandomString')->willReturn($randomString);
        $this->assertSame($randomString, $this->block->getRandomString(10));
    }

    /**
     * Unit test for getProduct() when registry provides the product
     *
     * @return void
     */
    public function testGetProduct(): void
    {
        $productMock = $this->createMock(Product::class);
        $this->registryMock->expects($this->once())->method('registry')->willReturn($productMock);
        $this->assertSame($productMock, $this->block->getProduct());
    }

    /**
     * Unit test for _toHtml() using data provider
     *
     * @dataProvider toHtmlProvider
     * @param bool|null $productCanShowPrice
     * @return void
     */
    public function testToHtml(?bool $productCanShowPrice): void
    {
        if ($productCanShowPrice === null) {
            $this->registryMock->method('registry')->with('product')->willReturn(null);
        } else {
            $productMock = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->addMethods(['getCanShowPrice'])
                ->getMock();
            $productMock->method('getCanShowPrice')->willReturn($productCanShowPrice);
            $this->registryMock->method('registry')->with('product')->willReturn($productMock);
        }

        $method = new \ReflectionMethod(Price::class, '_toHtml');
        $result = $method->invoke($this->block);

        $this->assertSame('', $result);
    }

    /**
     * Data provider for testToHtmlReturnsEmptyForNoProductOrCantShowPrice()
     *
     * @return array
     */
    public static function toHtmlProvider(): array
    {
        return [
            'no_product' => [null],
            'product_cannot_show_price' => [false],
            'product_can_show_price' => [true],
        ];
    }
}
