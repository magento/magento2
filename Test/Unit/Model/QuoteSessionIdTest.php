<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Signifyd\Model\QuoteSession\QuoteSessionInterface;
use Magento\Signifyd\Model\QuoteSessionId;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class QuoteSessionIdTest tests that QuoteSessionId class dependencies
 * follow the contracts.
 */
class QuoteSessionIdTest extends \PHPUnit_Framework_TestCase
{
    const QUOTE_ID = 1;

    /**
     * @var QuoteSessionId
     */
    private $quoteSessionId;

    /**
     * @var QuoteSessionInterface|MockObject
     */
    private $quoteSession;

    /**
     * @var IdentityGeneratorInterface|MockObject
     */
    private $identityGenerator;

    /**
     * Sets up testing class and dependency mocks.
     */
    protected function setUp()
    {
        $this->quoteSession = $this->getMockBuilder(QuoteSessionInterface::class)
            ->getMockForAbstractClass();

        $this->identityGenerator = $this->getMockBuilder(IdentityGeneratorInterface::class)
            ->getMockForAbstractClass();

        $this->quoteSessionId = new QuoteSessionId($this->quoteSession, $this->identityGenerator);
    }

    /**
     * Tests method by passing quoteId parameter
     *
     * @covers \Magento\Signifyd\Model\QuoteSessionId::get
     */
    public function testGetByQuoteId()
    {
        $this->identityGenerator->expects(static::once())
            ->method('generateIdForData');

        $this->quoteSessionId->get(self::QUOTE_ID);
    }

    /**
     * Tests method by getting quoteId from session
     *
     * @covers \Magento\Signifyd\Model\QuoteSessionId::get
     */
    public function testGetByQuoteSession()
    {
        $quote = $this->getMockBuilder(CartInterface::class)
            ->getMockForAbstractClass();

        $this->identityGenerator->expects(static::once())
            ->method('generateIdForData');

        $this->quoteSession->expects(static::once())
            ->method('getQuote')
            ->willReturn($quote);
        $quote->expects(static::once())
            ->method('getId');

        $this->quoteSessionId->get();
    }
}
