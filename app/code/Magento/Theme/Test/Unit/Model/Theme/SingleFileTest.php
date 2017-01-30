<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use \Magento\Theme\Model\Theme\SingleFile;

class SingleFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SingleFile
     */
    protected $object;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\FileInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    /**
     * Initialize testable object
     */
    protected function setUp()
    {
        $this->file = $this->getMockBuilder('Magento\Framework\View\Design\Theme\Customization\FileInterface')
            ->getMock();

        $this->object = new SingleFile($this->file);
    }

    /**
     * cover update method
     */
    public function testUpdate()
    {
        $fileContent = 'file content';
        $customFiles = [];
        $fileType = 'png';
        $customCss = $this->getMockBuilder('Magento\Framework\View\Design\Theme\FileInterface')
            ->disableOriginalConstructor()
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
                    'setData',
                    'getType',
                    'prepareFile',
                ]
            )
            ->getMock();
        $theme = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')
            ->setMethods(
                [
                    'getArea',
                    'getThemePath',
                    'getFullPath',
                    'getParentTheme',
                    'getCode',
                    'isPhysical',
                    'getInheritedThemes',
                    'getId',
                    'getCustomization',
                ]
            )
            ->getMock();
        $customization = $this->getMockBuilder('Magento\Framework\View\Design\Theme\CustomizationInterface')
            ->getMock();

        $customCss->expects($this->once())
            ->method('setData')
            ->with('content', $fileContent);
        $customCss->expects($this->once())
            ->method('setTheme')
            ->with($theme);
        $customCss->expects($this->once())
            ->method('save');
        $this->file->expects($this->once())
            ->method('create')
            ->willReturn($customCss);
        $this->file->expects($this->once())
            ->method('getType')
            ->willReturn($fileType);
        $customization->expects($this->once())
            ->method('getFilesByType')
            ->with($fileType)
            ->willReturn($customFiles);
        $theme->expects($this->once())
            ->method('getCustomization')
            ->willReturn($customization);

        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $this->assertInstanceOf(
            'Magento\Framework\View\Design\Theme\FileInterface',
            $this->object->update($theme, $fileContent)
        );
    }

    /**
     * cover update method when fileContent is empty
     */
    public function testUpdateWhenFileDelete()
    {
        $customCss = $this->getMockBuilder('Magento\Framework\View\Design\Theme\FileInterface')
            ->disableOriginalConstructor()
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
                    'setData',
                    'getType',
                    'prepareFile',
                ]
            )
            ->getMock();
        $fileContent = '';
        $customFiles = [$customCss];
        $fileType = 'png';

        $theme = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')
            ->setMethods(
                [
                    'getArea',
                    'getThemePath',
                    'getFullPath',
                    'getParentTheme',
                    'getCode',
                    'isPhysical',
                    'getInheritedThemes',
                    'getId',
                    'getCustomization',
                ]
            )
            ->getMock();
        $customization = $this->getMockBuilder('Magento\Framework\View\Design\Theme\CustomizationInterface')
            ->getMock();

        $customCss->expects($this->once())
            ->method('delete');
        $this->file->expects($this->once())
            ->method('getType')
            ->willReturn($fileType);
        $customization->expects($this->once())
            ->method('getFilesByType')
            ->with($fileType)
            ->willReturn($customFiles);
        $theme->expects($this->once())
            ->method('getCustomization')
            ->willReturn($customization);

        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $this->assertInstanceOf(
            'Magento\Framework\View\Design\Theme\FileInterface',
            $this->object->update($theme, $fileContent)
        );
    }
}
