<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\Config;

use Magento\Framework\Config\FileIterator;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FileResolverByModuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Config\FileResolverByModule
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileIteratorFactoryMock;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileDriver;

    protected function setUp()
    {
        $this->readerMock = $this->getMockBuilder(\Magento\Framework\Module\Dir\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileIteratorFactoryMock = $this->getMockBuilder(\Magento\Framework\Config\FileIteratorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentRegistrarMock = $this->getMockBuilder(\Magento\Framework\Component\ComponentRegistrar::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileDriver = $this->getMockBuilder(\Magento\Framework\Filesystem\Driver\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Config\FileResolverByModule::class,
            [
                'moduleReader' => $this->readerMock,
                'filesystem' => $this->filesystemMock,
                'iteratorFactory' => $this->fileIteratorFactoryMock,
                'componentRegistrar' => $this->componentRegistrarMock,
                'driver' => $this->fileDriver
            ]
        );
    }

    public function testGet()
    {
        $iterator = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iterator->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'some_path' => '<xml>Some Content</xml>'
            ]);
        $primaryIterator = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $primaryIterator->expects(self::once())
            ->method('toArray')
            ->willReturn([
                '/www/app/etc/db_schema.xml' => '<xml>Primary Content</xml>'
            ]);
        $directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryMock->expects(self::once())
            ->method('search')
            ->with('{db_schema.xml,*/db_schema.xml}')
            ->willReturn(['app/etc/db_schema.xml']);
        $directoryMock->expects(self::once())
            ->method('getAbsolutePath')
            ->willReturn('/www/app/etc/db_schema.xml');
        $this->readerMock->expects(self::once())
            ->method('getConfigurationFiles')
            ->willReturn($iterator);
        $this->fileIteratorFactoryMock->expects(self::once())
            ->method('create')
            ->with(['/www/app/etc/db_schema.xml'])
            ->willReturn($primaryIterator);
        $this->fileDriver->expects(self::once())
            ->method('isFile')
            ->with('/www/app/etc/db_schema.xml')
            ->willReturn(true);
        $this->filesystemMock->expects(self::once())
            ->method('getDirectoryRead')
            ->willReturn($directoryMock);
        self::assertEquals(
            $this->model->get('db_schema.xml', 'all'),
            [
                'some_path' => '<xml>Some Content</xml>',
                '/www/app/etc/db_schema.xml' => '<xml>Primary Content</xml>'
            ]
        );
    }

    /**
     * @expectedExceptionMessage Primary db_schema file doesn`t exist
     */
    public function testGetWithException()
    {
        $iterator = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iterator->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'some_path' => '<xml>Some Content</xml>'
            ]);
        $primaryIterator = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $primaryIterator->expects(self::once())
            ->method('toArray')
            ->willReturn([
                '/www/app/etc/db_schema.xml' => '<xml>Primary Content</xml>'
            ]);
        $directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryMock->expects(self::once())
            ->method('search')
            ->with('{db_schema.xml,*/db_schema.xml}')
            ->willReturn(['app/etc/db_schema.xml']);
        $directoryMock->expects(self::once())
            ->method('getAbsolutePath')
            ->willReturn('/www/app/etc/db_schema.xml');
        $this->readerMock->expects(self::once())
            ->method('getConfigurationFiles')
            ->willReturn($iterator);
        $this->fileIteratorFactoryMock->expects(self::once())
            ->method('create')
            ->with(['/www/app/etc/db_schema.xml'])
            ->willReturn($primaryIterator);
        $this->fileDriver->expects(self::once())
            ->method('isFile')
            ->with('/www/app/etc/db_schema.xml')
            ->willReturn(true);
        $this->filesystemMock->expects(self::once())
            ->method('getDirectoryRead')
            ->willReturn($directoryMock);
        $this->model->get('db_schema.xml', 'all');
    }

    public function testGetOneModule()
    {
        $iterator = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iterator->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'some_path/etc/db_schema.xml' => '<xml>Some Content</xml>'
            ]);
        $primaryIterator = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $primaryIterator->expects(self::once())
            ->method('toArray')
            ->willReturn([
                '/www/app/etc/db_schema.xml' => '<xml>Primary Content</xml>'
            ]);
        $directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryMock->expects(self::once())
            ->method('search')
            ->with('{db_schema.xml,*/db_schema.xml}')
            ->willReturn(['app/etc/db_schema.xml']);
        $directoryMock->expects(self::once())
            ->method('getAbsolutePath')
            ->willReturn('/www/app/etc/db_schema.xml');
        $this->readerMock->expects(self::once())
            ->method('getConfigurationFiles')
            ->willReturn($iterator);
        $this->fileIteratorFactoryMock->expects(self::once())
            ->method('create')
            ->with(['/www/app/etc/db_schema.xml'])
            ->willReturn($primaryIterator);
        $this->fileDriver->expects(self::once())
            ->method('isFile')
            ->with('/www/app/etc/db_schema.xml')
            ->willReturn(true);
        $this->filesystemMock->expects(self::once())
            ->method('getDirectoryRead')
            ->willReturn($directoryMock);
        $this->componentRegistrarMock->expects(self::once())
            ->method('getPath')
            ->with('module', 'Magento_Some')
            ->willReturn('some_path');
        self::assertEquals(
            [
                'some_path/etc/db_schema.xml' => '<xml>Some Content</xml>',
                '/www/app/etc/db_schema.xml' => '<xml>Primary Content</xml>'
            ],
            $this->model->get('db_schema.xml', 'Magento_Some')
        );
    }
}
