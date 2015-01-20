<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector;

use Magento\Framework\View\File\Factory;

/**
 * Tests Theme
 */
class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $themesDirectoryMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    public function setup()
    {
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()->getMock();

        $this->themesDirectoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->getMock();
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')
            ->will($this->returnValue($this->themesDirectoryMock));

        $this->fileFactoryMock = $this->getMockBuilder('Magento\Framework\View\File\Factory')
            ->disableOriginalConstructor()->getMock();

        $this->themeMock = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMock();
    }

    public function testGetFilesEmpty()
    {
        $this->themesDirectoryMock->expects($this->any())->method('search')->will($this->returnValue([]));
        $theme = new Theme(
            $this->filesystemMock,
            $this->fileFactoryMock
        );

        // Verify no files were returned
        $this->assertEquals([], $theme->getFiles($this->themeMock, ''));
    }

    public function testGetFilesSingle()
    {
        $filePath = '/opt/magento2/app/design/frontend/Magento/blank/Magento_Customer/css/something.less';
        $this->themesDirectoryMock->expects($this->once())
            ->method('search')
            ->will($this->returnValue(['file']));
        $this->themesDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with('file')
            ->will($this->returnValue($filePath));

        $fileMock = $this->getMockBuilder('Magento\Framework\View\Layout\File')
            ->getMock();

        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($filePath), null, $this->themeMock)
            ->will($this->returnValue($fileMock));

        $theme = new Theme(
            $this->filesystemMock,
            $this->fileFactoryMock
        );

        // One file was returned from search
        $this->assertEquals([$fileMock], $theme->getFiles($this->themeMock, 'css/*.less'));
    }

    public function testGetFilesMultiple()
    {
        $dirPath = '/Magento_Customer/css/';
        $themePath = '/opt/magento2/app/design/frontend/Magento/blank';
        $searchPath = 'css/*.test';
        $this->themeMock->expects($this->any())->method('getFullPath')
            ->will($this->returnValue($themePath));

        $this->themesDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will(
                $this->returnValueMap(
                    [
                        ['fileA.test', $dirPath . 'fileA.test'],
                        ['fileB.tst', $dirPath . 'fileB.tst'],
                        ['fileC.test', $dirPath . 'fileC.test'],
                    ]
                )
            );

        $fileMock = $this->getMockBuilder('Magento\Framework\View\Layout\File')
            ->getMock();

        // Verifies correct files are searched for
        $this->themesDirectoryMock->expects($this->once())
            ->method('search')
            ->with($themePath . '/' . $searchPath)
            ->will($this->returnValue(['fileA.test', 'fileC.test']));

        // Verifies Magento_Customer was correctly produced from directory path
        $this->fileFactoryMock->expects($this->any())
            ->method('create')
            ->with($this->isType('string'), null, $this->equalTo($this->themeMock))
            ->will($this->returnValue($fileMock));

        $theme = new Theme(
            $this->filesystemMock,
            $this->fileFactoryMock
        );
        // Only two files should be in array, which were returned from search
        $this->assertEquals([$fileMock, $fileMock], $theme->getFiles($this->themeMock, 'css/*.test'));
    }
}
