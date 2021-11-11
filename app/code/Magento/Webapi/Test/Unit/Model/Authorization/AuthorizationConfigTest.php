<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Authorization;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Webapi\Model\Authorization\AuthorizationConfig;
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
            ->with('webapi/authorization/enable_integration_as_bearer')
            ->willReturn(true);

        self::assertTrue($this->config->isIntegrationAsBearerEnabled());
    }

    public function testDisabled()
    {
        $this->scopeConfig->method('isSetFlag')
            ->with('webapi/authorization/enable_integration_as_bearer')
            ->willReturn(false);

        self::assertFalse($this->config->isIntegrationAsBearerEnabled());
    }
}
