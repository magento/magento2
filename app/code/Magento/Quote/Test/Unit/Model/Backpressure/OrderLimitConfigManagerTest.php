<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Backpressure;

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
    private $config;

    /**
     * @var OrderLimitConfigManager
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ScopeConfigInterface::class);

        $this->model = new OrderLimitConfigManager($this->config);
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
     */
    public function testReadLimit(
        int $identityType,
        int $guestLimit,
        int $authLimit,
        int $period,
        int $expectedLimit,
        int $expectedPeriod
    ): void {
        $this->initConfig($guestLimit, $authLimit, $period, true);
        $context = $this->createContext($identityType);

        $limit = $this->model->readLimit($context);
        $this->assertEquals($expectedLimit, $limit->getLimit());
        $this->assertEquals($expectedPeriod, $limit->getPeriod());
    }

    /**
     * Config variations for enabled check.
     *
     * @return array
     */
    public function getEnabledCases(): array
    {
        return [
            'disabled' => [100, 100, 60, false, false],
            'guest-misconfigured-1' => [0, 100, 60, true, false],
            'auth-misconfigured-1' => [10, -1, 60, true, false],
            'period-misconfigured-1' => [10, 111, 0, true, false],
            'enabled' => [10, 111, 60, true, true]
        ];
    }

    /**
     * Verify logic behind enabled check.
     *
     * @param int $guestLimit
     * @param int $authLimit
     * @param int $period
     * @param bool $enabled
     * @param bool $expected
     * @return void
     * @dataProvider getEnabledCases
     */
    public function testIsEnforcementEnabled(
        int $guestLimit,
        int $authLimit,
        int $period,
        bool $enabled,
        bool $expected
    ): void {
        $this->initConfig($guestLimit, $authLimit, $period, $enabled);

        $this->assertEquals($expected, $this->model->isEnforcementEnabled());
    }

    /**
     * Initialize config mock.
     *
     * @param int $guest
     * @param int $auth
     * @param int $period
     * @param bool $enabled
     * @return void
     */
    private function initConfig(int $guest, int $auth, int $period, bool $enabled): void
    {
        $this->config->method('getValue')
            ->willReturnCallback(
                function (string $path) use ($auth, $guest, $period): ?string {
                    switch ($path) {
                        case 'sales/backpressure/limit':
                            return (string) $auth;
                        case 'sales/backpressure/guest_limit':
                            return (string) $guest;
                        case 'sales/backpressure/period':
                            return (string) $period;
                    }

                    return null;
                }
            );
        $this->config->method('isSetFlag')
            ->willReturnCallback(
                function (string $path) use ($enabled): bool {
                    if ($path === 'sales/backpressure/enabled') {
                        return $enabled;
                    }

                    return false;
                }
            );
    }

    /**
     * Create backpressure context.
     *
     * @param int $identityType
     * @return ContextInterface
     */
    private function createContext(int $identityType): ContextInterface
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('getIdentityType')->willReturn($identityType);

        return $context;
    }
}
