<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Plugin;

use Magento\Analytics\Plugin\BearerTokenValidatorPlugin;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Validator\BearerTokenValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BearerTokenValidatorPluginTest extends TestCase
{
    /**
     * @var BearerTokenValidatorPlugin
     */
    private BearerTokenValidatorPlugin $plugin;

    /**
     * @var BearerTokenValidator|MockObject
     */
    private $validator;

    public function setUp(): void
    {
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->method('getValue')
            ->with('analytics/integration_name')
            ->willReturn('abc');
        $this->plugin = new BearerTokenValidatorPlugin($config);
        $this->validator = $this->createMock(BearerTokenValidator::class);
    }

    public function testTrueIsPassedThrough()
    {
        $integration = $this->createMock(Integration::class);
        $integration->method('__call')
            ->with('getName')
            ->willReturn('invalid');

        $result = $this->plugin->afterIsIntegrationAllowedAsBearerToken($this->validator, true, $integration);
        self::assertTrue($result);
    }

    public function testFalseWhenIntegrationDoesntMatch()
    {
        $integration = $this->createMock(Integration::class);
        $integration->method('__call')
            ->with('getName')
            ->willReturn('invalid');

        $result = $this->plugin->afterIsIntegrationAllowedAsBearerToken($this->validator, false, $integration);
        self::assertFalse($result);
    }

    public function testTrueWhenIntegrationMatches()
    {
        $integration = $this->createMock(Integration::class);
        $integration->method('__call')
            ->with('getName')
            ->willReturn('abc');

        $result = $this->plugin->afterIsIntegrationAllowedAsBearerToken($this->validator, true, $integration);
        self::assertTrue($result);
    }
}
