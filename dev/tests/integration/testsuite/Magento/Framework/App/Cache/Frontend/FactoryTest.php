<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Cache\Frontend;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Cache\Backend\Redis;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * Object manager used to build the factory under test
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Area
     */
    private $model;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->factory = $this->objectManager->create(Factory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $varDirectory = Bootstrap::getObjectManager()
            ->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::VAR_DIR);
        foreach (['unused_cache_dir_41157', 'used_cache_dir_41157'] as $directory) {
            if ($varDirectory->isExist($directory)) {
                $varDirectory->delete($directory);
            }
        }
    }

    /**
     * Check RemoteSynchronizedCache
     * Removing any cache item in the RemoteSynchronizedCache must invalidate all cache items
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testRemoteSynchronizedCache()
    {
        $data = 'data';
        $identifier = 'identifier';
        $secondIdentifier = 'secondIdentifier';
        $secondData = 'secondData';

        $frontendOptions = ['backend' => 'remote_synchronized_cache'];
        $this->model = $this->factory->create($frontendOptions);

        //Saving data
        $this->assertTrue($this->model->save($data, $identifier));
        $this->assertTrue($this->model->save($secondData, $secondIdentifier));

        //Checking data
        $this->assertEquals($this->model->load($identifier), $data);
        $this->assertEquals($this->model->load($secondIdentifier), $secondData);

        //Removing data
        sleep(2);
        $this->assertTrue($this->model->remove($secondIdentifier));
        $this->assertTrue($this->model->remove($identifier));
        $this->assertEquals($this->model->load($identifier), false);
        $this->assertEquals($this->model->load($secondIdentifier), false);

        //Saving data
        $this->assertTrue($this->model->save($data, $identifier));
        $this->assertTrue($this->model->save($secondData, $secondIdentifier));

        //Checking data
        $this->assertEquals($this->model->load($identifier), $data);
        $this->assertEquals($this->model->load($secondIdentifier), $secondData);
    }

    /**
     * Verify factory will create cache frontend instance with default options in case Redis is not available.
     *
     * @return void
     */
    public function testCreateCacheFrontedInstanceWithFallbackToDefaultOptions(): void
    {
        $options = [
            'backend_options' => [
                'server' => null,
            ],
            'id_prefix' => 'test_prefix',
            'backend' => Redis::class,
        ];

        self::assertInstanceOf(FrontendInterface::class, $this->factory->create($options));
    }

    /**
     * A frontend backed by a non file-based backend must not create its configured cache_dir.
     *
     * @return void
     */
    public function testNonFilesystemBackendDoesNotCreateCacheDir(): void
    {
        $this->factory->create(
            [
                'backend' => 'database',
                'backend_options' => ['cache_dir' => 'unused_cache_dir_41157'],
                'id_prefix' => 'test_41157_',
            ]
        );

        $varDirectory = Bootstrap::getObjectManager()
            ->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::VAR_DIR);

        self::assertFalse(
            $varDirectory->isExist('unused_cache_dir_41157'),
            'Cache directory must not be created for a backend that does not store cache entries on disk.'
        );
    }

    /**
     * A frontend backed by the file backend must still get its cache_dir created.
     *
     * @return void
     */
    public function testFilesystemBackendCreatesCacheDir(): void
    {
        $this->factory->create(
            [
                'backend' => 'file',
                'backend_options' => ['cache_dir' => 'used_cache_dir_41157'],
                'id_prefix' => 'test_41157_',
            ]
        );

        $varDirectory = Bootstrap::getObjectManager()
            ->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::VAR_DIR);

        self::assertTrue(
            $varDirectory->isExist('used_cache_dir_41157'),
            'Cache directory must be created for a file-based backend.'
        );
    }
}
