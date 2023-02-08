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
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
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
    private QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId;

    /**
     * @var \Magento\QuoteGraphQl\Test\Unit\Model\Resolver\QuoteIdMaskFactory|MockObject
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
     * @var Quote|MockObject
     */
    private Quote $quoteMock;

    /**
     * @var QuoteIdMask|MockObject
     */
    private QuoteIdMask $quoteIdMask;

    /**
     * @var array
     */
    private array $valueMock = [];

    protected function setUp(): void
    {
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->quoteIdToMaskedQuoteId = $this->createPartialMock(
            QuoteIdToMaskedQuoteIdInterface::class,
            ['execute']
        );
        $this->quoteIdMaskFactory = $this->createPartialMock(
            QuoteIdMaskFactory::class,
            ['create']
        );
        $this->quoteIdMaskResourceModelMock = $this->getMockBuilder(QuoteIdMaskResourceModel::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setQuoteId',
                ]
            )
            ->onlyMethods(['save'])
            ->getMock();
        $this->maskedCartId = new MaskedCartId(
            $this->quoteIdToMaskedQuoteId,
            $this->quoteIdMaskFactory,
            $this->quoteIdMaskResourceModelMock
        );
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteIdMask = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $this->maskedCartId->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    public function testResolve(): void
    {
        $this->valueMock = ['model' => $this->quoteMock];
        $cartId = 1;
        $this->quoteMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);
        $this->quoteIdMaskFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteIdMask);
        $this->quoteIdMask->setQuoteId($cartId);
        $this->quoteIdMaskResourceModelMock
            ->expects($this->once())
            ->method('save')
            ->with($this->quoteIdMask);
        $this->maskedCartId->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }
}
