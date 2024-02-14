<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\TestCase;

class GuestCartTestHelper
{
    /**
     * @var TestCase
     */
    protected $testCase;

    /**
     * Initialize helper
     *
     * @param TestCase $testCase
     */
    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * Return mocks with expected invokes
     *
     * First element is quoteIdMaskFactoryMock, second one is quoteIdMaskMock
     *
     * @param $maskedCartId
     * @param $cartId
     * @return array
     */
    public function mockQuoteIdMask($maskedCartId, $cartId)
    {
        $quoteIdMaskMock = $this->testCase->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId', 'getMaskedId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteIdMaskFactoryMock = $this->testCase->getMockBuilder(QuoteIdMaskFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteIdMaskFactoryMock->expects($this->testCase->once())->method('create')->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->testCase->once())->method('load')->with($maskedCartId)->willReturnSelf();
        $quoteIdMaskMock->expects($this->testCase->once())->method('getQuoteId')->willReturn($cartId);
        return [$quoteIdMaskFactoryMock, $quoteIdMaskMock];
    }
}
