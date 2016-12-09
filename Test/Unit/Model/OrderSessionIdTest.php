<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Signifyd\Model\OrderSessionId;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class OrderSessionIdTest extends \PHPUnit_Framework_TestCase
{
    const QUOTE_ID = 1;
    const QUOTE_CREATED_AT = '1970-01-01 00:00:00';
    const HASH = 'ede3c2f59fabe6dee8d1fefb5580200884ff1f16';

    /**
     * @var OrderSessionId
     */
    private $orderSessionId;

    /**
     * @var Session|MockObject
     */
    private $checkoutSession;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    public function setUp()
    {
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderSessionId = new OrderSessionId($this->checkoutSession);
    }

    public function testGenerate()
    {
        $this->checkoutSession->expects(static::once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects(static::once())
            ->method('getId')
            ->willReturn(self::QUOTE_ID);
        $this->quote->expects(static::once())
            ->method('getCreatedAt')
            ->willReturn(self::QUOTE_CREATED_AT);

        static::assertSame(self::HASH, $this->orderSessionId->generate());
    }
}
