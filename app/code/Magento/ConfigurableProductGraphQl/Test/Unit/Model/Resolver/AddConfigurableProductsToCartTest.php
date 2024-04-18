<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Test\Unit\Model\Resolver;

use Magento\ConfigurableProductGraphQl\Model\Resolver\AddConfigurableProductsToCart;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Quote\Model\QuoteMutexInterface;
use Magento\QuoteGraphQl\Model\Cart\AddProductsToCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for AddConfigurableProductsToCart
 */
class AddConfigurableProductsToCartTest extends TestCase
{
    /**
     * @var GetCartForUser|MockObject
     */
    private $getCartForUser;

    /**
     * @var AddProductsToCart|MockObject
     */
    private $addProductsToCart;

    /**
     * @var QuoteMutexInterface|MockObject
     */
    private $quoteMutex;

    /**
     * @var AddConfigurableProductsToCart
     */
    private $resolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->getCartForUser = $this->createMock(GetCartForUser::class);
        $this->addProductsToCart = $this->createMock(AddProductsToCart::class);
        $this->quoteMutex = $this->createMock(QuoteMutexInterface::class);

        $this->resolver = new AddConfigurableProductsToCart(
            $this->getCartForUser,
            $this->addProductsToCart,
            $this->quoteMutex
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolve()
    {
        $maskedId = 'maskedId';
        $args = ['input' => ['cart_id' => $maskedId, 'cart_items' => ['item1', 'item2']]];
        $field = $this->createMock(Field::class);
        $context = $this->createMock(ContextInterface::class);
        $info = $this->createMock(ResolveInfo::class);
        $this->quoteMutex->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($maskedIds, $callable, $actualArgs) use ($maskedId, $args, $context) {
                $this->assertEquals([$maskedId], $maskedIds);
                $this->assertTrue(is_callable($callable));
                $this->assertEquals([$context, $args], $actualArgs);
            });
        $this->resolver->resolve($field, $context, $info, null, $args);
    }
}
