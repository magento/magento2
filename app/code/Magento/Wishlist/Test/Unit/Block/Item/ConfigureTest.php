<?php declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Wishlist\Test\Unit\Block\Item;

use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Wishlist\Block\Item\Configure;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigureTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Configure
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $wishlistDataMock;

    protected function setUp(): void
    {
        $this->wishlistDataMock = $this->createMock(Data::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $escaperMock = $this->createMock(Escaper::class);
        $escaperMock->method('escapeHtml')
            ->willReturnCallback(
                function ($string) {
                    return 'escapeHtml' . $string;
                }
            );
        $this->contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($escaperMock);

        $this->model = new Configure(
            $this->contextMock,
            $this->wishlistDataMock,
            $this->registryMock
        );
    }

    public function testGetWishlistOptions()
    {
        $typeId = 'simple';
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getTypeId')->willReturn($typeId);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($product);

        $this->assertEquals(['productType' => 'escapeHtml' . $typeId], $this->model->getWishlistOptions());
    }

    public function testGetProduct()
    {
        $product = 'some test product';
        $this->registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'product'
        )->willReturn(
            $product
        );

        $this->assertEquals($product, $this->model->getProduct());
    }

    public function testSetLayout()
    {
        $layoutMock = $this->createMock(LayoutInterface::class);

        $blockMock = $this->createPartialMockWithReflection(
            AbstractBlock::class,
            ['setCustomAddToCartUrl']
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn($blockMock);

        $itemMock = $this->createMock(Item::class);

        $this->registryMock->expects($this->exactly(2))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn($itemMock);

        $this->wishlistDataMock->expects($this->once())
            ->method('getAddToCartUrl')
            ->with($itemMock)
            ->willReturn('some_url');

        $blockMock->expects($this->once())
            ->method('setCustomAddToCartUrl')
            ->with('some_url');

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithNoItem()
    {
        $layoutMock = $this->createMock(LayoutInterface::class);

        $blockMock = $this->createPartialMockWithReflection(
            AbstractBlock::class,
            ['setCustomAddToCartUrl']
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn($blockMock);

        $this->registryMock->expects($this->exactly(1))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn(null);

        $this->wishlistDataMock->expects($this->never())
            ->method('getAddToCartUrl');

        $blockMock->expects($this->never())
            ->method('setCustomAddToCartUrl');

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithNoBlockAndItem()
    {
        $layoutMock = $this->createMock(LayoutInterface::class);

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn(null);

        $this->registryMock->expects($this->never())
            ->method('registry');

        $this->wishlistDataMock->expects($this->never())
            ->method('getAddToCartUrl');

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }
}
