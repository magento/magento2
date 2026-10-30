<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Plugin\Model\Message;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Plugin\Model\Message\AddRelatedProductsToSuccessMessagePlugin;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddRelatedProductsToSuccessMessagePluginTest extends TestCase
{
    /**
     * @var Http&MockObject
     */
    private $request;

    /**
     * @var ProductRepositoryInterface&MockObject
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface&MockObject
     */
    private $storeManager;

    /**
     * @var CheckoutSession&MockObject
     */
    private $checkoutSession;

    /**
     * @var AddRelatedProductsToSuccessMessagePlugin
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Http::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->plugin = new AddRelatedProductsToSuccessMessagePlugin(
            $this->request,
            $this->productRepository,
            $this->storeManager,
            $this->checkoutSession
        );
    }

    /**
     * @param int $itemsCount
     * @return void
     */
    private function mockCartItemsCount(int $itemsCount): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getAllVisibleItems')->willReturn(array_fill(0, $itemsCount, new \stdClass()));
        $this->checkoutSession->method('getQuote')->willReturn($quote);
    }

    public function testBeforeAddComplexSuccessMessageWithoutRelatedProducts(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $this->request->method('getParam')->with('related_product')->willReturn(null);

        $this->assertNull(
            $this->plugin->beforeAddComplexSuccessMessage(
                $manager,
                'addCartSuccessMessage',
                ['product_name' => 'Main Product']
            )
        );
    }

    public function testBeforeAddComplexSuccessMessageWithRelatedProducts(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $store = $this->createMock(StoreInterface::class);
        $relatedProduct = $this->createMock(Product::class);

        $this->request->method('getFullActionName')->willReturn('checkout_cart_add');
        $this->request->method('getParam')->with('related_product')->willReturn('2');
        $this->mockCartItemsCount(2);
        $this->storeManager->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(1);
        $relatedProduct->method('getName')->willReturn('Related Product');
        $this->productRepository->method('getById')->with(2, false, 1)->willReturn($relatedProduct);

        $result = $this->plugin->beforeAddComplexSuccessMessage(
            $manager,
            'addCartSuccessMessage',
            ['product_name' => 'Main Product']
        );

        $this->assertSame('addCartSuccessMessage', $result[0]);
        $this->assertSame('Main Product and Related Product', $result[1]['product_name']);
    }

    public function testBeforeAddSuccessMessageWithRelatedProducts(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $store = $this->createMock(StoreInterface::class);
        $relatedProduct = $this->createMock(Product::class);

        $this->request->method('getFullActionName')->willReturn('checkout_cart_add');
        $this->request->method('getParam')->with('related_product')->willReturn('2');
        $this->mockCartItemsCount(2);
        $this->storeManager->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(1);
        $relatedProduct->method('getName')->willReturn('Related Product');
        $this->productRepository->method('getById')->with(2, false, 1)->willReturn($relatedProduct);

        $result = $this->plugin->beforeAddSuccessMessage(
            $manager,
            new Phrase('You added %1 to your shopping cart.', ['Main Product'])
        );

        $this->assertInstanceOf(Phrase::class, $result[0]);
        $this->assertSame('Main Product and Related Product', $result[0]->getArguments()[0]);
    }
}
