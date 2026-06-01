<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Unit\Driver;

use Magento\AwsS3\Driver\AwsS3;
use Magento\AwsS3\Driver\AwsS3Factory;
use Magento\AwsS3\Driver\CachedCredentialsProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterfaceFactory;
use Magento\RemoteStorage\Driver\Adapter\CachedAdapterInterfaceFactory;
use Magento\RemoteStorage\Driver\Adapter\MetadataProviderInterfaceFactory;
use Magento\RemoteStorage\Driver\Adapter\MetadataProviderInterface;
use Magento\RemoteStorage\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verify that the object URL constructed by AwsS3Factory does not contain './'.
 */
class AwsS3FactoryObjectUrlTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var AwsS3Factory
     */
    private $factory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $remoteStorageConfigMock = $this->createMock(Config::class);
        $metadataFactoryMock = $this->createMock(MetadataProviderInterfaceFactory::class);
        $metadataFactoryMock->method('create')
            ->willReturn($this->createMock(MetadataProviderInterface::class));
        $remoteStorageCacheMock = $this->createMock(CacheInterfaceFactory::class);
        $remoteCacheAdapterMock = $this->createMock(CachedAdapterInterfaceFactory::class);
        $cachedCredsProviderMock = $this->createMock(CachedCredentialsProvider::class);

        $this->factory = new AwsS3Factory(
            $this->objectManagerMock,
            $remoteStorageConfigMock,
            $metadataFactoryMock,
            $remoteStorageCacheMock,
            $remoteCacheAdapterMock,
            'testPrefix',
            $cachedCredsProviderMock
        );
    }

    #[DataProvider('objectUrlDataProvider')]
    public function testObjectUrlDoesNotContainDotSlash(string $prefix, string $expectedUrlSuffix): void
    {
        $config = [
            'region' => 'us-east-1',
            'bucket' => 'test-bucket',
            'credentials' => [
                'key' => 'test-key',
                'secret' => 'test-secret'
            ]
        ];

        $capturedObjectUrl = null;

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                AwsS3::class,
                $this->callback(function (array $args) use (&$capturedObjectUrl) {
                    $capturedObjectUrl = $args['objectUrl'] ?? null;
                    return true;
                })
            )
            ->willReturn($this->createMock(AwsS3::class));

        $this->factory->createConfigured($config, $prefix);

        $this->assertNotNull($capturedObjectUrl, 'objectUrl should have been passed to ObjectManager::create');
        $this->assertStringNotContainsString('/./', $capturedObjectUrl, 'objectUrl must not contain "/./"');
        $this->assertStringNotContainsString('/.', rtrim($capturedObjectUrl, '/'), 'objectUrl must not end with "/."');
        $this->assertStringEndsWith($expectedUrlSuffix, $capturedObjectUrl);
    }

    /**
     * @return array
     */
    public static function objectUrlDataProvider(): array
    {
        return [
            'empty prefix' => [
                'prefix' => '',
                'expectedUrlSuffix' => '.com/',
            ],
            'prefix without slashes' => [
                'prefix' => 'myprefix',
                'expectedUrlSuffix' => 'myprefix/',
            ],
            'prefix with trailing slash' => [
                'prefix' => 'myprefix/',
                'expectedUrlSuffix' => 'myprefix/',
            ],
            'prefix with leading slash' => [
                'prefix' => '/myprefix',
                'expectedUrlSuffix' => 'myprefix/',
            ],
        ];
    }
}
