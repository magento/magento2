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
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;
=======
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->directoryResolver = $this->objectManager
            ->create(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
<<<<<<< HEAD
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $this->directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
=======
        $this->filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @param string $path
     * @param string $directoryConfig
     * @param bool $expectation
     * @dataProvider validatePathDataProvider
<<<<<<< HEAD
=======
     * @throws \Magento\Framework\Exception\FileSystemException
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testValidatePath($path, $directoryConfig, $expectation)
    {
<<<<<<< HEAD
        $path = $this->directory->getAbsolutePath($path);
=======
        $directory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath() .'/' .$path;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
        $path = $this->directory->getAbsolutePath();
=======
        $directory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->directoryResolver->validatePath($path, 'wrong_dir');
    }

    /**
<<<<<<< HEAD
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
