<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \PHPUnit_Framework_TestCase
     */
    protected $testCase;

    /**
     * Initialize helper
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     */
    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * Return mocks with expected invokes
     *
     * @param $maskedCartId
     * @param $cartId
     * @return array
     */
    public function mockQuoteIdMask(
        $maskedCartId,
        $cartId
    ) {
        $quoteIdMaskMock = $this->testCase->getMock('Magento\Quote\Model\QuoteIdMask', [], [], '', false);
        $quoteIdMaskFactoryMock = $this->testCase->getMock('Magento\Quote\Model\QuoteIdMaskFactory', [], [], '', false);

        $quoteIdMaskFactoryMock->expects($this->testCase->once())->method('create')->willReturn(
            $quoteIdMaskMock
        );
        $quoteIdMaskMock->expects($this->testCase->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->testCase->once())
            ->method('getId')
            ->willReturn($cartId);

        return [$quoteIdMaskFactoryMock,$quoteIdMaskMock];
    }
}
