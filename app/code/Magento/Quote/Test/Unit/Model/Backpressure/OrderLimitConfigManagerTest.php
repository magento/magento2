<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Backpressure;

use Magento\Framework\Exception\RuntimeException;
use Magento\Quote\Model\Backpressure\OrderLimitConfigManager;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderLimitConfigManagerTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var OrderLimitConfigManager
     */
    private OrderLimitConfigManager $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->model = new OrderLimitConfigManager($this->scopeConfigMock);
    }

    /**
     * Different config variations.
     *
     * @return array
     */
    public function getConfigCases(): array
    {
        return [
            'guest' => [ContextInterface::IDENTITY_TYPE_IP, 100, 50, 60, 100, 60],
            'authed' => [ContextInterface::IDENTITY_TYPE_CUSTOMER, 100, 50, 3600, 50, 3600],
        ];
    }

    /**
     * Verify that limit config is read from store config.
     *
     * @param int $identityType
     * @param int $guestLimit
     * @param int $authLimit
     * @param int $period
     * @param int $expectedLimit
     * @param int $expectedPeriod
     * @return void
     * @dataProvider getConfigCases
     * @throws RuntimeException
     */
    public function testReadLimit(
        int $identityType,
        int $guestLimit,
        int $authLimit,
        int $period,
        int $expectedLimit,
        int $expectedPeriod
    ): void {
        $context = $this->createMock(ContextInterface::class);
        $context->method('getIdentityType')->willReturn($identityType);

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap(
                [
                    ['sales/backpressure/limit', 'store', null, $authLimit],
                    ['sales/backpressure/guest_limit', 'store', null, $guestLimit],
                    ['sales/backpressure/period', 'store', null, $period],
                ]
            );

        $limit = $this->model->readLimit($context);
        $this->assertEquals($expectedLimit, $limit->getLimit());
        $this->assertEquals($expectedPeriod, $limit->getPeriod());
    }

    /**
     * Verify logic behind enabled check
     *
     * @param bool $enabled
     * @param bool $expected
     * @return void
     * @dataProvider getEnabledCases
     */
    public function testIsEnforcementEnabled(
        bool    $enabled,
        bool    $expected
    ): void {
        $this->scopeConfigMock->method('isSetFlag')
            ->with('sales/backpressure/enabled')
            ->willReturn($enabled);

        $this->assertEquals($expected, $this->model->isEnforcementEnabled());
    }

    /**
     * Config variations for enabled check.
     *
     * @return array
     */
    public function getEnabledCases(): array
    {
        return [
            'disabled' => [false, false],
            'enabled' => [true, true],
        ];
    }
}
