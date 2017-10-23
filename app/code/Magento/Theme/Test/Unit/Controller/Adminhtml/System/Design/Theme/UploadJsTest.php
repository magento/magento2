<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

class UploadJsTest extends \Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest
{
    /** @var string */
    protected $name = 'UploadJs';

    /** @var  \Magento\Theme\Model\Uploader\Service|\PHPUnit_Framework_MockObject_MockObject */
    protected $serviceModel;

    /** @var  \Magento\Framework\View\Design\Theme\FlyweightFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeFactory;

    /** @var  \Magento\Framework\View\Design\Theme\Customization\File\Js|\PHPUnit_Framework_MockObject_MockObject */
    protected $customizationJs;

    /** @var  \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonHelper;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject  */
    protected $logger;

    /** @var \Magento\Framework\View\Design\Theme\CustomizationInterface|\PHPUnit_Framework_MockObject_MockObject  */
    protected $themeCustomization;

    protected function setUp()
    {
        parent::setUp();
        $this->serviceModel = $this->createMock(\Magento\Theme\Model\Uploader\Service::class);
        $this->themeFactory = $this->createMock(\Magento\Framework\View\Design\Theme\FlyweightFactory::class);
        $this->jsonHelper = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $this->themeCustomization = $this->getMockForAbstractClass(
            \Magento\Framework\View\Design\Theme\CustomizationInterface::class,
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
        $this->customizationJs = $this->createMock(\Magento\Framework\View\Design\Theme\Customization\File\Js::class);
    }

    public function testExecuteWithoutTheme()
    {
        $themeId = 23;

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $this->_objectManagerMock
            ->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Theme\Model\Uploader\Service::class)
            ->WillReturn($this->serviceModel);
        $this->_objectManagerMock
            ->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Framework\View\Design\Theme\FlyweightFactory::class)
            ->WillReturn($this->themeFactory);
        $this->_objectManagerMock
            ->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Framework\View\Design\Theme\Customization\File\Js::class)
            ->WillReturn($this->customizationJs);
        $this->_objectManagerMock
            ->expects($this->at(3))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->WillReturn($this->jsonHelper);

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

    public function testExecuteWithException()
    {
        $themeId = 23;

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Theme\Model\Uploader\Service::class)
            ->WillReturn($this->serviceModel);
        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Framework\View\Design\Theme\FlyweightFactory::class)
            ->WillReturn($this->themeFactory);
        $this->_objectManagerMock
            ->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Framework\View\Design\Theme\Customization\File\Js::class)
            ->WillReturn($this->customizationJs);
        $this->_objectManagerMock
            ->expects($this->at(4))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->WillReturn($this->jsonHelper);

        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Message'));

        $this->_objectManagerMock->expects($this->at(3))
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($this->logger);
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

    public function testExecute()
    {
        $themeId = 23;
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class, [], '', false);
        $jsFile = $this->getMockForAbstractClass(
            \Magento\Framework\View\Design\Theme\FileInterface::class,
            [],
            '',
            false,
            true,
            true,
            [
                'setTheme',
                'setFileName',
                'setData',
                'save',
            ]
        );

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Theme\Model\Uploader\Service::class)
            ->WillReturn($this->serviceModel);
        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Framework\View\Design\Theme\FlyweightFactory::class)
            ->WillReturn($this->themeFactory);
        $this->_objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Framework\View\Design\Theme\Customization\File\Js::class)
            ->WillReturn($this->customizationJs);
        $this->_objectManagerMock->expects($this->at(4))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->WillReturn($this->jsonHelper);

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
                \Magento\Framework\View\Design\Theme\CustomizationInterface::class,
                ['theme' => $theme]
            )
            ->willReturn($this->themeCustomization);
        $this->themeCustomization
            ->expects($this->once())
            ->method('getFilesByType')
            ->with(\Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE)
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
