<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test of file abstract service
 */
namespace Magento\Framework\View\Design\Theme\Customization;

class AbstractFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
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
        $this->_customizationPath = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization\Path',
            array(),
            array(),
            '',
            false
        );
        $this->_fileFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\FileFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);

        $this->_modelBuilder = $this->getMockBuilder(
            'Magento\Framework\View\Design\Theme\Customization\AbstractFile'
        )->setMethods(
            array('getType', 'getContentType')
        )->setConstructorArgs(
            array($this->_customizationPath, $this->_fileFactory, $this->_filesystem)
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
        $file = $this->getMock('Magento\Core\Model\Theme\File', array(), array(), '', false);
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
        $theme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $file = $this->getMock('Magento\Core\Model\Theme\File', array(), array(), '', false);

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
        /** @var $file \Magento\Core\Model\Theme\File */
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

        $files = array();
        foreach ($existedFiles as $fileData) {
            $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'save'), array(), '', false);
            $file->setData($fileData);
            $files[] = $file;
        }
        $customization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            array(),
            array(),
            '',
            false
        );
        $customization->expects(
            $this->atLeastOnce()
        )->method(
            'getFilesByType'
        )->with(
            $type
        )->will(
            $this->returnValue($files)
        );

        $theme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $theme->expects($this->any())->method('getCustomization')->will($this->returnValue($customization));

        $file = $this->getMock(
            'Magento\Core\Model\Theme\File',
            array('__wakeup', 'getTheme', 'save'),
            array(),
            '',
            false
        );
        $file->expects($this->any())->method('getTheme')->will($this->returnValue($theme));
        $file->setData($fileContent);

        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        /** @var $file \Magento\Core\Model\Theme\File */
        $model->prepareFile($file);
        $this->assertEquals($expectedContent, $file->getData());
    }

    /**
     * @return array
     */
    public function getTestContent()
    {
        return array(
            'first_condition' => array(
                'type' => 'css',
                'fileContent' => array('file_name' => 'test.css', 'content' => 'test content', 'sort_order' => 1),
                'expectedContent' => array(
                    'file_type' => 'css',
                    'file_name' => 'test_1.css',
                    'file_path' => 'css/test_1.css',
                    'content' => 'test content',
                    'sort_order' => 2
                ),
                'existedFiles' => array(
                    array('id' => 1, 'file_path' => 'css/test.css', 'content' => 'test content', 'sort_order' => 1)
                )
            ),
            'second_condition' => array(
                'type' => 'js',
                'fileContent' => array('file_name' => 'test.js', 'content' => 'test content', 'sort_order' => 1),
                'expectedContent' => array(
                    'file_type' => 'js',
                    'file_name' => 'test_3.js',
                    'file_path' => 'js/test_3.js',
                    'content' => 'test content',
                    'sort_order' => 12
                ),
                'existedFiles' => array(
                    array('id' => 1, 'file_path' => 'js/test.js', 'content' => 'test content', 'sort_order' => 3),
                    array('id' => 2, 'file_path' => 'js/test_1.js', 'content' => 'test content', 'sort_order' => 5),
                    array('id' => 3, 'file_path' => 'js/test_2.js', 'content' => 'test content', 'sort_order' => 7),
                    array('id' => 4, 'file_path' => 'js/test_4.js', 'content' => 'test content', 'sort_order' => 9),
                    array('id' => 5, 'file_path' => 'js/test_5.js', 'content' => 'test content', 'sort_order' => 11)
                )
            )
        );
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::save
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_saveFileContent
     */
    public function testSave()
    {
        $model = $this->_modelBuilder->setMethods(array('getFullPath', 'getType', 'getContentType'))->getMock();

        $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup'), array(), '', false);
        $file->setData(
            array(
                'file_type' => 'js',
                'file_name' => 'test_3.js',
                'file_path' => 'js/test_3.js',
                'content' => 'test content',
                'sort_order' => 12
            )
        );
        $model->expects($this->once())->method('getFullPath')->with($file)->will($this->returnValue('test_path'));

        $directoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('writeFile', 'delete', 'getRelativePath'),
            array(),
            '',
            false
        );
        $directoryMock->expects($this->once())->method('writeFile')->will($this->returnValue(true));
        $directoryMock->expects($this->once())->method('delete')->will($this->returnValue(true));

        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::ROOT_DIR
        )->will(
            $this->returnValue($directoryMock)
        );
        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        /** @var $file \Magento\Core\Model\Theme\File */
        $model->save($file);
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::delete
     * @covers \Magento\Framework\View\Design\Theme\Customization\AbstractFile::_deleteFileContent
     */
    public function testDelete()
    {
        $model = $this->_modelBuilder->setMethods(array('getFullPath', 'getType', 'getContentType'))->getMock();
        $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup'), array(), '', false);
        $file->setData(
            array(
                'file_type' => 'js',
                'file_name' => 'test_3.js',
                'file_path' => 'js/test_3.js',
                'content' => 'test content',
                'sort_order' => 12
            )
        );
        $directoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('touch', 'delete', 'getRelativePath'),
            array(),
            '',
            false
        );
        $directoryMock->expects($this->once())->method('touch')->will($this->returnValue(true));
        $directoryMock->expects($this->once())->method('delete')->will($this->returnValue(true));

        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::ROOT_DIR
        )->will(
            $this->returnValue($directoryMock)
        );

        $model->expects($this->once())->method('getFullPath')->with($file)->will($this->returnValue('test_path'));
        /** @var $model \Magento\Framework\View\Design\Theme\Customization\AbstractFile */
        /** @var $file \Magento\Core\Model\Theme\File */
        $model->delete($file);
    }
}
