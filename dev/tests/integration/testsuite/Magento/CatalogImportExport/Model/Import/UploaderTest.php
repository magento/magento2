<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Downloadable\Model\Url\DomainValidator;

/**
 * Tests for the \Magento\CatalogImportExport\Model\Import\Uploader class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploaderTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * Random string appended to downloaded image name
     */
    private const RANDOM_STRING = 'BRV8TAuR2AT88OH0';
    
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
     * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $httpClient;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->httpClient = $this->createMock(ClientInterface::class);
        $httpClientFactory = $this->createMock(ClientFactory::class);
        $httpClientFactory->method('create')->willReturn($this->httpClient);
        $random = $this->createMock(\Magento\Framework\Math\Random::class);
        $random->method('getRandomString')->willReturn(self::RANDOM_STRING);
        $domainValidator = $this->createMock(DomainValidator::class);
        $domainValidator->method('isValid')->willReturn(true);
        $this->uploader = $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Import\Uploader::class,
            [
                'random' => $random,
                'httpClientFactory' => $httpClientFactory,
                'domainValidator' => $domainValidator
            ]
        );

        $this->directory = $this->objectManager->get(TargetDirectory::class)->getDirectoryWrite(DirectoryList::ROOT);

        if (!$this->directory->getDriver() instanceof File) {
            $mediaPath = 'media';
        } else {
            $appParams = \Magento\TestFramework\Helper\Bootstrap::getInstance()
                ->getBootstrap()
                ->getApplication()
                ->getInitParams()[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
            $mediaPath = $appParams[DirectoryList::MEDIA][DirectoryList::PATH];
        }

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
     * @return void
     */
    public function testMoveWithExternalURL(): void
    {
        $fileName = 'http://magento.com/static/images/random_image.jpg';
        $this->httpClient->expects($this->once())->method('setTimeout')->with(0);
        $this->httpClient->expects($this->once())->method('setOptions')->with([
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME => 60
        ]);
        $this->httpClient->expects($this->once())->method('get')->with($fileName);
        $this->httpClient->method('getStatus')->willReturn(200);
        $this->httpClient->method('getBody')->willReturn(file_get_contents($this->getTestImagePath()));
        $this->uploader->move($fileName);
        $destFilePath = $this->uploader->getTmpDir() . '/' . 'random_image_' . self::RANDOM_STRING . '.jpg';
        $this->assertTrue($this->directory->isExist($destFilePath));
    }

    /**
     * @return void
     */
    public function testMoveWithValidFile(): void
    {
        $testImagePath = $this->getTestImagePath();
        $fileName = basename($testImagePath);
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        //phpcs:ignore
        $this->copyFile($testImagePath, $filePath);

        $this->uploader->move($fileName);
        $this->assertTrue($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }

    /**
     * Check validation against temporary directory.
     *
     * @return void
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
        $this->copyFile($testImagePath, $filePath);
        $this->uploader->move('../' . $fileName);
        $this->assertTrue($this->directory->isExist($tmpDir . '/' . $fileName));
    }

    /**
     * @return void
     */
    public function testMoveWithInvalidFile(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Disallowed file type');

        $fileName = 'media_import_image.php';
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        //phpcs:ignore
        $this->copyFile(__DIR__ . '/_files/' . $fileName, $filePath);
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

    /**
     * @param string $source
     * @param string $destination
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function copyFile(string $source, string $destination)
    {
        $driver = $this->directory->getDriver();
        $absolutePath = $this->directory->getAbsolutePath($destination);

        $driver->createDirectory(dirname($absolutePath));
        $driver->filePutContents($destination, file_get_contents($source));
    }
}
