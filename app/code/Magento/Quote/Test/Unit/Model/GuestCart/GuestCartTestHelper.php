<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

/**
 * Class GuestCartTestHelper
 *
 */
class GuestCartTestHelper
{
    /**
     * @var \PHPUnit\Framework\TestCase
     */
    protected $testCase;

    /**
     * Initialize helper
     *
     * @param \PHPUnit\Framework\TestCase $testCase
     */
    public function __construct(\PHPUnit\Framework\TestCase $testCase)
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
        $quoteIdMaskMock = $this->testCase->getMockBuilder(\Magento\Quote\Model\QuoteIdMask::class)
            ->setMethods(['load', 'getQuoteId', 'getMaskedId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteIdMaskFactoryMock = $this->testCase->getMockBuilder(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteIdMaskFactoryMock->expects($this->testCase->once())->method('create')->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->testCase->once())->method('load')->with($maskedCartId)->willReturnSelf();
        $quoteIdMaskMock->expects($this->testCase->once())->method('getQuoteId')->willReturn($cartId);
        return [$quoteIdMaskFactoryMock, $quoteIdMaskMock];
    }
}
