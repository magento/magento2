<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\View\Design\Theme\Customization\FileInterface;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\SingleFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SingleFileTest extends TestCase
{
    /**
     * @var SingleFile
     */
    protected $object;

    /**
     * @var FileInterface|MockObject
     */
    protected $file;

    /**
     * Initialize testable object
     */
    protected function setUp(): void
    {
        $this->file = $this->getMockBuilder(FileInterface::class)
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
        $customCss = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\FileInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setData',
                    'getType',
                    'prepareFile'
                ]
            )
            ->onlyMethods(
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
                    'setCustomizationService'
                ]
            )
            ->getMock();
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->addMethods(['getCustomization'])
            ->onlyMethods(
                [
                    'getArea',
                    'getThemePath',
                    'getFullPath',
                    'getParentTheme',
                    'getCode',
                    'isPhysical',
                    'getInheritedThemes',
                    'getId',
                ]
            )
            ->getMockForAbstractClass();
        $customization = $this->getMockBuilder(CustomizationInterface::class)
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

        /** @var ThemeInterface $theme */
        $this->assertInstanceOf(
            \Magento\Framework\View\Design\Theme\FileInterface::class,
            $this->object->update($theme, $fileContent)
        );
    }

    /**
     * cover update method when fileContent is empty
     */
    public function testUpdateWhenFileDelete()
    {
        $customCss = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\FileInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setData', 'getType', 'prepareFile'])
            ->onlyMethods(
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
                    'setCustomizationService'
                ]
            )
            ->getMock();
        $fileContent = '';
        $customFiles = [$customCss];
        $fileType = 'png';

        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->addMethods(['getCustomization'])
            ->onlyMethods(
                [
                    'getArea',
                    'getThemePath',
                    'getFullPath',
                    'getParentTheme',
                    'getCode',
                    'isPhysical',
                    'getInheritedThemes',
                    'getId',
                ]
            )
            ->getMockForAbstractClass();
        $customization = $this->getMockBuilder(CustomizationInterface::class)
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

        /** @var ThemeInterface $theme */
        $this->assertInstanceOf(
            \Magento\Framework\View\Design\Theme\FileInterface::class,
            $this->object->update($theme, $fileContent)
        );
    }
}
