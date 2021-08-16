<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Helper\File\Media;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage\File;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/** Unit tests for \Magento\MediaStorage\Model\File\Storage\File class */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var Media
     */
    private $loggerMock;

    /**
     * @var Database
     */
    private $storageHelperMock;

    /**
     * @var DateTime
     */
    private $mediaHelperMock;

    /**
     * @var \Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    private $fileUtilityMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->storageHelperMock = $this->createMock(Database::class);
        $this->mediaHelperMock = $this->createMock(Media::class);
        $this->fileUtilityMock = $this->createMock(\Magento\MediaStorage\Model\ResourceModel\File\Storage\File::class);

        $this->file = new File(
            $this->loggerMock,
            $this->storageHelperMock,
            $this->mediaHelperMock,
            $this->fileUtilityMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->file);
    }

    public function testSaveFileWithWrongFileFormat(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Wrong file info format');
        $this->file->saveFile([]);
    }

    public function testSaveFileUnsuccessfullyWithMissingDirectory(): void
    {
        $this->fileUtilityMock
            ->expects($this->once())
            ->method('saveFile')
            ->willThrowException(new Exception());

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to save file "filename.ext" at "filename.ext"');
        $this->file->saveFile([
            'filename' => 'filename.ext',
            'content' => 'content',
        ]);
    }

    public function testSaveFileUnsuccessfullyWithoutMissingDirectory(): void
    {
        $this->fileUtilityMock
            ->expects($this->once())
            ->method('saveFile')
            ->willThrowException(new Exception());

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to save file "filename.ext" at "directory/filename.ext"');
        $this->file->saveFile([
            'directory' => 'directory',
            'filename' => 'filename.ext',
            'content' => 'content',
        ]);
    }
}
