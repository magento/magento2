<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Design\Theme\Customization\File\Js;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\Theme\FileInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Uploader\Service;
use Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class UploadJsTest extends ThemeTestCase
{
    /**
     * @var string
     */
    protected $name = 'UploadJs';

    /**
     * @var Service|MockObject
     */
    protected $serviceModel;

    /**
     * @var FlyweightFactory|MockObject
     */
    protected $themeFactory;

    /**
     * @var Js|MockObject
     */
    protected $customizationJs;

    /**
     * @var Data|MockObject
     */
    protected $jsonHelper;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var CustomizationInterface|MockObject
     */
    protected $themeCustomization;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceModel = $this->createMock(Service::class);
        $this->themeFactory = $this->createMock(FlyweightFactory::class);
        $this->jsonHelper = $this->createMock(Data::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class, [], '', false);
        $this->themeCustomization = $this->getMockForAbstractClass(
            CustomizationInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'generateFileInfo',
                'getFilesByType'
            ]
        );
        $this->customizationJs = $this->createMock(Js::class);
    }

    /**
     * @return void
     */
    public function testExecuteWithoutTheme(): void
    {
        $themeId = 23;

        $this->_request
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $this->_objectManagerMock
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [Service::class] => $this->serviceModel,
                [FlyweightFactory::class] => $this->themeFactory,
                [Js::class] => $this->customizationJs,
                [Data::class] => $this->jsonHelper
            });

        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willReturn(null);
        $this->jsonHelper
            ->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => "We cannot find a theme with id \"$themeId\"."])
            ->willReturn('{"error":"true","message":"We cannot find a theme with id "' . $themeId . '"."}');
        $this->response->expects($this->once())
            ->method('representJson')
            ->with('{"error":"true","message":"We cannot find a theme with id "' . $themeId . '"."}');

        $this->_model->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $themeId = 23;

        $this->_request
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Message'));

        $this->_objectManagerMock
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [Service::class] => $this->serviceModel,
                [FlyweightFactory::class] => $this->themeFactory,
                [Js::class] => $this->customizationJs,
                [LoggerInterface::class] => $this->logger,
                [Data::class] => $this->jsonHelper
            });
        $this->logger->expects($this->once())
            ->method('critical');

        $this->jsonHelper->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'We can\'t upload the JS file right now.'])
            ->willReturn('{"error":"true","message":"We can\'t upload the JS file right now."}');
        $this->response->expects($this->once())
            ->method('representJson')
            ->with('{"error":"true","message":"We can\'t upload the JS file right now."}');

        $this->_model->execute();
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $themeId = 23;
        $theme = $this->getMockForAbstractClass(ThemeInterface::class, [], '', false);
        $jsFile = $this->getMockForAbstractClass(
            FileInterface::class,
            [],
            '',
            false,
            true,
            true,
            [
                'setTheme',
                'setFileName',
                'setData',
                'save'
            ]
        );

        $this->_request
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $this->_objectManagerMock
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                    [Service::class] => $this->serviceModel,
                    [FlyweightFactory::class] => $this->themeFactory,
                    [Js::class] => $this->customizationJs,
                    [Data::class] => $this->jsonHelper
            });

        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willReturn($theme);
        $this->serviceModel
            ->expects($this->once())
            ->method('uploadJsFile')
            ->with('js_files_uploader')
            ->willReturn(['filename' => 'filename', 'content' => 'content']);
        $this->customizationJs
            ->expects($this->once())
            ->method('create')
            ->willReturn($jsFile);
        $jsFile->expects($this->once())
            ->method('setTheme')
            ->with($theme);
        $jsFile->expects($this->once())
            ->method('setFileName')
            ->with('filename');
        $jsFile->expects($this->once())
            ->method('setData')
            ->with('content', 'content');
        $jsFile->expects($this->once())
            ->method('save');

        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                CustomizationInterface::class,
                ['theme' => $theme]
            )
            ->willReturn($this->themeCustomization);
        $this->themeCustomization
            ->expects($this->once())
            ->method('getFilesByType')
            ->with(Js::TYPE)
            ->willReturn([$jsFile]);
        $this->themeCustomization
            ->expects($this->once())
            ->method('generateFileInfo')
            ->with([$jsFile])
            ->willReturn(['fileOne' => ['name' => 'name']]);

        $this->jsonHelper
            ->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => false, 'files' => ['fileOne' => ['name' => 'name']]])
            ->willReturn('{"error":false,"files":{"fileOne":{"name":"name"}}}');
        $this->response->expects($this->once())
            ->method('representJson')
            ->with('{"error":false,"files":{"fileOne":{"name":"name"}}}');

        $this->_model->execute();
    }
}
