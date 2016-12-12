<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Signifyd\Model\OrderSessionId;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class OrderSessionIdTest extends \PHPUnit_Framework_TestCase
{
    const QUOTE_ID = 1;
    const QUOTE_CREATED_AT = '1970-01-01 00:00:00';
    const HASH = 'hash';

    /**
     * @var OrderSessionId
     */
    private $orderSessionId;

    /**
     * @var SessionManagerInterface|MockObject
     */
    private $session;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var IdentityGeneratorInterface|MockObject
     */
    private $identityGenerator;

    protected function setUp()
    {
        $this->session = $this->getMockBuilder(SessionManagerInterface::class)
            ->setMethods(['getQuote'])
            ->getMockForAbstractClass();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityGenerator = $this->getMockBuilder(IdentityGeneratorInterface::class)
            ->getMockForAbstractClass();

        $this->orderSessionId = new OrderSessionId($this->session, $this->identityGenerator);
    }

    public function testGenerate()
    {
        $this->session->expects(static::once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects(static::once())
            ->method('getId')
            ->willReturn(self::QUOTE_ID);
        $this->quote->expects(static::once())
            ->method('getCreatedAt')
            ->willReturn(self::QUOTE_CREATED_AT);

        $this->identityGenerator->expects(static::once())
            ->method('generateIdForData')
            ->willReturn('hash');

        static::assertSame(self::HASH, $this->orderSessionId->generate());
    }
}
