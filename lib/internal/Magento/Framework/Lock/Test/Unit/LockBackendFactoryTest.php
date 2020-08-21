<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Lock\Backend\Cache as CacheLock;
use Magento\Framework\Lock\Backend\Database as DatabaseLock;
use Magento\Framework\Lock\Backend\FileLock;
use Magento\Framework\Lock\Backend\Zookeeper as ZookeeperLock;
use Magento\Framework\Lock\LockBackendFactory;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LockBackendFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var LockBackendFactory
     */
    private $factory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->factory = new LockBackendFactory($this->objectManagerMock, $this->deploymentConfigMock);
    }

    public function testCreateWithException()
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $this->expectExceptionMessage('Unknown locks provider: someProvider');
        $this->deploymentConfigMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['lock/provider', LockBackendFactory::LOCK_DB], ['lock/config', []])
            ->willReturnOnConsecutiveCalls('someProvider', []);

        $this->factory->create();
    }

    /**
     * @param string $lockProvider
     * @param string $lockProviderClass
     * @param array $config
     * @dataProvider createDataProvider
     */
    public function testCreate(string $lockProvider, string $lockProviderClass, array $config)
    {
        $lockManagerMock = $this->getMockForAbstractClass(LockManagerInterface::class);
        $this->deploymentConfigMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['lock/provider', LockBackendFactory::LOCK_DB], ['lock/config', []])
            ->willReturnOnConsecutiveCalls($lockProvider, $config);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($lockProviderClass, $config)
            ->willReturn($lockManagerMock);

        $this->assertSame($lockManagerMock, $this->factory->create());
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        $data = [
            'db' => [
                'lockProvider' => LockBackendFactory::LOCK_DB,
                'lockProviderClass' => DatabaseLock::class,
                'config' => ['prefix' => 'somePrefix'],
            ],
            'cache' => [
                'lockProvider' => LockBackendFactory::LOCK_CACHE,
                'lockProviderClass' => CacheLock::class,
                'config' => [],
            ],
            'file' => [
                'lockProvider' => LockBackendFactory::LOCK_FILE,
                'lockProviderClass' => FileLock::class,
                'config' => ['path' => '/my/path'],
            ],
        ];

        if (extension_loaded('zookeeper')) {
            $data['zookeeper'] = [
                'lockProvider' => LockBackendFactory::LOCK_ZOOKEEPER,
                'lockProviderClass' => ZookeeperLock::class,
                'config' => ['host' => 'some host'],
            ];
        }

        return $data;
    }
}
