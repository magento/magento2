<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Filesystem;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for the \Magento\Framework\App\Filesystem\DirectoryResolver verification.
 */
class DirectoryResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
=======
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;
>>>>>>> upstream/2.2-develop

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->directoryResolver = $this->objectManager
            ->create(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
<<<<<<< HEAD
        $this->filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
=======
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $this->directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
>>>>>>> upstream/2.2-develop
    }

    /**
     * @param string $path
     * @param string $directoryConfig
     * @param bool $expectation
     * @dataProvider validatePathDataProvider
<<<<<<< HEAD
     * @throws \Magento\Framework\Exception\FileSystemException
=======
>>>>>>> upstream/2.2-develop
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testValidatePath($path, $directoryConfig, $expectation)
    {
<<<<<<< HEAD
        $directory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath() .'/' .$path;
=======
        $path = $this->directory->getAbsolutePath($path);
>>>>>>> upstream/2.2-develop
        $this->assertEquals($expectation, $this->directoryResolver->validatePath($path, $directoryConfig));
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testValidatePathWithException()
    {
<<<<<<< HEAD
        $directory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath();
=======
        $path = $this->directory->getAbsolutePath();
>>>>>>> upstream/2.2-develop
        $this->directoryResolver->validatePath($path, 'wrong_dir');
    }

    /**
<<<<<<< HEAD
     * @param string $path
     * @param string $directoryConfig
     * @param bool $expectation
     * @dataProvider validatePathDataProvider
     * @throws \Magento\Framework\Exception\FileSystemException
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testValidatePathWithSymlink($path, $directoryConfig, $expectation)
    {
        $directory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::PUB);
        $driver = $directory->getDriver();

        $mediaPath = $directory->getAbsolutePath('media');
        $mediaMovedPath = $directory->getAbsolutePath('moved-media');

        try {
            $driver->rename($mediaPath, $mediaMovedPath);
            $driver->symlink($mediaMovedPath, $mediaPath);
            $this->testValidatePath($path, $directoryConfig, $expectation);
        } finally {
            // be defensive in case some operations failed
            if ($driver->isExists($mediaPath) && $driver->isExists($mediaMovedPath)) {
                $driver->deleteFile($mediaPath);
                $driver->rename($mediaMovedPath, $mediaPath);
            } elseif ($driver->isExists($mediaMovedPath)) {
                $driver->rename($mediaMovedPath, $mediaPath);
            }
        }
    }

    /**
=======
>>>>>>> upstream/2.2-develop
     * @return array
     */
    public function validatePathDataProvider()
    {
        return [
            [
                '/',
                DirectoryList::MEDIA,
                true,
            ],
            [
                '/../../pub/',
                DirectoryList::MEDIA,
                false,
            ],
        ];
    }
}
