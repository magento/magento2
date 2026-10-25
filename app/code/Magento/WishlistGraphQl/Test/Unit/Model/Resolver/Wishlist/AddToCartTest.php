<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\WishlistGraphQl\Test\Unit\Model\Resolver\Wishlist;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\Wishlist\AddProductsToWishlist as AddProductsToWishlistModel;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;
use Magento\WishlistGraphQl\Model\CartItems\CartItemsRequestBuilder;
use Magento\WishlistGraphQl\Model\Resolver\Wishlist\AddToCart;
use PHPUnit\Framework\TestCase;

class AddToCartTest extends TestCase
{
    private ObjectManagerInterface $originalObjectManager;

    protected function setUp(): void
    {
        $this->originalObjectManager = ObjectManager::getInstance();
    }

    protected function tearDown(): void
    {
        ObjectManager::setInstance($this->originalObjectManager);
        parent::tearDown();
    }

    public function testFactoryIsResolvedForLegacyConstructorCall(): void
    {
        $cartItemFactory = $this->createMock(CartItemFactory::class);
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->once())
            ->method('get')
            ->with(CartItemFactory::class)
            ->willReturn($cartItemFactory);
        ObjectManager::setInstance($objectManager);

        $subject = $this->createSubject();

        $this->assertSame($cartItemFactory, $this->getCartItemFactory($subject));
    }

    public function testUsesInjectedFactory(): void
    {
        $cartItemFactory = $this->createMock(CartItemFactory::class);

        $subject = $this->createSubject($cartItemFactory);

        $this->assertSame($cartItemFactory, $this->getCartItemFactory($subject));
    }

    private function createSubject(?CartItemFactory $cartItemFactory = null): AddToCart
    {
        $dependencies = [
            $this->createMock(WishlistResourceModel::class),
            $this->createMock(WishlistFactory::class),
            $this->createMock(WishlistConfig::class),
            $this->createMock(AddProductsToWishlistModel::class),
            $this->createMock(WishlistDataMapper::class),
            $this->createMock(LocaleQuantityProcessor::class),
            $this->createMock(CreateEmptyCartForCustomer::class),
            $this->createMock(AddProductsToCartService::class),
            $this->createMock(CartItemsRequestBuilder::class),
        ];
        if ($cartItemFactory !== null) {
            $dependencies[] = $cartItemFactory;
        }

        return new AddToCart(...$dependencies);
    }

    private function getCartItemFactory(AddToCart $subject): CartItemFactory
    {
        $property = new \ReflectionProperty(AddToCart::class, 'cartItemFactory');
        return $property->getValue($subject);
    }
}