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
    protected $_file;

    /**
     * @var Media
     */
    protected $_loggerMock;

    /**
     * @var Database
     */
    protected $_storageHelperMock;

    /**
     * @var DateTime
     */
    protected $_mediaHelperMock;

    /**
     * @var \Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $_fileUtilityMock;

    protected function setUp(): void
    {
        $this->_loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->_storageHelperMock = $this->createMock(Database::class);
        $this->_mediaHelperMock = $this->createMock(Media::class);
        $this->_fileUtilityMock = $this->createMock(\Magento\MediaStorage\Model\ResourceModel\File\Storage\File::class);

        $this->_file = new File(
            $this->_loggerMock,
            $this->_storageHelperMock,
            $this->_mediaHelperMock,
            $this->_fileUtilityMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->_file);
    }

    public function testSaveFileWithWrongFileFormat(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Wrong file info format');
        $this->_file->saveFile([]);
    }

    public function testSaveFileUnsuccessfullyWithMissingDirectory()
    {
        $this->_fileUtilityMock
            ->expects($this->once())
            ->method('saveFile')
            ->willThrowException(new Exception());

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to save file "filename.ext" at "filename.ext"');
        $this->_file->saveFile([
            'filename' => 'filename.ext',
            'content' => 'content',
        ]);
    }

    public function testSaveFileUnsuccessfullyWithoutMissingDirectory()
    {
        $this->_fileUtilityMock
            ->expects($this->once())
            ->method('saveFile')
            ->willThrowException(new Exception());

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to save file "filename.ext" at "directory/filename.ext"');
        $this->_file->saveFile([
            'directory' => 'directory',
            'filename' => 'filename.ext',
            'content' => 'content',
        ]);
    }
}
