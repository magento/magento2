<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model\InstantPurchase\CreditCard;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\InstantPurchase\CreditCard\AvailabilityChecker;

/**
 * @covers \Magento\Braintree\Model\InstantPurchase\CreditCard\AvailabilityChecker
 */
class AvailabilityCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var AvailabilityChecker
     */
    private $availabilityChecker;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->availabilityChecker = new AvailabilityChecker($this->configMock);
    }

    /**
     * Test isAvailable method
     *
     * @dataProvider isAvailableDataProvider
     *
     * @param bool $isVerify3DSecure
     * @param bool $expected
     *
     * @return void
     */
    public function testIsAvailable(bool $isVerify3DSecure, bool $expected)
    {
        $this->configMock->expects($this->once())->method('isVerify3DSecure')->willReturn($isVerify3DSecure);
        $actual = $this->availabilityChecker->isAvailable();
        self::assertEquals($expected, $actual);
    }

    /**
     * Data provider for isAvailable method test
     *
     * @return array
     */
    public function isAvailableDataProvider()
    {
        return [
            [true, false],
            [false, true],
        ];
    }
}
