<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Phrase;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\App\ObjectManager;

class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var SaveHandlerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveHandlerFactoryMock;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->objectManager->addSharedInstance($this->deploymentConfigMock, DeploymentConfig::class);
        $this->saveHandlerFactoryMock = $this->createMock(SaveHandlerFactory::class);
        $this->objectManager->addSharedInstance($this->saveHandlerFactoryMock, SaveHandlerFactory::class);
    }

    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(DeploymentConfig::class);
        $this->objectManager->removeSharedInstance(SaveHandlerFactory::class);
    }

    /**
     * Tests that the session handler is correctly set when object is created.
     *
     * @dataProvider saveHandlerProvider
     * @param string $deploymentConfigHandler
     */
    public function testConstructor($deploymentConfigHandler)
    {
        $expected = $this->getExpectedSaveHandler($deploymentConfigHandler, ini_get('session.save_handler'));

        $this->deploymentConfigMock->method('get')
            ->willReturnCallback(function ($configPath) use ($deploymentConfigHandler) {
                switch ($configPath) {
                    case Config::PARAM_SESSION_SAVE_METHOD:
                        return $deploymentConfigHandler;
                    case Config::PARAM_SESSION_CACHE_LIMITER:
                        return 'private_no_expire';
                    case Config::PARAM_SESSION_SAVE_PATH:
                        return 'explicit_save_path';
                    default:
                        return null;
                }
            });

        $this->saveHandlerFactoryMock->expects($this->once())
            ->method('create')
            ->with($expected);
        $sessionConfig = $this->objectManager->create(ConfigInterface::class);
        $this->objectManager->create(SaveHandler::class, ['sessionConfig' => $sessionConfig]);

        // Test expectation
        $this->assertEquals(
            $expected,
            $sessionConfig->getOption('session.save_handler')
        );
    }

    public function saveHandlerProvider()
    {
        return [
            ['db'],
            ['redis'],
            ['files'],
            [false],
        ];
    }

    /**
     * Retrieve expected session.save_handler
     *
     * @param string $deploymentConfigHandler
     * @param string $iniHandler
     * @return string
     */
    private function getExpectedSaveHandler($deploymentConfigHandler, $iniHandler)
    {
        if ($deploymentConfigHandler) {
            return $deploymentConfigHandler;
        } elseif ($iniHandler) {
            return $iniHandler;
        } else {
            return SaveHandlerInterface::DEFAULT_HANDLER;
        }
    }

    public function testConstructorWithException()
    {
        $this->deploymentConfigMock->method('get')
            ->willReturnCallback(function ($configPath) {
                switch ($configPath) {
                    case Config::PARAM_SESSION_SAVE_METHOD:
                        return 'db';
                    case Config::PARAM_SESSION_CACHE_LIMITER:
                        return 'private_no_expire';
                    case Config::PARAM_SESSION_SAVE_PATH:
                        return 'explicit_save_path';
                    default:
                        return null;
                }
            });

        $this->saveHandlerFactoryMock->expects($this->at(0))
            ->method('create')
            ->willThrowException(new SessionException(new Phrase('Session Exception')));
        $this->saveHandlerFactoryMock->expects($this->at(1))
            ->method('create')
            ->with(SaveHandlerInterface::DEFAULT_HANDLER);
        $sessionConfig = $this->objectManager->create(ConfigInterface::class);
        $this->objectManager->create(SaveHandler::class, ['sessionConfig' => $sessionConfig]);

        // Test expectation
        $this->assertEquals(
            'db',
            $sessionConfig->getOption('session.save_handler')
        );
    }
}
