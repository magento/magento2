<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test of file abstract service
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Customization;

use Magento\Framework\App\Filesystem\DirectoryList;

class AbstractFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework_MockObject_MockBuilder
     */
    protected $_modelBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    protected function setUp()
    {
        $this->_customizationPath = $this->createMock(\Magento\Framework\View\Design\Theme\Customization\Path::class);
        $this->_fileFactory =
            $this->createPartialMock(\Magento\Framework\View\Design\Theme\FileFactory::class, ['create']);
        $this->_filesystem = $this->createMock(\Magento\Framework\Filesystem::class);

        $this->_modelBuilder = $this->getMockBuilder(
            \Magento\Framework\View\Design\Theme\Customization\AbstractFile::class
        )->setMethods(
            ['getType', 'getContentType']
        )->setConstructorArgs(
            [$this->_customizationPath, $this->_fileFactory, $this->_filesystem]
        );
    }

    protected function tearDown()
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
        $file = $this->createMock(\Magento\Theme\Model\Theme\File::class);
        $file->expects($this->once())->method('setCustomizationService')->with($model);
        $this->_fileFactory->expects($this->once())->method('create')->will($this->returnValue($file));
        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        $this->assertEquals($file, $model->create());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::getFullPath
     */
    public function testGetFullPath()
    {
        $model = $this->_modelBuilder->getMock();
        $theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $file = $this->createMock(\Magento\Theme\Model\Theme\File::class);

        $file->expects($this->any())->method('getTheme')->will($this->returnValue($theme));
        $file->expects($this->once())->method('getData')->with('file_path')->will($this->returnValue('file.path'));

        $this->_customizationPath->expects(
            $this->once()
        )->method(
            'getCustomizationPath'
        )->will(
            $this->returnValue('/path')
        );

        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        /** @var $file \Magento\Theme\Model\Theme\File */
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
        $model->expects($this->any())->method('getType')->will($this->returnValue($type));
        $model->expects($this->any())->method('getContentType')->will($this->returnValue($type));

        $files = [];
        foreach ($existedFiles as $fileData) {
            $file = $this->createPartialMock(\Magento\Theme\Model\Theme\File::class, ['__wakeup', 'save']);
            $file->setData($fileData);
            $files[] = $file;
        }
        $customization = $this->createMock(\Magento\Framework\View\Design\Theme\Customization::class);
        $customization->expects(
            $this->atLeastOnce()
        )->method(
            'getFilesByType'
        )->with(
            $type
        )->will(
            $this->returnValue($files)
        );

        $theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $theme->expects($this->any())->method('getCustomization')->will($this->returnValue($customization));

        $file = $this->createPartialMock(\Magento\Theme\Model\Theme\File::class, ['__wakeup', 'getTheme', 'save']);
        $file->expects($this->any())->method('getTheme')->will($this->returnValue($theme));
        $file->setData($fileContent);

        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        /** @var $file \Magento\Theme\Model\Theme\File */
        $model->prepareFile($file);
        $this->assertEquals($expectedContent, $file->getData());
    }

    /**
     * @return array
     */
    public function getTestContent()
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
        $model = $this->_modelBuilder->setMethods(['getFullPath', 'getType', 'getContentType'])->getMock();

        $file = $this->createPartialMock(\Magento\Theme\Model\Theme\File::class, ['__wakeup']);
        $file->setData(
            [
                'file_type' => 'js',
                'file_name' => 'test_3.js',
                'file_path' => 'js/test_3.js',
                'content' => 'test content',
                'sort_order' => 12,
            ]
        );
        $model->expects($this->once())->method('getFullPath')->with($file)->will($this->returnValue('test_path'));

        $directoryMock = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['writeFile', 'delete', 'getRelativePath']
        );
        $directoryMock->expects($this->once())->method('writeFile')->will($this->returnValue(true));
        $directoryMock->expects($this->once())->method('delete')->will($this->returnValue(true));

        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::ROOT
        )->will(
            $this->returnValue($directoryMock)
        );
        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        /** @var $file \Magento\Theme\Model\Theme\File */
        $model->save($file);
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::delete
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_deleteFileContent
     */
    public function testDelete()
    {
        $model = $this->_modelBuilder->setMethods(['getFullPath', 'getType', 'getContentType'])->getMock();
        $file = $this->createPartialMock(\Magento\Theme\Model\Theme\File::class, ['__wakeup']);
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
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['touch', 'delete', 'getRelativePath']
        );
        $directoryMock->expects($this->once())->method('touch')->will($this->returnValue(true));
        $directoryMock->expects($this->once())->method('delete')->will($this->returnValue(true));

        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::ROOT
        )->will(
            $this->returnValue($directoryMock)
        );

        $model->expects($this->once())->method('getFullPath')->with($file)->will($this->returnValue('test_path'));
        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        /** @var $file \Magento\Theme\Model\Theme\File */
        $model->delete($file);
    }
}
