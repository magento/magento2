<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Filesystem;

<<<<<<< HEAD
use Magento\Framework\App\Filesystem\DirectoryList;
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->directoryResolver = $this->objectManager
            ->create(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
        $this->filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
    }

    /**
     * @param string $path
     * @param string $directoryConfig
     * @param bool $expectation
     * @dataProvider validatePathDataProvider
<<<<<<< HEAD
=======
     * @throws \Magento\Framework\Exception\FileSystemException
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testValidatePath($path, $directoryConfig, $expectation)
    {
<<<<<<< HEAD
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
=======
        $directory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $path = $directory->getAbsolutePath() .'/' .$path;
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
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
=======
        $directory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $path = $directory->getAbsolutePath();
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
