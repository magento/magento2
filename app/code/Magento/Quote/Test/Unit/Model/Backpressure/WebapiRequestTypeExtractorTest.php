<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Backpressure;

use Magento\Quote\Model\Backpressure\WebapiRequestTypeExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Quote\Model\Backpressure\OrderLimitConfigManager;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;

/**
 * Tests the WebapiRequestTypeExtractor class
 */
class WebapiRequestTypeExtractorTest extends TestCase
{
    /**
     * @var OrderLimitConfigManager|MockObject
     */
    private $configManagerMock;

    /**
     * @var WebapiRequestTypeExtractor
     */
    private WebapiRequestTypeExtractor $typeExtractor;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configManagerMock = $this->createMock(OrderLimitConfigManager::class);
        $this->typeExtractor = new WebapiRequestTypeExtractor($this->configManagerMock);
    }

    /**
     * Tests CompositeRequestTypeExtractor
     *
     * @param string $service
     * @param string $method
     * @param bool $isEnforcementEnabled
     * @param mixed $expected
     * @dataProvider dataProvider
     */
    public function testExtract(string $service, string $method, bool $isEnforcementEnabled, $expected)
    {
        $this->configManagerMock->method('isEnforcementEnabled')
            ->willReturn($isEnforcementEnabled);

        $this->assertEquals($expected, $this->typeExtractor->extract($service, $method, 'someEndPoint'));
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            ['wrongService', 'wrongMethod', false, null],
            [CartManagementInterface::class, 'wrongMethod', false, null],
            [GuestCartManagementInterface::class, 'wrongMethod', false, null],
            [GuestCartManagementInterface::class, 'placeOrder', false, null],
            [GuestCartManagementInterface::class, 'placeOrder', true, 'quote-order'],
        ];
    }
}
