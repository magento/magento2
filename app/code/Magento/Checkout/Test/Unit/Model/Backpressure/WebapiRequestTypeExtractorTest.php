<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Backpressure;

use Magento\Checkout\Model\Backpressure\WebapiRequestTypeExtractor;
use Magento\Quote\Model\Backpressure\OrderLimitConfigManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the WebapiRequestTypeExtractor class
 */
class WebapiRequestTypeExtractorTest extends TestCase
{
    /**
     * @var OrderLimitConfigManager|MockObject
     */
    private $orderLimitConfigManagerMock;

    /**
     * @var WebapiRequestTypeExtractor
     */
    private WebapiRequestTypeExtractor $webapiRequestTypeExtractor;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderLimitConfigManagerMock = $this->createMock(OrderLimitConfigManager::class);

        $this->webapiRequestTypeExtractor = new WebapiRequestTypeExtractor($this->orderLimitConfigManagerMock);
    }

    /**
     * @param bool $isEnforcementEnabled
     * @param string $method
     * @param string|null $expected
     * @dataProvider dataProvider
     */
    public function testExtract(bool $isEnforcementEnabled, string $method, $expected)
    {
        $this->orderLimitConfigManagerMock->method('isEnforcementEnabled')->willReturn($isEnforcementEnabled);

        $this->assertEquals(
            $expected,
            $this->webapiRequestTypeExtractor->extract('someService', $method, 'someEndpoint')
        );
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [false, 'someMethod', null],
            [false, 'savePaymentInformationAndPlaceOrder', null],
            [true, 'savePaymentInformationAndPlaceOrder', 'quote-order'],
        ];
    }
}
