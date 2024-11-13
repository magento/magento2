<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem;

/**
 * Test for \Magento\Framework\File\Uploader
 */
class UploaderTest extends TestCase
{
    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Filesystem\File\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->uploaderFactory = $objectManager->get(UploaderFactory::class);
        $filesystem = $objectManager->get(Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        $this->mediaDirectory->delete('customer_address');
    }

    /**
     * @dataProvider uploadDataProvider
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testUpload(string $expectedFile, ?string $newImageName = null): void
    {
        $this->mediaDirectory->delete('customer_address');
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath('customer_address/tmp/'));
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath('customer_address/tmp/magento.jpg');
        $this->mediaDirectory->getDriver()->filePutContents(
            $tmpFilePath,
            file_get_contents(__DIR__ . '/_files/magento.jpg')
        );

        $fileData = [
            'name' => 'magento.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tmpFilePath,
            'error' => 0,
            'size' => 139416,
        ];

        $uploader = $this->uploaderFactory->create(['fileId' => $fileData]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        $uploader->save($this->mediaDirectory->getAbsolutePath('customer_address'), $newImageName);

        self::assertEquals($newImageName ?? 'magento.jpg', $uploader->getUploadedFileName());
        self::assertTrue($this->mediaDirectory->isExist($expectedFile));
    }

    /**
     * @return array
     */
    public static function uploadDataProvider(): array
    {
        return [
            [
                'customer_address/magento.jpg',
                null,
            ],
            [
                'customer_address/new_magento.jpg',
                'new_magento.jpg',
            ]
        ];
    }
}
