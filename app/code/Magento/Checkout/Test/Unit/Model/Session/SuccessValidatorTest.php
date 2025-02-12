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
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @return void
     */
    public function testIsValid(): void
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    /**
     * @return void
     */
    public function testIsValidWithNotEmptyGetLastSuccessQuoteId(): void
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();

        $checkoutSession
            ->method('__call')
            ->willReturnCallback(fn($operation) => match ([$operation]) {
                ['getLastSuccessQuoteId'] => 1,
                ['getLastQuoteId'] => 0
            });
        
        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    /**
     * @return void
     */
    public function testIsValidWithEmptyQuoteAndOrder(): void
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();

        $checkoutSession
            ->method('__call')
            ->willReturnCallback(fn($operation) => match ([$operation]) {
                ['getLastSuccessQuoteId'] => 1,
                ['getLastQuoteId'] => 1,
                ['getLastOrderId'] => 0
            });

        $this->assertFalse($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    /**
     * @return void
     */
    public function testIsValidTrue(): void
    {
        $checkoutSession = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->getMock();

        $checkoutSession
            ->method('__call')
            ->willReturnCallback(fn($operation) => match ([$operation]) {
                ['getLastSuccessQuoteId'] => 1,
                ['getLastQuoteId'] => 1,
                ['getLastOrderId'] => 1
            });

        $this->assertTrue($this->createSuccessValidator($checkoutSession)->isValid($checkoutSession));
    }

    /**
     * @param MockObject $checkoutSession
     *
     * @return object
     */
    protected function createSuccessValidator(MockObject $checkoutSession): object
    {
        return $this->objectManagerHelper->getObject(
            SuccessValidator::class,
            ['checkoutSession' => $checkoutSession]
        );
    }
}
