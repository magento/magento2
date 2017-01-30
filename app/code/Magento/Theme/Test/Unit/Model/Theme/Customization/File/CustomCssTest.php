<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Customization\File;

use \Magento\Theme\Model\Theme\Customization\File\CustomCss;

class CustomCssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Design\Theme\Customization\Path
     */
    protected $customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Design\Theme\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var CustomCss
     */
    protected $object;

    /**
     * Initialize testable object
     */
    public function setUp()
    {
        $this->customizationPath = $this->getMockBuilder('Magento\Framework\View\Design\Theme\Customization\Path')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactory = $this->getMockBuilder('Magento\Framework\View\Design\Theme\FileFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new CustomCss(
            $this->customizationPath,
            $this->fileFactory,
            $this->filesystem
        );
    }

    /**
     * cover _prepareSortOrder
     * cover _prepareFileName
     */
    public function testPrepareFile()
    {
        $file = $this->getMockBuilder('Magento\Framework\View\Design\Theme\FileInterface')
            ->setMethods(
                [
                    'delete',
                    'save',
                    'getContent',
                    'getFileInfo',
                    'getFullPath',
                    'getFileName',
                    'setFileName',
                    'getTheme',
                    'setTheme',
                    'getCustomizationService',
                    'setCustomizationService',
                    'getId',
                    'setData',
                ]
            )
            ->getMock();
        $file->expects($this->any())
            ->method('setData')
            ->willReturnMap(
                [
                    ['file_type', CustomCss::TYPE, $this->returnSelf()],
                    ['file_path', CustomCss::TYPE . '/' . CustomCss::FILE_NAME, $this->returnSelf()],
                    ['sort_order', CustomCss::SORT_ORDER, $this->returnSelf()],
                ]
            );
        $file->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $file->expects($this->at(0))
            ->method('getFileName')
            ->willReturn(null);
        $file->expects($this->at(1))
            ->method('getFileName')
            ->willReturn(CustomCss::FILE_NAME);
        $file->expects($this->once())
            ->method('setFileName')
            ->with(CustomCss::FILE_NAME);

        /** @var $file \Magento\Framework\View\Design\Theme\FileInterface */
        $this->assertInstanceOf(
            'Magento\Theme\Model\Theme\Customization\File\CustomCss',
            $this->object->prepareFile($file)
        );
    }
}
