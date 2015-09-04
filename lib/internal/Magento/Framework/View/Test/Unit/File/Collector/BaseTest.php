<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\File\Collector;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\File;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\File\Collector\Base
     */
    protected $fileCollector;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\View\File\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\View\Helper\PathPattern|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pathPatternHelperMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    /**
     * @var \Magento\Framework\Module\Dir\Search|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dirSearch;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder('Magento\Framework\View\File\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pathPatternHelperMock = $this->getMockBuilder('Magento\Framework\View\Helper\PathPattern')
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->getMockForAbstractClass();
        $this->themeMock = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')
            ->setMethods(['getData'])
            ->getMockForAbstractClass();

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->directoryMock);

        $this->dirSearch = $this->getMock('Magento\Framework\Module\Dir\Search', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->fileCollector = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\File\Collector\Base',
            [
                'filesystem' => $this->filesystemMock,
                'fileFactory' => $this->fileFactoryMock,
                'pathPatternHelper' => $this->pathPatternHelperMock,
                'subDir' => 'layout',
                'dirSearch' => $this->dirSearch,
            ]
        );
    }

    public function testGetFiles()
    {
        $sharedFiles = [
            'Namespace/One/view/base/layout/one.xml',
            'Namespace/Two/view/base/layout/two.xml'
        ];
        $themeFiles = [
            'Namespace/Two/view/frontend/layout/four.txt',
            'Namespace/Two/view/frontend/layout/three.xml'
        ];

        $this->directoryMock->expects($this->any())
            ->method('search')
            ->willReturnMap(
                [
                    ['*/*/view/base/layout/*.xml', null, $sharedFiles],
                    ['*/*/view/frontend/layout/*.xml', null, $themeFiles]
                ]
            );

        $this->dirSearch->expects($this->any())
            ->method('collectFiles')
            ->willReturnMap(
                [
                    ['view/base/layout/*.xml', $sharedFiles],
                    ['view/frontend/layout/*.xml', $themeFiles]
                ]
            );
        $this->pathPatternHelperMock->expects($this->once())
            ->method('translatePatternFromGlob')
            ->with('*.xml')
            ->willReturn('[^/]*\\.xml');
        $this->directoryMock->expects($this->atLeastOnce())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);
        $this->fileFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->createFileMock());
        $this->themeMock->expects($this->once())
            ->method('getData')
            ->with('area')
            ->willReturn('frontend');

        $result = $this->fileCollector->getFiles($this->themeMock, '*.xml');
        $this->assertCount(3, $result);
        $this->assertInstanceOf('Magento\Framework\View\File', $result[0]);
        $this->assertInstanceOf('Magento\Framework\View\File', $result[1]);
        $this->assertInstanceOf('Magento\Framework\View\File', $result[2]);
    }

    /**
     * Create file mock object
     *
     * @return \Magento\Framework\View\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFileMock()
    {
        return $this->getMockBuilder('Magento\Framework\View\File')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
