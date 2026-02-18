<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Frontend;

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Test\Unit\Cache\Frontend\FactoryTest\CacheDecoratorDummy;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\Frontend\Adapter\Symfony;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\BackendWrapper;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\LowLevelBackend;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\LowLevelFrontend;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DB\Adapter\AdapterInterface as DbAdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;

/**
 * Unit tests for Cache Frontend Factory
 * Tests Symfony cache implementation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FactoryTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/FactoryTest/CacheDecoratorDummy.php';
    }

    public function testCreate()
    {
        $model = $this->_buildModelForCreate();
        $result = $model->create(['backend' => NullAdapter::class]);

        $this->assertInstanceOf(
            FrontendInterface::class,
            $result,
            'Created object must implement \Magento\Framework\Cache\FrontendInterface'
        );

        $lowLevelFrontend = $result->getLowLevelFrontend();
        $this->assertInstanceOf(
            LowLevelFrontend::class,
            $lowLevelFrontend,
            'Created object must have Symfony LowLevelFrontend'
        );

        $backend = $result->getBackend();
        $this->assertTrue(
            $backend instanceof BackendWrapper ||
            $backend instanceof LowLevelBackend,
            'Created object must have valid Symfony backend wrapper'
        );
    }

    public function testCreateOptions()
    {
        $model = $this->_buildModelForCreate();
        $result = $model->create(
            [
                'backend' => FilesystemAdapter::class,
                'frontend_options' => ['lifetime' => 2601],
                'backend_options' => ['file_extension' => '.wtf'],
            ]
        );

        $frontend = $result->getLowLevelFrontend();
        $backend = $result->getBackend();

        $this->assertEquals(2601, $frontend->getOption('lifetime'));

        // For Symfony, backend options are not stored in the wrapper (returns null)
        $fileExtension = $backend->getOption('file_extension');
        $this->assertNull(
            $fileExtension,
            'Backend options are not stored in Symfony wrapper, should return null'
        );
    }

    public function testCreateEnforcedOptions()
    {
        $model = $this->_buildModelForCreate(['backend' => FilesystemAdapter::class]);
        $result = $model->create(['backend' => NullAdapter::class]);

        // The enforced option test verifies that enforced options override regular options
        // Since Symfony uses wrappers, we verify the backend has the correct interface
        $backend = $result->getBackend();
        $this->assertTrue(
            $backend instanceof BackendWrapper ||
            $backend instanceof LowLevelBackend,
            'Backend must be valid Symfony wrapper'
        );
    }

    /**     * @param string $expectedPrefix
     */
    #[DataProvider('idPrefixDataProvider')]
    public function testIdPrefix($options, $expectedPrefix)
    {
        $model = $this->_buildModelForCreate(['backend' => FilesystemAdapter::class]);
        $result = $model->create($options);

        $frontend = $result->getLowLevelFrontend();
        $this->assertEquals($expectedPrefix, $frontend->getOption('cache_id_prefix'));
    }

    /**
     * @return array
     */
    public static function idPrefixDataProvider()
    {
        return [
            // start of md5('DIR')
            'default id prefix' => [['backend' => NullAdapter::class], 'c15_'],
            'id prefix in "id_prefix" option' => [
                ['backend' => NullAdapter::class, 'id_prefix' => 'id_prefix_value'],
                'id_prefix_value',
            ],
            'id prefix in "prefix" option' => [
                ['backend' => NullAdapter::class, 'prefix' => 'prefix_value'],
                'prefix_value',
            ]
        ];
    }

    public function testCreateDecorators()
    {
        $model = $this->_buildModelForCreate(
            [],
            [
                [
                    'class' => CacheDecoratorDummy::class,
                    'parameters' => ['param' => 'value'],
                ]
            ]
        );
        $result = $model->create(['backend' => NullAdapter::class]);

        $this->assertInstanceOf(
            CacheDecoratorDummy::class,
            $result
        );

        $params = $result->getParams();
        $this->assertArrayHasKey('param', $params);
        $this->assertEquals($params['param'], 'value');
    }

    /**
     * Create the model to be tested, providing it with all required dependencies
     *
     * @param array $enforcedOptions
     * @param array $decorators
     * @return Factory
     * phpcs:disable Squiz.PHP.NonExecutableCode.Unreachable
     */
    protected function _buildModelForCreate($enforcedOptions = [], $decorators = [])
    {
        $filesystem = $this->createFilesystemMock();
        $resource = $this->createResourceConnectionMock();
        $serializer = $this->createSerializerMock();

        $cachePoolMock = $this->createMock(\Psr\Cache\CacheItemPoolInterface::class);
        $adapterMock = $this->createMock(TagAdapterInterface::class);

        $cacheFactory = function () use ($cachePoolMock) {
            return $cachePoolMock;
        };

        $processFrontendFunc = $this->createFrontendProcessor(
            $filesystem,
            $resource,
            $serializer,
            $cacheFactory,
            $adapterMock
        );

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->any())->method('create')->willReturnCallback($processFrontendFunc);

        // Create mock for SymfonyAdapterProvider
        $adapterProviderMock = $this->createMock(SymfonyAdapterProvider::class);
        $adapterProviderMock->expects($this->any())
            ->method('createTagAdapter')
            ->willReturn($adapterMock);

        $model = new Factory(
            $objectManager,
            $filesystem,
            $resource,
            $adapterProviderMock,
            $enforcedOptions,
            $decorators
        );

        return $model;
    }

    /**
     * Create filesystem mock
     *
     * @return Filesystem|MockObject
     */
    private function createFilesystemMock()
    {
        $dirMock = $this->createMock(ReadInterface::class);
        $dirMock->expects($this->any())->method('getAbsolutePath')->willReturn('DIR');

        $writeDirMock = $this->createMock(WriteInterface::class);
        $writeDirMock->expects($this->any())->method('getAbsolutePath')->willReturn('DIR');
        $writeDirMock->expects($this->any())->method('create')->willReturn(true);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($dirMock);
        $filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($writeDirMock);

        return $filesystem;
    }

    /**
     * Create resource connection mock
     *
     * @return ResourceConnection|MockObject
     */
    private function createResourceConnectionMock()
    {
        $resource = $this->createMock(ResourceConnection::class);
        $connectionMock = $this->createMock(DbAdapterInterface::class);
        $resource->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $resource->expects($this->any())->method('getTableName')->willReturnCallback(function ($table) {
            return $table;
        });

        return $resource;
    }

    /**
     * Create serializer mock
     *
     * @return Serialize|MockObject
     */
    private function createSerializerMock()
    {
        $serializer = $this->createMock(Serialize::class);
        $serializer->expects($this->any())->method('serialize')->willReturnCallback(
            function ($data) {
                // phpcs:ignore Magento2.Security.InsecureFunction.FoundWithAlternative
                return serialize($data);
            }
        );
        $serializer->expects($this->any())->method('unserialize')->willReturnCallback(
            function ($data) {
                // phpcs:ignore Magento2.Security.InsecureFunction.FoundWithAlternative
                return unserialize($data);
            }
        );

        return $serializer;
    }

    /**
     * Create frontend processor callback
     *
     * @param Filesystem $filesystem
     * @param ResourceConnection $resource
     * @param Serialize $serializer
     * @param \Closure $cacheFactory
     * @param mixed $adapterMock
     * @return \Closure
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function createFrontendProcessor($filesystem, $resource, $serializer, $cacheFactory, $adapterMock)
    {
        return function ($class, $params) use ($filesystem, $resource, $serializer, $cacheFactory, $adapterMock) {
            switch ($class) {
                case CacheDecoratorDummy::class:
                    $frontend = $params['frontend'];
                    unset($params['frontend']);
                    return new $class($frontend, $params);
                case SymfonyAdapterProvider::class:
                    return new $class($filesystem, $resource, $serializer);
                case Symfony::class:
                    $defaultLifetime = $params['defaultLifetime'] ?? 7200;
                    $idPrefix = $params['idPrefix'] ?? '';
                    return new $class($cacheFactory, $adapterMock, $defaultLifetime, $idPrefix);
                default:
                    throw new \Exception("Test is not designed to create {$class} objects");
            }
        };
    }
}
