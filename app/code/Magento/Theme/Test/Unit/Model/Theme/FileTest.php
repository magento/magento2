<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Design\Theme\Customization\FileInterface;
use Magento\Framework\View\Design\Theme\Customization\FileServiceFactory;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\ResourceModel\Theme\File as ThemeResourceFile;
use Magento\Theme\Model\ResourceModel\Theme\File\Collection;
use Magento\Theme\Model\Theme\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    protected $model;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var FlyweightFactory|MockObject
     */
    protected $themeFactory;

    /**
     * @var FileServiceFactory|MockObject
     */
    protected $fileServiceFactory;

    /**
     * @var ThemeResourceFile|MockObject
     */
    protected $resource;

    /**
     * @var Collection|MockObject
     */
    protected $resourceCollection;

    protected function setUp(): void
    {
        $context = $this->createMock(
            Context::class
        );
        $this->registry = $this->createMock(
            Registry::class
        );
        $this->themeFactory = $this->createMock(FlyweightFactory::class);
        $this->fileServiceFactory = $this->createMock(
            FileServiceFactory::class
        );
        $this->resource = $this->createMock(ThemeResourceFile::class);
        $this->resourceCollection = $this->createMock(
            Collection::class
        );
        $context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->createMock(ManagerInterface::class));
        $validator = $this->createMock(RemoveAction::class);
        $validator->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);
        $context->expects($this->once())
            ->method('getActionValidator')
            ->willReturn($validator);

        /** @var Context $context */
        $this->model = new File(
            $context,
            $this->registry,
            $this->themeFactory,
            $this->fileServiceFactory,
            $this->resource,
            $this->resourceCollection
        );
    }

    /**
     * @test
     * @return void
     */
    public function testSetCustomizationService()
    {
        $customization = $this->createMock(FileInterface::class);

        /** @var $customization FileInterface */
        $this->assertInstanceOf(get_class($this->model), $this->model->setCustomizationService($customization));
    }

    /**
     * @test
     * @return void
     */
    public function testGetFullPathWithoutFileType()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->model->getFullPath();
    }

    /**
     * @test
     * @return void
     */
    public function testGetFullPath()
    {
        $fileServiceName = 'file_service';
        $fullPath = '/full/path';
        $customization = $this->createMock(FileInterface::class);

        $this->model->setData('file_type', $fileServiceName);
        $this->fileServiceFactory->expects($this->once())
            ->method('create')
            ->with($fileServiceName)
            ->willReturn($customization);
        $customization->expects($this->once())
            ->method('getFullPath')
            ->willReturn($fullPath);

        $this->assertEquals($fullPath, $this->model->getFullPath());
    }

    /**
     * @test
     * @return void
     */
    public function testSetTheme()
    {
        $themeId = 1;
        $themePath = '/path/to/theme';
        $theme = $this->createMock(ThemeInterface::class);
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('getThemePath')
            ->willReturn($themePath);
        /** @var ThemeInterface $theme */
        $this->model->setTheme($theme);
        $this->assertEquals($themeId, $this->model->getThemeId());
        $this->assertEquals($themePath, $this->model->getThemePath());
    }

    /**
     * @test
     * @return void
     */
    public function testGetTheme()
    {
        $themeId = 1;
        $this->model->setThemeId($themeId);
        $theme = $this->createMock(ThemeInterface::class);
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->with($themeId, DesignInterface::DEFAULT_AREA)
            ->willReturn($theme);
        $this->assertInstanceOf(ThemeInterface::class, $this->model->getTheme());
    }

    /**
     * @test
     * @return void
     */
    public function testGetThemeException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Theme id should be set');
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->with(null, DesignInterface::DEFAULT_AREA)
            ->willReturn(null);
        $this->model->getTheme();
    }

    /**
     * @test
     * @return void
     */
    public function testSetGetFileName()
    {
        $fileName = 'fileName';
        $this->assertInstanceOf(get_class($this->model), $this->model->setFileName($fileName));
        $this->assertEquals($fileName, $this->model->getFileName());
    }

    /**
     * @test
     * @return void
     */
    public function testGetContent()
    {
        $content = 'content';
        $this->model->setContent($content);
        $this->assertEquals($content, $this->model->getContent());
    }

    public function testGetFileInfo()
    {
        $fileId = 123;
        $fileName = 'fileName';
        $data = [
            'id' => $fileId,
            'name' => $fileName,
            'temporary' => 0,
        ];
        $this->model->setId($fileId);
        $this->model->setFileName($fileName);
        $this->model->setIsTemporary(false);

        $this->assertEquals($data, $this->model->getFileInfo());
    }

    /**
     * @test
     * @return void
     */
    public function testBeforeSaveDelete()
    {
        $fileServiceName = 'service_name';
        $customization = $this->createMock(FileInterface::class);
        $this->fileServiceFactory->expects($this->once())
            ->method('create')
            ->with($fileServiceName)
            ->willReturn($customization);
        $customization->expects($this->once())
            ->method('prepareFile')
            ->with($this->model)
            ->willReturnSelf();
        $customization->expects($this->once())
            ->method('save')
            ->with($this->model)
            ->willReturnSelf();
        $customization->expects($this->once())
            ->method('delete')
            ->with($this->model)
            ->willReturnSelf();

        $this->model->setData('file_type', $fileServiceName);
        $this->model->beforeSave();
        $this->model->beforeDelete();
    }
}
