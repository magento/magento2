<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config\FileUploader;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Theme\Controller\Adminhtml\Design\Config\FileUploader\Save;
use Magento\Theme\Model\Design\Config\FileUploader\FileProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    use MockCreationTrait;

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
        $this->context = $this->createMock(Context::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultPage = $this->createPartialMockWithReflection(
            ResultInterface::class,
            ['setHttpResponseCode', 'setHeader', 'renderResult', 'setData']
        );
        $this->fileProcessor = $this->createMock(FileProcessor::class);
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
        $this->resultPage->method('setData')
            ->with($result)
            ->willReturn($resultJson);
        $this->assertEquals($resultJson, $this->controller->execute());
    }
}
