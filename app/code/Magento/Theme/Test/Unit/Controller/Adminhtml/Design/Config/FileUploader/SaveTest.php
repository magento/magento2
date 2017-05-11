<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config\FileUploader;

use Magento\Theme\Controller\Adminhtml\Design\Config\FileUploader\Save;
use Magento\Framework\Controller\ResultFactory;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultFactory;

    /** @var \Magento\Framework\Controller\ResultInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultPage;

    /** @var \Magento\Theme\Model\Design\Config\FileUploader\FileProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileProcessor;

    /** @var Save */
    protected $controller;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPage = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->fileProcessor = $this->getMockBuilder(
            \Magento\Theme\Model\Design\Config\FileUploader\FileProcessor::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->controller = new Save($this->context, $this->fileProcessor);
    }

    public function testExecute()
    {
        $_FILES['test_key'] = [];
        $result = [
            'file' => '',
            'url' => ''
        ];
        $resultJson = '{"file": "", "url": ""}';

        $this->fileProcessor->expects($this->once())
            ->method('saveToTmp')
            ->with('test_key')
            ->willReturn($result);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultPage);
        $this->resultPage->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturn($resultJson);
        $this->assertEquals($resultJson, $this->controller->execute());
    }
}
