<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Test\Unit\SaveHandler\Redis;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Session\SaveHandler\Redis\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->appStateMock = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config::class, [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
}
