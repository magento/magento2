<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Sample;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Sample as SampleModel;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Sample
     */
    protected $sample;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $sampleModel;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var File
     */
    protected $fileHelper;

    /**
     * @var Download
     */
    protected $downloadHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->request = $this->createMock(Http::class);
        $this->response = $this->createPartialMockWithReflection(
            ResponseInterface::class,
            [
                'setHttpResponseCode',
                'clearBody',
                'setHeader',
                'sendHeaders',
                'sendResponse'
            ]
        );
        $this->fileHelper = $this->createPartialMock(
            File::class,
            ['getFilePath']
        );
        $this->downloadHelper = $this->createPartialMock(
            Download::class,
            [
                'setResource',
                'getFilename',
                'getContentType',
                'output',
                'getFileSize',
                'getContentDisposition'
            ]
        );
        $this->sampleModel = $this->createPartialMockWithReflection(
            SampleModel::class,
            [
                'load',
                'getId',
                'getSampleType',
                'getSampleUrl',
                'getBasePath',
                'getBaseSamplePath',
                'getSampleFile'
            ]
        );
        $this->objectManager = $this->createPartialMock(
            ObjectManager::class,
            [
                'create',
                'get'
            ]
        );
        $this->sample = $this->objectManagerHelper->getObject(
            Sample::class,
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    /**
     * Execute download sample file action
     *
     * @return void
     */
    public function testExecuteFile(): void
    {
        $this->request
            ->method('getParam')
            ->with('id', 0)
            ->willReturn(1);
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('clearBody')
            ->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')
            ->willReturnSelf();
        $this->objectManager
            ->method('get')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == File::class) {
                    return $this->fileHelper;
                } elseif ($arg1 == \Magento\Downloadable\Model\Sample::class) {
                    return $this->sampleModel;
                } elseif ($arg1 == Download::class) {
                    return $this->downloadHelper;
                }
            });
        $this->fileHelper->expects($this->once())->method('getFilePath')
            ->willReturn('filepath/sample.jpg');
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->willReturn('sample.jpg');
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->willReturnSelf('file');
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->willReturn(null);
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->willReturn(null);
        $this->downloadHelper->expects($this->once())->method('output')
            ->willReturnSelf();
        $this->sampleModel->expects($this->once())->method('load')
            ->willReturnSelf();
        $this->sampleModel->expects($this->once())->method('getId')
            ->willReturn('1');
        $this->sampleModel->method('getSampleType')->willReturn('file');
        $this->objectManager->expects($this->once())->method('create')
            ->willReturn($this->sampleModel);

        $this->sample->execute();
    }

    /**
     * Execute download sample url action
     *
     * @return void
     */
    public function testExecuteUrl(): void
    {
        $this->request
            ->method('getParam')
            ->with('id', 0)
            ->willReturn(1);
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('clearBody')
            ->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')
            ->willReturnSelf();
        $this->objectManager
            ->method('get')
            ->with(Download::class)
            ->willReturn($this->downloadHelper);
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->willReturn('sample.jpg');
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->willReturnSelf('url');
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->willReturn(null);
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->willReturn(null);
        $this->downloadHelper->expects($this->once())->method('output')
            ->willReturnSelf();
        $this->sampleModel->expects($this->once())->method('load')
            ->willReturnSelf();
        $this->sampleModel->expects($this->once())->method('getId')
            ->willReturn('1');
        $this->sampleModel->method('getSampleType')->willReturn('url');
        $this->sampleModel->expects($this->once())->method('getSampleUrl')
            ->willReturn('http://example.com/simple.jpg');
        $this->objectManager->expects($this->once())->method('create')
            ->willReturn($this->sampleModel);

        $this->sample->execute();
    }
}
