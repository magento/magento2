<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Quote\Test\Unit\Model\GuestCart\QuoteIdMaskFactoryTestHelper;
use Magento\Quote\Test\Unit\Model\GuestCart\QuoteIdMaskTestHelper;
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
        $quoteIdMask = new QuoteIdMaskTestHelper();
        $quoteIdMask->load($maskedCartId)->setQuoteIdForTest($cartId);
        $quoteIdMaskFactory = new QuoteIdMaskFactoryTestHelper($quoteIdMask);
        return [$quoteIdMaskFactory, $quoteIdMask];
    }
}
