<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\QuoteGraphQl\Model\AddProductsToCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\CartItem\PrecursorInterface;
use PHPUnit\Framework\TestCase;

class AddProductsToCartTest extends TestCase
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

    private function createSubject(?CartItemFactory $cartItemFactory = null): AddProductsToCart
    {
        $dependencies = [
            $this->createMock(GetCartForUser::class),
            $this->createMock(AddProductsToCartService::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(PrecursorInterface::class),
        ];
        if ($cartItemFactory !== null) {
            $dependencies[] = $cartItemFactory;
        }

        return new AddProductsToCart(...$dependencies);
    }

    private function getCartItemFactory(AddProductsToCart $subject): CartItemFactory
    {
        $property = new \ReflectionProperty(AddProductsToCart::class, 'cartItemFactory');
        return $property->getValue($subject);
    }
}