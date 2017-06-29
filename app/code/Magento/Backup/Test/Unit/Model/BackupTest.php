<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @covers \Magento\Backup\Model\Backup
 */
class BackupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Backup\Model\Backup
     */
    protected $backupModel;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Backup\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder(\Magento\Backup\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->getMock();

        $this->filesystemMock->expects($this->atLeastOnce())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->directoryMock);

        $this->objectManager = new ObjectManager($this);
        $this->backupModel = $this->objectManager->getObject(
            \Magento\Backup\Model\Backup::class,
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
    public function outputDataProvider()
    {
        return [
            ['isFile' => true, 'result' => 'test_result'],
            ['isFile' => false, 'result' => null]
        ];
    }
}
