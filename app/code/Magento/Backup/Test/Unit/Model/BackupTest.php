<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Model;

use Magento\Backup\Helper\Data;
use Magento\Backup\Model\Backup;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Backup\Model\Backup
 */
class BackupTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Backup
     */
    protected $backupModel;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var Data|MockObject
     */
    protected $dataHelperMock;

    /**
     * @var WriteInterface|MockObject
     */
    protected $directoryMock;

    protected function setUp(): void
    {
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->getMockBuilder(WriteInterface::class)
            ->getMock();

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->directoryMock);

        $this->objectManager = new ObjectManager($this);
        $this->backupModel = $this->objectManager->getObject(
            Backup::class,
            [
                'filesystem' => $this->filesystemMock,
                'helper' => $this->dataHelperMock
            ]
        );
    }

    /**
     * @covers \Magento\Backup\Model\Backup::output
     * @param bool $isFile
     * @param string $result
     * @dataProvider outputDataProvider
     */
    public function testOutput($isFile, $result)
    {
        $path = '/path/to';
        $time = 1;
        $name = 'test';
        $type = 'db';
        $extension = 'sql';
        $relativePath = '/path/to/1_db_test.sql';
        $contents = 'test_result';

        $this->directoryMock->expects($this->atLeastOnce())
            ->method('isFile')
            ->with($relativePath)
            ->willReturn($isFile);
        $this->directoryMock->expects($this->any())
            ->method('getRelativePath')
            ->with($relativePath)
            ->willReturn($relativePath);
        $this->directoryMock->expects($this->any())
            ->method('readFile')
            ->with($relativePath)
            ->willReturn($contents);
        $this->dataHelperMock->expects($this->any())
            ->method('getExtensionByType')
            ->with($type)
            ->willReturn($extension);

        $this->backupModel->setPath($path);
        $this->backupModel->setName($name);
        $this->backupModel->setTime($time);
        $this->assertEquals($result, $this->backupModel->output());
    }

    /**
     * @return array
     */
    public static function outputDataProvider()
    {
        return [
            ['isFile' => true, 'result' => 'test_result'],
            ['isFile' => false, 'result' => null]
        ];
    }
}
