<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config\FileUploader;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Theme\Controller\Adminhtml\Design\Config\FileUploader\Save;
use Magento\Theme\Model\Design\Config\FileUploader\FileProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    /** @var Context|MockObject */
    protected $context;

    /** @var ResultFactory|MockObject */
    protected $resultFactory;

    /** @var ResultInterface|MockObject */
    protected $resultPage;

    /** @var FileProcessor|MockObject */
    protected $fileProcessor;

    /** @var Save */
    protected $controller;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPage = $this->getMockBuilder(ResultInterface::class)
            ->addMethods(['setData'])
            ->getMockForAbstractClass();
        $this->fileProcessor = $this->getMockBuilder(
            FileProcessor::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->controller = new Save($this->context, $this->fileProcessor);
    }

    protected function tearDown(): void
    {
        $_FILES = [];
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
