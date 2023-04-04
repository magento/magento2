<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Unit\Driver;

use Magento\AwsS3\Driver\AwsS3Factory;
use Magento\AwsS3\Driver\CachedCredentialsProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterfaceFactory;
use Magento\RemoteStorage\Driver\Adapter\CachedAdapterInterfaceFactory;
use Magento\RemoteStorage\Driver\Adapter\MetadataProviderInterfaceFactory;
use Magento\RemoteStorage\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AwsS3FactoryTest extends TestCase
{
    /**
     * @var AwsS3Factory
     */
    private $factory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Config|MockObject
     */
    private $remoteStorageConfigMock;

    /**
     * @var MetadataProviderInterfaceFactory|MockObject
     */
    private $metadataFactoryMock;

    /**
     * @var CacheInterfaceFactory|MockObject
     */
    private $remoteStorageCacheMock;

    /**
     * @var CachedAdapterInterfaceFactory|MockObject
     */
    private $remoteCacheAdapterMock;

    /**
     * @var string|null
     */
    private $cachePrefix = 'testPrefix';

    /**
     * @var CachedCredentialsProvider|MockObject
     */
    private $cachedCredsProviderMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->remoteStorageConfigMock = $this->createMock(Config::class);
        $this->metadataFactoryMock = $this->createMock(MetadataProviderInterfaceFactory::class);
        $this->remoteStorageCacheMock = $this->createMock(CacheInterfaceFactory::class);
        $this->remoteCacheAdapterMock = $this->createMock(CachedAdapterInterfaceFactory::class);
        $this->cachedCredsProviderMock = $this->createMock(CachedCredentialsProvider::class);

        $this->factory = new AwsS3Factory(
            $this->objectManagerMock,
            $this->remoteStorageConfigMock,
            $this->metadataFactoryMock,
            $this->remoteStorageCacheMock,
            $this->remoteCacheAdapterMock,
            $this->cachePrefix,
            $this->cachedCredsProviderMock
        );
    }

    /**
     * If no credentials in magento config, credentials retrieved from AWS should be cached
     *
     * @return void
     */
    public function testPrepareConfigUseCache()
    {
        $config = [
            'region' => 'us-west-1',
            'bucket' => 'someName',
            'credentials' => []
        ];
        $this->cachedCredsProviderMock->expects($this->once())->method('get');
        $this->invokePrepareConfig($config);
    }

    public function testPrepareConfigMissingRequired()
    {
        $config = [
            'credentials' => [
                'key' => 'someKey',
                'secret' => 'verySecretKey'
            ]
        ];

        $this->expectException('\Magento\RemoteStorage\Driver\DriverException');
        $this->invokePrepareConfig($config);
    }

    /**
     * Invoke private method via reflection
     *
     * @param array $config
     * @return array
     */
    private function invokePrepareConfig(array $config): array
    {
        $method = new \ReflectionMethod(
            AwsS3Factory::class,
            'prepareConfig'
        );
        $method->setAccessible(true);

        return $method->invokeArgs($this->factory, [$config]);
    }
}
