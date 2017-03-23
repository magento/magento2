<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

class UploadCssTest extends \Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest
{
    /** @var string  */
    protected $name = 'UploadCss';

    public function testExecute()
    {
        $serviceModel = $this->getMock(\Magento\Theme\Model\Uploader\Service::class, [], [], '', false);
        $serviceModel->expects($this->once())
            ->method('uploadCssFile')
            ->with('css_file_uploader')
            ->willReturn(['filename' => 'filename', 'content' => 'content']);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Theme\Model\Uploader\Service::class)
            ->willReturn($serviceModel);

        $jsonData = $this->getMock(\Magento\Framework\Json\Helper\Data::class, [], [], '', false);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => false, 'content' => 'content'])
            ->willReturn('{"filename":"filename","content":"content"}');

        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->willReturn($jsonData);

        $this->response
            ->expects($this->once())
            ->method('representJson')
            ->with('{"filename":"filename","content":"content"}');

        $this->_model->execute();
    }

    public function testExecuteWithLocalizedException()
    {
        $exception = new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Message'));
        $serviceModel = $this->getMock(\Magento\Theme\Model\Uploader\Service::class, [], [], '', false);
        $serviceModel->expects($this->once())
            ->method('uploadCssFile')
            ->with('css_file_uploader')
            ->willThrowException($exception);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Theme\Model\Uploader\Service::class)
            ->willReturn($serviceModel);

        $jsonData = $this->getMock(\Magento\Framework\Json\Helper\Data::class, [], [], '', false);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Message'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->willReturn($jsonData);

        $this->_model->execute();
    }

    public function testExecuteWithException()
    {
        $exception = new \Exception('Message');
        $serviceModel = $this->getMock(\Magento\Theme\Model\Uploader\Service::class, [], [], '', false);
        $serviceModel->expects($this->once())
            ->method('uploadCssFile')
            ->with('css_file_uploader')
            ->willThrowException($exception);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Theme\Model\Uploader\Service::class)
            ->willReturn($serviceModel);

        $logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $logger->expects($this->once())
            ->method('critical');
        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($logger);

        $jsonData = $this->getMock(\Magento\Framework\Json\Helper\Data::class, [], [], '', false);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'We can\'t upload the CSS file right now.'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->_objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->willReturn($jsonData);

        $this->_model->execute();
    }
}
