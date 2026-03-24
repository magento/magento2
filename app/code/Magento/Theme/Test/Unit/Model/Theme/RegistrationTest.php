<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\Theme\Image;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Theme\Model\ResourceModel\Theme\Data\Collection as ThemeDataCollection;
use Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory as ThemeDataCollectionFactory;
use Magento\Theme\Model\Theme\Data\Collection;
use Magento\Theme\Model\Theme\Registration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegistrationTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Registration
     */
    protected $model;

    /**
     * @var ThemeDataCollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var Collection|MockObject
     */
    protected $filesystemCollection;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createPartialMock(
            ThemeDataCollectionFactory::class,
            ['create']
        );
        $this->filesystemCollection = $this->createMock(Collection::class);

        $this->model = new Registration(
            $this->collectionFactory,
            $this->filesystemCollection
        );
    }

    /**
     * @test
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRegister()
    {
        $image = 'preview.jpg';
        $themeFilePath = 'any/path';
        $parentId = 1;
        $fullPath = '/full/path';
        $theme = $this->createPartialMockWithReflection(
            ThemeInterface::class,
            [
                'getArea', 'getThemePath', 'getFullPath', 'getParentTheme',
                'getCode', 'isPhysical', 'getInheritedThemes', 'getId',
                'setParentId', 'getCustomization', 'getPreviewImage',
                'getThemeImage', 'setType', 'save'
            ]
        );
        $parentTheme = $this->createMock(ThemeInterface::class);
        $parentThemeFromCollectionId = 123;
        $parentThemeFromCollection = $this->createPartialMockWithReflection(
            ThemeInterface::class,
            [
                'getArea', 'getThemePath', 'getFullPath', 'getParentTheme',
                'getCode', 'isPhysical', 'getInheritedThemes', 'getId',
                'getType'
            ]
        );
        $themeFromCollection = $this->createPartialMockWithReflection(
            ThemeInterface::class,
            [
                'getArea', 'getThemePath', 'getFullPath', 'getParentTheme',
                'getCode', 'isPhysical', 'getInheritedThemes', 'getId',
                'setType', 'save', 'getType', 'getParentId', 'setParentId'
            ]
        );
        $collection = $this->createMock(ThemeDataCollection::class);
        $customization = $this->createMock(CustomizationInterface::class);
        $imageModel = $this->createMock(Image::class);

        $theme->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('setType')
            ->willReturn(ThemeInterface::TYPE_PHYSICAL);
        $theme->expects($this->any())
            ->method('setParentId')
            ->willReturn($parentId);
        $theme->expects($this->any())
            ->method('getFullPath')
            ->willReturn($fullPath);
        $theme->expects($this->any())
            ->method('getParentTheme')
            ->willReturn($parentTheme);
        $parentTheme->expects($this->any())
            ->method('getId')
            ->willReturn($parentId);
        $this->collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($collection);
        $this->filesystemCollection->expects($this->once())
            ->method('clear');
        $this->filesystemCollection->expects($this->once())
            ->method('hasTheme')
            ->with($themeFromCollection)
            ->willReturn(false);
        $this->filesystemCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$theme]));
        $collection->expects($this->once())
            ->method('getThemeByFullPath')
            ->with($fullPath)
            ->willReturn($theme);
        $theme->expects($this->once())
            ->method('getCustomization')
            ->willReturn($customization);
        $customization->expects($this->once())
            ->method('getThemeFilesPath')
            ->willReturn($themeFilePath);
        $theme->expects($this->any())
            ->method('getPreviewImage')
            ->willReturn($image);
        $theme->expects($this->once())
            ->method('getThemeImage')
            ->willReturn($imageModel);
        $imageModel->expects($this->once())
            ->method('createPreviewImage')
            ->with($themeFilePath . '/' . $image)
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addTypeFilter')
            ->with(ThemeInterface::TYPE_PHYSICAL)
            ->willReturnSelf();
        $collection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$themeFromCollection]));
        $collection->expects($this->any())
            ->method('addTypeRelationFilter')
            ->willReturnSelf();
        $themeFromCollection->expects($this->once())
            ->method('setType')
            ->with(ThemeInterface::TYPE_VIRTUAL)
            ->willReturnSelf();
        $themeFromCollection->expects($this->any())
            ->method('save')
            ->willReturnSelf();
        $themeFromCollection->expects($this->any())
            ->method('getParentTheme')
            ->willReturn($parentThemeFromCollection);
        $themeFromCollection->expects($this->any())
            ->method('getType')
            ->willReturn(ThemeInterface::TYPE_STAGING);
        $themeFromCollection->expects($this->any())
            ->method('getParentId')
            ->willReturn(null);
        $themeFromCollection->expects($this->any())
            ->method('setParentId')
            ->with($parentThemeFromCollectionId)
            ->willReturnSelf();
        $parentThemeFromCollection->expects($this->any())
            ->method('getType')
            ->willReturn(ThemeInterface::TYPE_VIRTUAL);
        $parentThemeFromCollection->expects($this->any())
            ->method('getId')
            ->willReturn($parentThemeFromCollectionId);

        $this->assertInstanceOf(get_class($this->model), $this->model->register());
    }
}
