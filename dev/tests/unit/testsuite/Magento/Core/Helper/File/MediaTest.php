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
namespace Magento\Core\Helper\File;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    const UPDATE_TIME = 'update_time';

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $dirMock;

    /** @var  Media */
    protected $helper;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->dirMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock = $this->getMockBuilder('Magento\Framework\App\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem::MEDIA_DIR)
            ->will($this->returnValue($this->dirMock));
        $dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $dateMock->expects($this->any())
            ->method('date')
            ->will($this->returnValue(self::UPDATE_TIME));
        $this->helper = $this->objectManager->getObject(
            'Magento\Core\Helper\File\Media',
            ['filesystem' => $filesystemMock, 'date' => $dateMock]
        );
    }

    /**
     * @param string $path
     * @param string $expectedDir
     * @param string $expectedFile
     * @dataProvider pathDataProvider
     */
    public function testCollectFileInfo($path, $expectedDir, $expectedFile)
    {
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';

        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->will($this->returnValue($relativePath));
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('readFile')
            ->with($relativePath)
            ->will($this->returnValue($content));

        $expected = [
            'filename' => $expectedFile,
            'content' => $content,
            'update_time' => self::UPDATE_TIME,
            'directory' => $expectedDir,
        ];

        $this->assertEquals($expected, $this->helper->collectFileInfo($mediaDirectory, $path));
    }

    public function pathDataProvider()
    {
        return [
            'file only' => ['filename', null, 'filename'],
            'with dir' => ['dir/filename', 'dir', 'filename'],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage File mediaDir/path does not exist
     */
    public function testCollectFileInfoNotFile()
    {
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';
        $path = 'path';
        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->will($this->returnValue($relativePath));
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->will($this->returnValue(false));
        $this->dirMock->expects($this->never())
            ->method('isReadable')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->never())
            ->method('readFile')
            ->with($relativePath)
            ->will($this->returnValue($content));

        $this->helper->collectFileInfo($mediaDirectory, $path);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage File mediaDir/path is not readable
     */
    public function testCollectFileInfoNotReadable()
    {
        $content = 'content';
        $mediaDirectory = 'mediaDir';
        $relativePath = 'relativePath';
        $path = 'path';
        $this->dirMock->expects($this->once())
            ->method('getRelativePath')
            ->with($mediaDirectory . '/' . $path)
            ->will($this->returnValue($relativePath));
        $this->dirMock->expects($this->once())
            ->method('isFile')
            ->with($relativePath)
            ->will($this->returnValue(true));
        $this->dirMock->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->will($this->returnValue(false));
        $this->dirMock->expects($this->never())
            ->method('readFile')
            ->with($relativePath)
            ->will($this->returnValue($content));

        $this->helper->collectFileInfo($mediaDirectory, $path);
    }
}
