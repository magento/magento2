<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Test\Unit\SaveHandler\Redis;

use Magento\Framework\Session\SaveHandler\Redis\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Session\SaveHandler\Redis\Config
     */
    protected $config;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->config = $objectManager->getObject(
            'Magento\Framework\Session\SaveHandler\Redis\Config',
            [
                'deploymentConfig' => $this->deploymentConfig,
                'appState' => $this->appState
            ]
        );
    }

    public function testGetLogLevel()
    {
        $expected = 2;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_LOG_LEVEL)
            ->willReturn($expected);
        $this->assertEquals($this->config->getLogLevel(), $expected);
    }

    public function testGetHost()
    {
        $expected = '127.0.0.1';
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_HOST)
            ->willReturn($expected);
        $this->assertEquals($this->config->getHost(), $expected);
    }

    public function testGetPort()
    {
        $expected = 1234;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_PORT)
            ->willReturn($expected);
        $this->assertEquals($this->config->getPort(), $expected);
    }

    public function testGetDatabase()
    {
        $expected = 2;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_DATABASE)
            ->willReturn($expected);
        $this->assertEquals($this->config->getDatabase(), $expected);
    }

    public function testGetPassword()
    {
        $expected = 'password';
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_PASSWORD)
            ->willReturn($expected);
        $this->assertEquals($this->config->getPassword(), $expected);
    }

    public function testGetTimeout()
    {
        $expected = 10;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_TIMEOUT)
            ->willReturn($expected);
        $this->assertEquals($this->config->getTimeout(), $expected);
    }

    public function testGetPersistentIdentifier()
    {
        $expected = 'sess01';
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_PERSISTENT_IDENTIFIER)
            ->willReturn($expected);
        $this->assertEquals($this->config->getPersistentIdentifier(), $expected);
    }

    public function testGetCompressionThreshold()
    {
        $expected = 2;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_COMPRESSION_THRESHOLD)
            ->willReturn($expected);
        $this->assertEquals($this->config->getCompressionThreshold(), $expected);
    }

    public function testGetCompressionLibrary()
    {
        $expected = 'gzip';
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_COMPRESSION_LIBRARY)
            ->willReturn($expected);
        $this->assertEquals($this->config->getCompressionLibrary(), $expected);
    }

    public function testGetMaxConcurrency()
    {
        $expected = 6;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_MAX_CONCURRENCY)
            ->willReturn($expected);
        $this->assertEquals($this->config->getMaxConcurrency(), $expected);
    }

    public function testGetMaxLifetime()
    {
        $expected = 30;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_MAX_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getMaxLifetime(), $expected);
    }

    public function testGetMinLifetime()
    {
        $expected = 30;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_MIN_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getMinLifetime(), $expected);
    }

    public function testGetDisableLocking()
    {
        $expected = false;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_DISABLE_LOCKING)
            ->willReturn($expected);
        $this->assertEquals($this->config->getDisableLocking(), $expected);
    }

    public function testGetBotLifetime()
    {
        $expected = 30;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_BOT_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getBotLifetime(), $expected);
    }

    public function testGetBotFirstLifetime()
    {
        $expected = 30;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_BOT_FIRST_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getBotFirstLifetime(), $expected);
    }

    public function testGetFirstLifetime()
    {
        $expected = 30;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_FIRST_LIFETIME)
            ->willReturn($expected);
        $this->assertEquals($this->config->getFirstLifetime(), $expected);
    }

    public function testBreakAfter()
    {
        $areaCode = 'frontend';
        $breakAfter = 5;
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(Config::PARAM_BREAK_AFTER . '_' . $areaCode)
            ->willReturn($breakAfter);
        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $this->assertEquals($this->config->getBreakAfter(), $breakAfter);
    }
}
