<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Signifyd\Model\SignifydOrderSessionId;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SignifydOrderSessionIdTest tests that SignifydOrderSessionId class dependencies
 * follow the contracts.
 */
class SignifydOrderSessionIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SignifydOrderSessionId
     */
    private $signifydOrderSessionId;

    /**
     * @var IdentityGeneratorInterface|MockObject
     */
    private $identityGenerator;

    /**
     * Sets up testing class and dependency mocks.
     */
    protected function setUp()
    {
        $this->identityGenerator = $this->getMockBuilder(IdentityGeneratorInterface::class)
            ->getMockForAbstractClass();

        $this->signifydOrderSessionId = new SignifydOrderSessionId($this->identityGenerator);
    }

    /**
     * Tests method by passing quoteId parameter
     *
     * @covers \Magento\Signifyd\Model\SignifydOrderSessionId::get
     */
    public function testGetByQuoteId()
    {
        $quoteId = 1;
        $signifydOrderSessionId = 'asdfzxcv';

        $this->identityGenerator->expects(static::once())
            ->method('generateIdForData')
            ->with($quoteId)
            ->willReturn($signifydOrderSessionId);

        $this->assertEquals(
            $signifydOrderSessionId,
            $this->signifydOrderSessionId->get($quoteId)
        );
    }
}
