<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests for the \Magento\CatalogImportExport\Model\Import\Uploader class.
 */
class UploaderTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * Random string appended to downloaded image name
     */
    const RANDOM_STRING = 'BRV8TAuR2AT88OH0';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    private $uploader;
    /**
     * @var \Magento\Framework\Filesystem\File\ReadInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileReader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileReader = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\File\ReadInterface::class);
        $fileReadFactory = $this->createMock(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $fileReadFactory->method('create')->willReturn($this->fileReader);
        $random = $this->createMock(\Magento\Framework\Math\Random::class);
        $random->method('getRandomString')->willReturn(self::RANDOM_STRING);
        $this->uploader = $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Import\Uploader::class,
            [
                'random' => $random,
                'readFactory' => $fileReadFactory
            ]
        );

        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);

        $appParams = \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->getBootstrap()
            ->getApplication()
            ->getInitParams()[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $mediaPath = $appParams[DirectoryList::MEDIA][DirectoryList::PATH];
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $tmpDir = $this->directory->getRelativePath($mediaPath . '/import');
        if (!$this->directory->create($tmpDir)) {
            throw new \RuntimeException('Failed to create temporary directory');
        }
        if (!$this->uploader->setTmpDir($tmpDir)) {
            throw new \RuntimeException(
                'Failed to set temporary directory for files.'
            );
        }

        parent::setUp();
    }

    /**
     * Tests move with external url
     *
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testMoveWithExternalURL(): void
    {
        $fileName = 'http://magento.com/static/images/random_image.jpg';
        $this->fileReader->method('readAll')->willReturn(file_get_contents($this->getTestImagePath()));
        $this->uploader->move($fileName);
        $destFilePath = $this->uploader->getTmpDir() . '/' . 'random_image_' . self::RANDOM_STRING . '.jpg';
        $this->assertTrue($this->directory->isExist($destFilePath));
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testMoveWithValidFile(): void
    {
        $testImagePath = $this->getTestImagePath();
        $fileName = basename($testImagePath);
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        //phpcs:ignore
        copy($testImagePath, $filePath);
        $this->uploader->move($fileName);
        $this->assertTrue($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }

    /**
     * Check validation against temporary directory.
     *
     * @magentoAppIsolation enabled
     * @return void
     *
     */
    public function testMoveWithFileOutsideTemp(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $tmpDir = $this->uploader->getTmpDir();
        $newTmpDir = $tmpDir . '/test1';
        if (!$this->directory->create($newTmpDir)) {
            throw new \RuntimeException('Failed to create temp dir');
        }
        $this->uploader->setTmpDir($newTmpDir);
        $testImagePath = $this->getTestImagePath();
        $fileName = basename($testImagePath);
        $filePath = $this->directory->getAbsolutePath($tmpDir . '/' . $fileName);
        //phpcs:ignore
        copy($testImagePath, $filePath);
        $this->uploader->move('../' . $fileName);
        $this->assertTrue($this->directory->isExist($tmpDir . '/' . $fileName));
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testMoveWithInvalidFile(): void
    {
        $this->expectExceptionMessage("Disallowed file type");
        $this->expectException(\Exception::class);
        $fileName = 'media_import_image.php';
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        //phpcs:ignore
        copy(__DIR__ . '/_files/' . $fileName, $filePath);
        $this->uploader->move($fileName);
        $this->assertFalse($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }

    /**
     * Get the full path to the test image
     *
     * @return string
     */
    private function getTestImagePath(): string
    {
        return __DIR__ . '/_files/magento_additional_image_one.jpg';
    }
}
