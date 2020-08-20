<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use PHPUnit\Framework\TestCase;

/**
 * Test for checkout cart model.
 *
 * @see \Magento\Checkout\Model\Cart
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CartFactory */
    private $cartFactory;

    /** @var ProductInterfaceFactory */
    private $productFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var CartInterface */
    private $quote;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartFactory = $this->objectManager->get(CartFactory::class);
        $this->productFactory = $this->objectManager->get(ProductInterfaceFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
        $this->checkoutSession = $this->objectManager->get(CheckoutSessionFactory::class)->create();
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->quote instanceof CartInterface) {
            $this->quoteRepository->delete($this->quote);
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/set_product_min_in_cart.php
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testAddProductWithLowerQty(): void
    {
        $cart = $this->cartFactory->create();
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage((string)__('The fewest you may purchase is %1', 3));
        $product = $this->productRepository->get('simple');
        $cart->addProduct($product->getId(), ['qty' => 1]);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/set_product_min_in_cart.php
     *
     * @return void
     */
    public function testAddProductWithNoQty(): void
    {
        $cart = $this->cartFactory->create();
        $product = $this->productRepository->get('simple');
        $cart->addProduct($product->getId(), [])->save();
        $this->quote = $cart->getQuote();
        $this->assertCount(1, $cart->getItems());
        $this->assertEquals($product->getId(), $this->checkoutSession->getLastAddedProductId());
    }

    /**
     * @return void
     */
    public function testAddNotExistingProduct(): void
    {
        $product = $this->productFactory->create();
        $this->expectExceptionObject(
            new LocalizedException(__('The product wasn\'t found. Verify the product and try again.'))
        );
        $this->cartFactory->create()->addProduct($product);
    }

    /**
     * @return void
     */
    public function testAddNotExistingProductId(): void
    {
        $this->expectExceptionObject(
            new LocalizedException(__('The product wasn\'t found. Verify the product and try again.'))
        );
        $this->cartFactory->create()->addProduct(989);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     *
     * @return void
     */
    public function testAddProductFromUnavailableWebsite(): void
    {
        $product = $this->productRepository->get('simple');
        $this->expectExceptionObject(
            new LocalizedException(__('The product wasn\'t found. Verify the product and try again.'))
        );
        $this->executeInStoreContext
            ->execute('fixture_second_store', [$this->cartFactory->create(), 'addProduct'], $product->getId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     *
     * @return void
     */
    public function testAddProductWithInvalidRequest(): void
    {
        $product = $this->productRepository->get('simple');
        $message = __('We found an invalid request for adding product to quote.');
        $this->expectExceptionObject(new LocalizedException($message));
        $this->cartFactory->create()->addProduct($product->getId(), '');
    }
}
