<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test for \Magento\Framework\File\Uploader
 */
class UploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->uploaderFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\MediaStorage\Model\File\UploaderFactory::class);

        $this->filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
    }

    /**
     * @return void
     */
    public function testUploadFileFromAllowedFolder(): void
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);

        $fileName = 'text.txt';
        $tmpDir = 'tmp';
        $filePath = $tmpDirectory->getAbsolutePath($fileName);

        $tmpDirectory->writeFile($fileName, 'just a text');

        $type = [
            'tmp_name' => $filePath,
            'name' => $fileName,
        ];

        $uploader = $this->uploaderFactory->create(['fileId' => $type]);
        $uploader->save($mediaDirectory->getAbsolutePath($tmpDir));

        $this->assertTrue($mediaDirectory->isFile($tmpDir . DIRECTORY_SEPARATOR . $fileName));
    }

    /**
     * @return void
     */
    public function testUploadFileFromNotAllowedFolder(): void
    {
        $this->expectExceptionMessage('Invalid parameter given. A valid $fileId[tmp_name] is expected.');
        $this->expectException(\InvalidArgumentException::class);
        $fileName = 'text.txt';
        $tmpDir = 'tmp';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::LOG);
        $filePath = $tmpDirectory->getAbsolutePath() . $tmpDir . DIRECTORY_SEPARATOR . $fileName;

        $tmpDirectory->writeFile($tmpDir . DIRECTORY_SEPARATOR . $fileName, 'just a text');

        $type = [
            'tmp_name' => $filePath,
            'name' => $fileName,
        ];

        $this->uploaderFactory->create(['fileId' => $type]);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $tmpDir = 'tmp';
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete($tmpDir);

        $logDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::LOG);
        $logDirectory->delete($tmpDir);
    }
}
