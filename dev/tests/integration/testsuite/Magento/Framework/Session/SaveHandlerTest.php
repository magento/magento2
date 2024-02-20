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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Tests \Magento\Framework\Session\SaveHandler functionality.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var SaveHandlerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $saveHandlerFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->objectManager->addSharedInstance($this->deploymentConfigMock, DeploymentConfig::class);
        $this->saveHandlerFactoryMock = $this->createMock(SaveHandlerFactory::class);
        $this->objectManager->addSharedInstance($this->saveHandlerFactoryMock, SaveHandlerFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(DeploymentConfig::class);
        $this->objectManager->removeSharedInstance(SaveHandlerFactory::class);
    }

    /**
     * @return void
     */
    public function testRedisSaveHandler(): void
    {
        $this->deploymentConfigMock->method('get')
            ->willReturnMap(
                [
                    [Config::PARAM_SESSION_SAVE_METHOD, null, 'redis'],
                    [Config::PARAM_SESSION_SAVE_PATH, null, 'explicit_save_path'],
                ]
            );

        $redisHandlerMock = $this->getMockBuilder(SaveHandler\Redis::class)
            ->disableOriginalConstructor()
            ->getMock();
        $redisHandlerMock->method('open')
            ->with('explicit_save_path', 'test_session_id')
            ->willReturn(true);

        $this->saveHandlerFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->with('redis')
            ->willReturn($redisHandlerMock);

        $sessionConfig = $this->objectManager->create(ConfigInterface::class);
        /** @var SaveHandler $saveHandler */
        $saveHandler = $this->objectManager->create(SaveHandler::class, ['sessionConfig' => $sessionConfig]);
        $result = $saveHandler->open('explicit_save_path', 'test_session_id');
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testRedisSaveHandlerFallbackToDefaultOnSessionException(): void
    {
        $this->deploymentConfigMock->method('get')
            ->willReturnMap(
                [
                    [Config::PARAM_SESSION_SAVE_METHOD, null, 'redis'],
                    [Config::PARAM_SESSION_SAVE_PATH, null, 'explicit_save_path'],
                ]
            );

        $redisHandlerMock = $this->getMockBuilder(SaveHandler\Redis::class)
            ->disableOriginalConstructor()
            ->getMock();
        $redisHandlerMock->method('open')
            ->with('explicit_save_path', 'test_session_id')
            ->willThrowException(new SessionException(new Phrase('Session Exception')));

        $defaultHandlerMock = $this->getMockBuilder(SaveHandler\Native::class)
            ->disableOriginalConstructor()
            ->getMock();
        $defaultHandlerMock->expects($this->once())->method('open')->with('explicit_save_path', 'test_session_id');

        $this->saveHandlerFactoryMock
            ->method('create')
            ->withConsecutive(['redis'], [SaveHandlerInterface::DEFAULT_HANDLER])
            ->willReturnOnConsecutiveCalls($redisHandlerMock, $defaultHandlerMock);

        $sessionConfig = $this->objectManager->create(ConfigInterface::class);
        /** @var SaveHandler $saveHandler */
        $saveHandler = $this->objectManager->create(SaveHandler::class, ['sessionConfig' => $sessionConfig]);
        $saveHandler->open('explicit_save_path', 'test_session_id');
    }
}
