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
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
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
    private QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId;

    /**
     * @var QuoteIdMaskFactory|MockObject
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

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
        $this->quoteIdToMaskedQuoteId = $this->createPartialMock(
            QuoteIdToMaskedQuoteIdInterface::class,
            ['execute']
        );
        $this->quoteIdMaskFactory = $this->createPartialMock(
            QuoteIdMaskFactory ::class,
            ['create']
        );
        $this->quoteIdMaskFactory = $this->getMockBuilder(QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['setQuoteId']
            )
            ->getMock();
        $this->quoteIdMaskResourceModelMock = $this->createMock(QuoteIdMaskResourceModel::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->quoteIdMaskResourceModelMock = $this->getMockBuilder(QuoteIdMaskResourceModel::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getQuoteMaskId',
                    'ensureQuoteMaskExist'
                ]
            )
            ->getMock();
        $this->cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->addMethods(['setQuoteId','getId'])
            ->getMock();
        $this->maskedCartId = new MaskedCartId(
            $this->quoteIdToMaskedQuoteId,
            $this->quoteIdMaskFactory,
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
        $this->valueMock = ['model' => $this->cartMock];
        $quoteIdMask = $this->createPartialMock(
            QuoteIdMaskResourceModel::class,
            ['setQuoteId']
        );
        $this->quoteIdToMaskedQuoteId
            ->expects($this->any())
            ->method('execute');
       // echo get_class($this->quoteIdMaskFactory);die;
       // $this->quoteIdMaskFactory->setQuoteId('maskId');
        $this->quoteIdMaskFactory->expects($this->once())
            ->method('create')
            ->willReturn($quoteIdMask);
        $this->maskedCartId->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }
}
