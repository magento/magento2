<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GraphQl\Model\Query\Context;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Resolver\Cart;
use Magento\QuoteGraphQl\Model\Resolver\MaskedCartId;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    use MockCreationTrait;

    private const MASKED_CART_ID = 'maskedCartId0123456789abcdefABCD';

    /**
     * @var GetCartForUser|MockObject
     */
    private GetCartForUser $getCartForUser;

    /**
     * @var Quote|Stub
     */
    private Quote $quote;

    /**
     * @var Context|Stub
     */
    private Context $context;

    /**
     * @var Cart
     */
    private Cart $cartResolver;

    protected function setUp(): void
    {
        $this->getCartForUser = $this->createMock(GetCartForUser::class);
        $this->quote = $this->createStub(Quote::class);
        $this->quote->method('getId')->willReturn(5);

        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $extensionAttributes = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['setStore', 'getStore']
        );
        $extensionAttributes->expects($this->once())->method('getStore')->willReturn($store);
        $this->context = $this->createStub(Context::class);
        $this->context->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->context->method('getUserId')->willReturn(null);

        $this->cartResolver = new Cart($this->getCartForUser);
    }

    public function testResolvePassesRequestedMaskedIdToChildResolvers(): void
    {
        $this->getCartForUser->expects($this->once())
            ->method('execute')
            ->with(self::MASKED_CART_ID, null, 1)
            ->willReturn($this->quote);

        $result = $this->cartResolver->resolve(
            $this->createStub(Field::class),
            $this->context,
            $this->createStub(ResolveInfo::class),
            null,
            ['cart_id' => self::MASKED_CART_ID]
        );

        $this->assertSame($this->quote, $result['model']);
        $this->assertSame(self::MASKED_CART_ID, $result['masked_id'] ?? null);
    }

    public function testCartIdFieldDoesNotLookUpMaskedIdAgain(): void
    {
        $this->getCartForUser->expects($this->once())->method('execute')->willReturn($this->quote);
        $quoteIdToMaskedQuoteId = $this->createMock(QuoteIdToMaskedQuoteIdInterface::class);
        $quoteIdToMaskedQuoteId->expects($this->never())->method('execute');
        $quoteIdMaskFactory = $this->createMock(QuoteIdMaskFactory::class);
        $quoteIdMaskFactory->expects($this->never())->method('create');
        $quoteIdMaskResourceModel = $this->createMock(QuoteIdMaskResourceModel::class);
        $quoteIdMaskResourceModel->expects($this->never())->method('save');
        $maskedCartIdResolver = new MaskedCartId(
            $quoteIdToMaskedQuoteId,
            $quoteIdMaskFactory,
            $quoteIdMaskResourceModel
        );

        $cartValue = $this->cartResolver->resolve(
            $this->createStub(Field::class),
            $this->context,
            $this->createStub(ResolveInfo::class),
            null,
            ['cart_id' => self::MASKED_CART_ID]
        );
        $cartId = $maskedCartIdResolver->resolve(
            $this->createStub(Field::class),
            $this->context,
            $this->createStub(ResolveInfo::class),
            $cartValue
        );

        $this->assertSame(self::MASKED_CART_ID, $cartId);
    }
}
