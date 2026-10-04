<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\View\Design\Theme\Customization\FileInterface;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\Theme\FileInterface as ThemeFileInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Theme\Model\Theme\SingleFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SingleFileTest extends TestCase
{
    use MockCreationTrait;

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
        $this->file = $this->createMock(FileInterface::class);

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
        $customCss = $this->createPartialMockWithReflection(
            ThemeFileInterface::class,
            [
                'setData', 'getType', 'prepareFile',
                'delete', 'save', 'getContent', 'getFileInfo',
                'getFullPath', 'getFileName', 'setFileName',
                'getTheme', 'setTheme',
                'getCustomizationService', 'setCustomizationService'
            ]
        );
        $theme = $this->createPartialMockWithReflection(
            ThemeInterface::class,
            [
                'getCustomization',
                'getArea', 'getThemePath', 'getFullPath',
                'getParentTheme', 'getCode', 'isPhysical',
                'getInheritedThemes', 'getId'
            ]
        );
        $customization = $this->createMock(CustomizationInterface::class);

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
            ThemeFileInterface::class,
            $this->object->update($theme, $fileContent)
        );
    }

    /**
     * cover update method when fileContent is empty
     */
    public function testUpdateWhenFileDelete()
    {
        $customCss = $this->createPartialMockWithReflection(
            ThemeFileInterface::class,
            [
                'setData', 'getType', 'prepareFile',
                'delete', 'save', 'getContent', 'getFileInfo',
                'getFullPath', 'getFileName', 'setFileName',
                'getTheme', 'setTheme',
                'getCustomizationService', 'setCustomizationService'
            ]
        );
        $fileContent = '';
        $customFiles = [$customCss];
        $fileType = 'png';

        $theme = $this->createPartialMockWithReflection(
            ThemeInterface::class,
            [
                'getCustomization',
                'getArea', 'getThemePath', 'getFullPath',
                'getParentTheme', 'getCode', 'isPhysical',
                'getInheritedThemes', 'getId'
            ]
        );
        $customization = $this->createMock(CustomizationInterface::class);

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
            ThemeFileInterface::class,
            $this->object->update($theme, $fileContent)
        );
    }
}
