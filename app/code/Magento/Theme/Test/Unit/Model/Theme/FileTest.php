<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Design\Theme\Customization\FileInterface;
use Magento\Framework\View\Design\Theme\Customization\FileServiceFactory;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\ResourceModel\Theme\File\Collection;
use Magento\Theme\Model\Theme\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var \Magento\Theme\Model\ResourceModel\Theme\File|MockObject
     */
    protected $resource;

    /**
     * @var Collection|MockObject
     */
    protected $resourceCollection;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(
            Context::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder(
            Registry::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->themeFactory = $this->getMockBuilder(FlyweightFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileServiceFactory = $this->getMockBuilder(
            FileServiceFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->getMockBuilder(ManagerInterface::class)
            ->getMock());
        $validator = $this->getMockBuilder(RemoveAction::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $customization = $this->getMockBuilder(FileInterface::class)
            ->getMock();

        /** @var $customization \Magento\Framework\View\Design\Theme\Customization\FileInterface */
        $this->assertInstanceOf(get_class($this->model), $this->model->setCustomizationService($customization));
    }

    /**
     * @test
     * @return void
     */
    public function testGetFullPathWithoutFileType()
    {
        $this->expectException('UnexpectedValueException');
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
        $customization = $this->getMockBuilder(FileInterface::class)
            ->getMock();

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
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->getMock();
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
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->getMock();
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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
        $customization = $this->getMockBuilder(FileInterface::class)
            ->getMock();
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
