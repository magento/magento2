<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Model\Config\AuthorizationConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var AuthorizationConfig
     */
    private $config;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->config = new AuthorizationConfig($this->scopeConfig);
    }

    public function testEnabled()
    {
        $this->scopeConfig->method('isSetFlag')
            ->with('oauth/consumer/enable_integration_as_bearer')
            ->willReturn(true);

        self::assertTrue($this->config->isIntegrationAsBearerEnabled());
    }

    public function testDisabled()
    {
        $this->scopeConfig->method('isSetFlag')
            ->with('oauth/consumer/enable_integration_as_bearer')
            ->willReturn(false);

        self::assertFalse($this->config->isIntegrationAsBearerEnabled());
    }
}
