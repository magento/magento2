<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Resolver\Cart;
use Magento\QuoteGraphQl\Model\Resolver\MaskedCartId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MaskedCartIdTest extends TestCase
{
    /**
     * @var MaskedCartId
     */
    private MaskedCartId $maskedCartId;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface|MockObject
     */
    private QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteIdMock;

    /**
     * @var QuoteIdMaskFactory|MockObject
     */
    private QuoteIdMaskFactory $quoteIdMaskFactoryMock;

    /**
     * @var QuoteIdMaskResourceModel|MockObject
     */
    private QuoteIdMaskResourceModel $quoteIdMaskResourceModelMock;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var Context|MockObject
     */
    private Context $contextMock;

    /**
     * @var cart|MockObject
     */
    private Cart $cartMock;

    /**
     * @var array
     */
    private array $valueMock = [];

    protected function setUp(): void
    {
        $this->quoteIdToMaskedQuoteIdMock = $this->createMock(QuoteIdToMaskedQuoteIdInterface::class);
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->quoteIdMaskResourceModelMock = $this->createMock(QuoteIdMaskResourceModel::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();
        $this->quoteIdMaskResourceModelMock = $this->getMockBuilder(QuoteIdMaskResourceModel::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getQuoteMaskId',
                    'ensureQuoteMaskExist'
                ]
            )
            ->getMock();
        $this->maskedCartId = new MaskedCartId(
            $this->quoteIdToMaskedQuoteIdMock,
            $this->quoteIdMaskFactoryMock,
            $this->quoteIdMaskResourceModelMock
        );
    }
    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $this->maskedCartId->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    public function testResolve(): void
    {
        $this->valueMock = ['model' => $this->valueMock];
        $this->cartMock
            ->expects($this->any())
            ->method('getId');
        $this->quoteIdMaskResourceModelMock
            ->expects($this->any())
            ->method('getQuoteMaskId')
            ->willReturn('maskedId');
        $this->quoteIdMaskResourceModelMock
            ->expects($this->any())
            ->method('ensureQuoteMaskExist')
            ->willReturn('maskedId');

    }
}


