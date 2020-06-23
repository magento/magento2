<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Session;

use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SuccessValidatorTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testIsValid()
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidWithNotEmptyGetLastSuccessQuoteId()
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();

        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->willReturn(
            1
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->willReturn(0);

        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidWithEmptyQuoteAndOrder()
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();
        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->willReturn(
            1
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->willReturn(1);

        $checkoutSession->expects($this->at(2))->method('__call')->with('getLastOrderId')->willReturn(0);

        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    public function testIsValidTrue()
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();
        $checkoutSession->expects(
            $this->at(0)
        )->method(
            '__call'
        )->with(
            'getLastSuccessQuoteId'
        )->willReturn(
            1
        );

        $checkoutSession->expects($this->at(1))->method('__call')->with('getLastQuoteId')->willReturn(1);

        $checkoutSession->expects($this->at(2))->method('__call')->with('getLastOrderId')->willReturn(1);

        $this->assertTrue($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    /**
     * @param MockObject $checkoutSession
     * @return object
     */
    protected function createSuccessValidator(MockObject $checkoutSession)
    {
        return $this->objectManagerHelper->getObject(
            SuccessValidator::class,
            ['checkoutSession' => $checkoutSession]
        );
    }
}
