<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Phrase;
use Magento\Theme\Model\Uploader\Service;
use Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest;
use Psr\Log\LoggerInterface;

class UploadCssTest extends ThemeTest
{
    /** @var string  */
    protected $name = 'UploadCss';

    public function testExecute()
    {
        $serviceModel = $this->createMock(Service::class);
        $serviceModel->expects($this->once())
            ->method('uploadCssFile')
            ->with('css_file_uploader')
            ->willReturn(['filename' => 'filename', 'content' => 'content']);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(Service::class)
            ->willReturn($serviceModel);

        $jsonData = $this->createMock(Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => false, 'content' => 'content'])
            ->willReturn('{"filename":"filename","content":"content"}');

        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonData);

        $this->response
            ->expects($this->once())
            ->method('representJson')
            ->with('{"filename":"filename","content":"content"}');

        $this->_model->execute();
    }

    public function testExecuteWithLocalizedException()
    {
        $exception = new LocalizedException(new Phrase('Message'));
        $serviceModel = $this->createMock(Service::class);
        $serviceModel->expects($this->once())
            ->method('uploadCssFile')
            ->with('css_file_uploader')
            ->willThrowException($exception);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(Service::class)
            ->willReturn($serviceModel);

        $jsonData = $this->createMock(Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Message'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonData);

        $this->_model->execute();
    }

    public function testExecuteWithException()
    {
        $exception = new \Exception('Message');
        $serviceModel = $this->createMock(Service::class);
        $serviceModel->expects($this->once())
            ->method('uploadCssFile')
            ->with('css_file_uploader')
            ->willThrowException($exception);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(Service::class)
            ->willReturn($serviceModel);

        $logger = $this->getMockForAbstractClass(LoggerInterface::class, [], '', false);
        $logger->expects($this->once())
            ->method('critical');
        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($logger);

        $jsonData = $this->createMock(Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'We can\'t upload the CSS file right now.'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->_objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonData);

        $this->_model->execute();
    }
}
