<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Config\FileResolverByModule;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileResolverByModuleTest extends TestCase
{
    /**
     * @var FileResolverByModule
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Reader|MockObject
     */
    private $readerMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var FileIteratorFactory|MockObject
     */
    private $fileIteratorFactoryMock;

    /**
     * @var ComponentRegistrar|MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var File|MockObject
     */
    private $fileDriver;

    protected function setUp(): void
    {
        $this->readerMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileIteratorFactoryMock = $this->getMockBuilder(FileIteratorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentRegistrarMock = $this->getMockBuilder(ComponentRegistrar::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileDriver = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            FileResolverByModule::class,
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
        $directoryMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

    public function testGetWithException()
    {
        $this->expectExceptionMessage('Primary db_schema file doesn`t exist');
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
        $directoryMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
            ->willReturn(false);
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
        $directoryMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
