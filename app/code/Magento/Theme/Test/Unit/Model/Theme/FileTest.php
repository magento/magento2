<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\Theme\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var File
     */
    protected $model;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\FileServiceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileServiceFactory;

    /**
     * @var \Magento\Theme\Model\Resource\Theme\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Theme\Model\Resource\Theme\File\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollection;

    protected function setUp()
    {
        $context = $this->getMockBuilder('Magento\Framework\Model\Context')->disableOriginalConstructor()->getMock();
        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')->disableOriginalConstructor()->getMock();
        $this->themeFactory = $this->getMockBuilder('Magento\Framework\View\Design\Theme\FlyweightFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileServiceFactory = $this->getMockBuilder(
            'Magento\Framework\View\Design\Theme\Customization\FileServiceFactory'
        )->disableOriginalConstructor()->getMock();
        $this->resource = $this->getMockBuilder('Magento\Theme\Model\Resource\Theme\File')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollection = $this->getMockBuilder('Magento\Theme\Model\Resource\Theme\File\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->getMockBuilder('Magento\Framework\Event\ManagerInterface')->getMock());
        $validator = $this->getMockBuilder('Magento\Framework\Model\ActionValidator\RemoveAction')
                ->disableOriginalConstructor()
                ->getMock();
        $validator->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);
        $context->expects($this->once())
            ->method('getActionValidator')
            ->willReturn($validator);

        /** @var $context \Magento\Framework\Model\Context */
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
        $customization = $this->getMockBuilder('Magento\Framework\View\Design\Theme\Customization\FileInterface')
            ->getMock();

        /** @var $customization \Magento\Framework\View\Design\Theme\Customization\FileInterface */
        $this->assertInstanceOf(get_class($this->model), $this->model->setCustomizationService($customization));
    }

    /**
     * @test
     * @return void
     * @expectedException \UnexpectedValueException
     */
    public function testGetFullPathWithoutFileType()
    {
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
        $customization = $this->getMockBuilder('Magento\Framework\View\Design\Theme\Customization\FileInterface')
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
        $theme = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMock();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('getThemePath')
            ->willReturn($themePath);
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
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
        $theme = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMock();
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->with($themeId, DesignInterface::DEFAULT_AREA)
            ->willReturn($theme);
        $this->assertInstanceOf('Magento\Framework\View\Design\ThemeInterface', $this->model->getTheme());
    }

    /**
     * @test
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Theme id should be set
     */
    public function testGetThemeException()
    {
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
        $customization = $this->getMockBuilder('Magento\Framework\View\Design\Theme\Customization\FileInterface')
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
