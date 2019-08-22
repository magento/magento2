<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected static $_baseDir;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    private $storage;

    /**
     * @inheritdoc
     */
    // phpcs:disable
    public static function setUpBeforeClass()
    {
        self::$_baseDir = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Cms\Helper\Wysiwyg\Images::class
        )->getCurrentPath() . 'MagentoCmsModelWysiwygImagesStorageTest';
        if (!file_exists(self::$_baseDir)) {
            mkdir(self::$_baseDir);
        }
        touch(self::$_baseDir . '/1.swf');
    }
    // phpcs:enable

    /**
     * @inheritdoc
     */
    // phpcs:ignore
    public static function tearDownAfterClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem\Driver\File::class
        )->deleteDirectory(
            self::$_baseDir
        );
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->storage = $this->objectManager->create(\Magento\Cms\Model\Wysiwyg\Images\Storage::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testGetFilesCollection(): void
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $collection = $this->storage->getFilesCollection(self::$_baseDir, 'media');
        $this->assertInstanceOf(\Magento\Cms\Model\Wysiwyg\Images\Storage\Collection::class, $collection);
        foreach ($collection as $item) {
            $this->assertInstanceOf(\Magento\Framework\DataObject::class, $item);
            $this->assertStringEndsWith('/1.swf', $item->getUrl());
            $this->assertStringMatchesFormat(
                'http://%s/static/%s/adminhtml/%s/%s/Magento_Cms/images/placeholder_thumbnail.jpg',
                $item->getThumbUrl()
            );
            return;
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testGetThumbsPath(): void
    {
        $this->assertStringStartsWith(
            $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
            $this->storage->getThumbsPath()
        );
    }

    /**
     * @return void
     */
    public function testDeleteDirectory(): void
    {
        $path = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->getCurrentPath();
        $dir = 'testDeleteDirectory';
        $fullPath = $path . $dir;
        $this->storage->createDirectory($dir, $path);
        $this->assertFileExists($fullPath);
        $this->storage->deleteDirectory($fullPath);
        $this->assertFileNotExists($fullPath);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We cannot delete directory /downloadable.
     */
    public function testDeleteDirectoryWithExcludedDirPath(): void
    {
        $dir = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->getCurrentPath() . 'downloadable';
        $this->storage->deleteDirectory($dir);
    }

    /**
     * @return void
     */
    public function testUploadFile(): void
    {
        $fileName = 'magento_small_image.jpg';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $fixtureDir = realpath(__DIR__ . '/../../../../Catalog/_files');
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->storage->uploadFile(self::$_baseDir);
        $this->assertTrue(is_file(self::$_baseDir . DIRECTORY_SEPARATOR . $fileName));
        // phpcs:enable
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We can't upload the file to current folder right now. Please try another folder.
     */
    public function testUploadFileWithExcludedDirPath(): void
    {
        $fileName = 'magento_small_image.jpg';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $fixtureDir = realpath(__DIR__ . '/../../../../Catalog/_files');
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $dir = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->getCurrentPath() . 'downloadable';
        $this->storage->uploadFile($dir);
        // phpcs:enable
    }

    /**
     * @param string $fileName
     * @param string $fileType
     * @param string|null $storageType
     *
     * @return void
     * @dataProvider testUploadFileWithWrongExtensionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File validation failed.
     */
    public function testUploadFileWithWrongExtension(string $fileName, string $fileType, ?string $storageType): void
    {
        $tmpDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $fixtureDir = realpath(__DIR__ . '/../../../_files');
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => $fileType,
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->storage->uploadFile(self::$_baseDir, $storageType);
        $this->assertFalse(is_file(self::$_baseDir . DIRECTORY_SEPARATOR . $fileName));
        // phpcs:enable
    }

    /**
     * @return array
     */
    public function testUploadFileWithWrongExtensionDataProvider(): array
    {
        return [
            [
                'fileName' => 'text.txt',
                'fileType' => 'text/plain',
                'storageType' => null,
            ],
            [
                'fileName' => 'test.swf',
                'fileType' => 'application/x-shockwave-flash',
                'storageType' => 'media',
            ],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File validation failed.
     * @return void
     */
    public function testUploadFileWithWrongFile(): void
    {
        $fileName = 'file.gif';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $file = fopen($filePath, "wb");
        fwrite($file, 'just a text');

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/gif',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->storage->uploadFile(self::$_baseDir);
        $this->assertFalse(is_file(self::$_baseDir . DIRECTORY_SEPARATOR . $fileName));
        // phpcs:enable
    }
}
