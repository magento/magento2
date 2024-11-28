<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test of file abstract service
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Customization;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\View\Design\Theme\Customization;
use Magento\Framework\View\Design\Theme\Customization\AbstractFile;
use Magento\Framework\View\Design\Theme\Customization\Path;
use Magento\Framework\View\Design\Theme\FileFactory;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractFileTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework_MockObject_MockBuilder
     */
    protected $_modelBuilder;

    /**
     * @var MockObject
     */
    protected $_customizationPath;

    /**
     * @var MockObject
     */
    protected $_fileFactory;

    /**
     * @var MockObject
     */
    protected $_filesystem;

    protected function setUp(): void
    {
        $this->_customizationPath = $this->createMock(Path::class);
        $this->_fileFactory =
            $this->createPartialMock(FileFactory::class, ['create']);
        $this->_filesystem = $this->createMock(Filesystem::class);

        $this->_modelBuilder = $this->getMockBuilder(
            AbstractFile::class
        )->onlyMethods(
            ['getType', 'getContentType']
        )->setConstructorArgs(
            [$this->_customizationPath, $this->_fileFactory, $this->_filesystem]
        );
    }

    protected function tearDown(): void
    {
        $this->_customizationPath = null;
        $this->_fileFactory = null;
        $this->_filesystem = null;
        $this->_modelBuilder = null;
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::__construct
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::create
     */
    public function testCreate()
    {
        $model = $this->_modelBuilder->getMock();
        $file = $this->createMock(File::class);
        $file->expects($this->once())->method('setCustomizationService')->with($model);
        $this->_fileFactory->expects($this->once())->method('create')->willReturn($file);
        /** @var AbstractFile $model */
        $this->assertEquals($file, $model->create());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::getFullPath
     */
    public function testGetFullPath()
    {
        $model = $this->_modelBuilder->getMock();
        $theme = $this->createMock(Theme::class);
        $file = $this->createMock(File::class);

        $file->expects($this->any())->method('getTheme')->willReturn($theme);
        $file->expects($this->once())->method('getData')->with('file_path')->willReturn('file.path');

        $this->_customizationPath->expects(
            $this->once()
        )->method(
            'getCustomizationPath'
        )->willReturn(
            '/path'
        );

        /** @var \Magento\Framework\View\Design\Theme\Customization\AbstractFile $model */
        /** @var File $file */
        $this->assertEquals('/path' . '/' . 'file.path', $model->getFullPath($file));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::prepareFile
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_prepareFileName
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_prepareFilePath
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_prepareSortOrder
     * @dataProvider getTestContent
     */
    public function testPrepareFile($type, $fileContent, $expectedContent, $existedFiles)
    {
        $model = $this->_modelBuilder->getMock();
        $model->expects($this->any())->method('getType')->willReturn($type);
        $model->expects($this->any())->method('getContentType')->willReturn($type);

        $files = [];
        foreach ($existedFiles as $fileData) {
            $file = $this->createPartialMock(File::class, ['save']);
            $file->setData($fileData);
            $files[] = $file;
        }
        $customization = $this->createMock(Customization::class);
        $customization->expects(
            $this->atLeastOnce()
        )->method(
            'getFilesByType'
        )->with(
            $type
        )->willReturn(
            $files
        );

        $theme = $this->createMock(Theme::class);
        $theme->expects($this->any())->method('getCustomization')->willReturn($customization);

        $file = $this->createPartialMock(File::class, ['getTheme', 'save']);
        $file->expects($this->any())->method('getTheme')->willReturn($theme);
        $file->setData($fileContent);

        /** @var \Magento\Framework\View\Design\Theme\Customization\AbstractFile $model */
        /** @var File $file */
        $model->prepareFile($file);
        $this->assertEquals($expectedContent, $file->getData());
    }

    /**
     * @return array
     */
    public static function getTestContent()
    {
        return [
            'first_condition' => [
                'type' => 'css',
                'fileContent' => ['file_name' => 'test.css', 'content' => 'test content', 'sort_order' => 1],
                'expectedContent' => [
                    'file_type' => 'css',
                    'file_name' => 'test_1.css',
                    'file_path' => 'css/test_1.css',
                    'content' => 'test content',
                    'sort_order' => 2,
                ],
                'existedFiles' => [
                    ['id' => 1, 'file_path' => 'css/test.css', 'content' => 'test content', 'sort_order' => 1],
                ],
            ],
            'second_condition' => [
                'type' => 'js',
                'fileContent' => ['file_name' => 'test.js', 'content' => 'test content', 'sort_order' => 1],
                'expectedContent' => [
                    'file_type' => 'js',
                    'file_name' => 'test_3.js',
                    'file_path' => 'js/test_3.js',
                    'content' => 'test content',
                    'sort_order' => 12,
                ],
                'existedFiles' => [
                    ['id' => 1, 'file_path' => 'js/test.js', 'content' => 'test content', 'sort_order' => 3],
                    ['id' => 2, 'file_path' => 'js/test_1.js', 'content' => 'test content', 'sort_order' => 5],
                    ['id' => 3, 'file_path' => 'js/test_2.js', 'content' => 'test content', 'sort_order' => 7],
                    ['id' => 4, 'file_path' => 'js/test_4.js', 'content' => 'test content', 'sort_order' => 9],
                    ['id' => 5, 'file_path' => 'js/test_5.js', 'content' => 'test content', 'sort_order' => 11],
                ],
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::save
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_saveFileContent
     */
    public function testSave()
    {
        $model = $this->_modelBuilder->onlyMethods(['getFullPath'])->getMock();

        $file = $this->createPartialMock(File::class, ['__wakeup']);
        $file->setData(
            [
                'file_type' => 'js',
                'file_name' => 'test_3.js',
                'file_path' => 'js/test_3.js',
                'content' => 'test content',
                'sort_order' => 12,
            ]
        );
        $model->expects($this->once())->method('getFullPath')->with($file)->willReturn('test_path');

        $directoryMock = $this->createPartialMock(
            Write::class,
            ['writeFile', 'delete', 'getRelativePath']
        );
        $directoryMock->expects($this->once())->method('writeFile')->willReturn(true);
        $directoryMock->expects($this->once())->method('delete')->willReturn(true);

        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::ROOT
        )->willReturn(
            $directoryMock
        );
        /** @var \Magento\Framework\View\Design\Theme\Customization\AbstractFile $model */
        /** @var File $file */
        $model->save($file);
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::delete
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_deleteFileContent
     */
    public function testDelete()
    {
        $model = $this->_modelBuilder->onlyMethods(['getFullPath'])->getMock();
        $file = $this->createPartialMock(File::class, ['__wakeup']);
        $file->setData(
            [
                'file_type' => 'js',
                'file_name' => 'test_3.js',
                'file_path' => 'js/test_3.js',
                'content' => 'test content',
                'sort_order' => 12,
            ]
        );
        $directoryMock = $this->createPartialMock(
            Write::class,
            ['touch', 'delete', 'getRelativePath']
        );
        $directoryMock->expects($this->once())->method('touch')->willReturn(true);
        $directoryMock->expects($this->once())->method('delete')->willReturn(true);

        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::ROOT
        )->willReturn(
            $directoryMock
        );

        $model->expects($this->once())->method('getFullPath')->with($file)->willReturn('test_path');
        /** @var \Magento\Framework\View\Design\Theme\Customization\AbstractFile $model */
        /** @var File $file */
        $model->delete($file);
    }
}
