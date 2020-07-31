<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit\SaveHandler\Redis;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Session\SaveHandler\Redis\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var \Magento\Framework\App\Config|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config::class);

        $objectManager = new ObjectManager($this);
        $this->config = $objectManager->getObject(
            Config::class,
            [
                'deploymentConfig' => $this->deploymentConfigMock,
                'appState' => $this->appStateMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testGetLogLevel()
    {
        $expected = 2;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_LOG_LEVEL)
            ->willReturn($expected);
        $this->assertEquals($this->config->getLogLevel(), $expected);
    }

    public function testGetHost()
    {
        $expected = '127.0.0.1';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_HOST)
            ->willReturn($expected);
        $this->assertEquals($this->config->getHost(), $expected);
    }

    public function testGetPort()
    {
        $expected = 1234;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_PORT)
            ->willReturn($expected);
        $this->assertEquals($this->config->getPort(), $expected);
    }

    public function testGetDatabase()
    {
        $expected = 2;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_DATABASE)
            ->willReturn($expected);
        $this->assertEquals($this->config->getDatabase(), $expected);
    }

    public function testGetPassword()
    {
        $expected = 'password';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_PASSWORD)
            ->willReturn($expected);
        $this->assertEquals($this->config->getPassword(), $expected);
    }

    public function testGetTimeout()
    {
        $expected = 10;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_TIMEOUT)
            ->willReturn($expected);
        $this->assertEquals($this->config->getTimeout(), $expected);
    }

    public function testGetPersistentIdentifier()
    {
        $expected = 'sess01';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_PERSISTENT_IDENTIFIER)
            ->willReturn($expected);
        $this->assertEquals($this->config->getPersistentIdentifier(), $expected);
    }

    public function testGetCompressionThreshold()
    {
        $expected = 2;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_COMPRESSION_THRESHOLD)
            ->willReturn($expected);
        $this->assertEquals($this->config->getCompressionThreshold(), $expected);
    }

    public function testGetCompressionLibrary()
    {
        $expected = 'gzip';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_COMPRESSION_LIBRARY)
            ->willReturn($expected);
        $this->assertEquals($this->config->getCompressionLibrary(), $expected);
    }

    public function testGetMaxConcurrency()
    {
        $expected = 6;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_MAX_CONCURRENCY)
            ->willReturn($expected);
        $this->assertEquals($this->config->getMaxConcurrency(), $expected);
    }

    public function testGetMaxLifetime()
    {
        $this->assertEquals($this->config->getMaxLifetime(), Config::SESSION_MAX_LIFETIME);
    }

    public function testGetMinLifetime()
    {
        $expected = 30;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_MIN_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getMinLifetime(), $expected);
    }

    public function testGetDisableLocking()
    {
        $expected = false;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_DISABLE_LOCKING)
            ->willReturn($expected);
        $this->assertEquals($this->config->getDisableLocking(), $expected);
    }

    public function testGetBotLifetime()
    {
        $expected = 30;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_BOT_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getBotLifetime(), $expected);
    }

    public function testGetBotFirstLifetime()
    {
        $expected = 30;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_BOT_FIRST_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getBotFirstLifetime(), $expected);
    }

    public function testGetFirstLifetime()
    {
        $expected = 30;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_FIRST_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getFirstLifetime(), $expected);
    }

    public function testBreakAfter()
    {
        $areaCode = 'frontend';
        $breakAfter = 5;
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_BREAK_AFTER . '_' . $areaCode)
            ->willReturn($breakAfter);
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->assertEquals($this->config->getBreakAfter(), $breakAfter);
    }

    public function testGetLifetimeAdmin()
    {
        $areaCode = 'adminhtml';
        $expectedLifetime = 123;
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_ADMIN_SESSION_LIFETIME)
            ->willReturn($expectedLifetime);
        $this->assertEquals($this->config->getLifetime(), $expectedLifetime);
    }

    public function testGetLifetimeFrontend()
    {
        $areaCode = 'frontend';
        $expectedLifetime = 234;
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_COOKIE_LIFETIME,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($expectedLifetime);
        $this->assertEquals($this->config->getLifetime(), $expectedLifetime);
    }

    public function testGetSentinelServers()
    {
        $expected = 'server-1,server-2';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_SENTINEL_SERVERS)
            ->willReturn($expected);
        $this->assertEquals($expected, $this->config->getSentinelServers());
    }

    public function testGetSentinelMaster()
    {
        $expected = 'master';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_SENTINEL_MASTER)
            ->willReturn($expected);
        $this->assertEquals($this->config->getSentinelMaster(), $expected);
    }

    public function testGetSentinelVerifyMaster()
    {
        $expected = '1';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_SENTINEL_VERIFY_MASTER)
            ->willReturn($expected);
        $this->assertEquals($this->config->getSentinelVerifyMaster(), $expected);
    }

    public function testGetSentinelConnectRetries()
    {
        $expected = '10';
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturn(Config::PARAM_SENTINEL_CONNECT_RETRIES)
            ->willReturn($expected);
        $this->assertEquals($this->config->getSentinelConnectRetries(), $expected);
    }

    public function testGetFailAfter()
    {
        $this->assertEquals($this->config->getFailAfter(), Config::DEFAULT_FAIL_AFTER);
    }
}
