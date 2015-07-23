<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\File\Factory;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\File\Collector\Theme
     */
    protected $themeFileCollector;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    public function setup()
    {
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder('Magento\Framework\View\File\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeMock = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')
            ->getMock();

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($this->directoryMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->themeFileCollector = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\File\Collector\Theme',
            [
                'filesystem' => $this->filesystemMock,
                'fileFactory' => $this->fileFactoryMock
            ]
        );
    }

    public function testGetFilesEmpty()
    {
        $this->directoryMock->expects($this->any())
            ->method('search')
            ->willReturn([]);

        // Verify no files were returned
        $this->assertEquals([], $this->themeFileCollector->getFiles($this->themeMock, ''));
    }

    public function testGetFilesSingle()
    {
        $filePath = '/opt/magento2/app/design/frontend/Magento/blank/Magento_Customer/css/something.less';

        $fileMock = $this->getMockBuilder('Magento\Framework\View\File')
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryMock->expects($this->once())
            ->method('search')
            ->willReturn(['file']);
        $this->directoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with('file')
            ->willReturn($filePath);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with($filePath, null, $this->themeMock)
            ->willReturn($fileMock);

        // One file was returned from search
        $this->assertEquals([$fileMock], $this->themeFileCollector->getFiles($this->themeMock, 'css/*.less'));
    }

    public function testGetFilesMultiple()
    {
        $dirPath = '/Magento_Customer/css/';
        $themePath = '/opt/magento2/app/design/frontend/Magento/blank';
        $searchPath = 'css/*.test';

        $fileMock = $this->getMockBuilder('Magento\Framework\View\File')
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeMock->expects($this->any())
            ->method('getFullPath')
            ->willReturn($themePath);
        $this->directoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnMap(
                [
                    ['fileA.test', $dirPath . 'fileA.test'],
                    ['fileB.tst', $dirPath . 'fileB.tst'],
                    ['fileC.test', $dirPath . 'fileC.test'],
                ]
            );
        // Verifies correct files are searched for
        $this->directoryMock->expects($this->once())
            ->method('search')
            ->with($themePath . '/' . $searchPath)
            ->willReturn(['fileA.test', 'fileC.test']);
        // Verifies Magento_Customer was correctly produced from directory path
        $this->fileFactoryMock->expects($this->any())
            ->method('create')
            ->with($this->isType('string'), null, $this->themeMock)
            ->willReturn($fileMock);

        // Only two files should be in array, which were returned from search
        $this->assertEquals(
            [$fileMock, $fileMock],
            $this->themeFileCollector->getFiles($this->themeMock, 'css/*.test')
        );
    }
}
