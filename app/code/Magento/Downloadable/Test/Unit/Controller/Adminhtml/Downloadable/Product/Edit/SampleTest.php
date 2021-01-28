<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SampleTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Sample */
    protected $sample;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $sampleModel;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\Downloadable\Helper\Download
     */
    protected $downloadHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            [
                'setHttpResponseCode',
                'clearBody',
                'sendHeaders',
                'sendResponse',
                'setHeader'
            ]
        );
        $this->fileHelper = $this->createPartialMock(\Magento\Downloadable\Helper\File::class, [
                'getFilePath'
            ]);
        $this->downloadHelper = $this->createPartialMock(\Magento\Downloadable\Helper\Download::class, [
                'setResource',
                'getFilename',
                'getContentType',
                'output',
                'getFileSize',
                'getContentDisposition'
            ]);
        $this->sampleModel = $this->createPartialMock(
            \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Sample::class,
            [
                'load',
                'getId',
                'getSampleType',
                'getSampleUrl',
                'getBasePath',
                'getBaseSamplePath',
                'getSampleFile',
            ]
        );
        $this->objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, [
                'create',
                'get'
            ]);
        $this->sample = $this->objectManagerHelper->getObject(
            \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Sample::class,
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    /**
     * Execute download sample file action
     */
    public function testExecuteFile()
    {
        $this->request->expects($this->at(0))->method('getParam')->with('id', 0)
            ->willReturn(1);
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('clearBody')
            ->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')
            ->willReturnSelf();
        $this->objectManager->expects($this->at(1))->method('get')->with(\Magento\Downloadable\Helper\File::class)
            ->willReturn($this->fileHelper);
        $this->objectManager->expects($this->at(2))->method('get')->with(\Magento\Downloadable\Model\Sample::class)
            ->willReturn($this->sampleModel);
        $this->objectManager->expects($this->at(3))->method('get')->with(\Magento\Downloadable\Helper\Download::class)
            ->willReturn($this->downloadHelper);
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
        $this->sampleModel->expects($this->any())->method('getSampleType')
            ->willReturn('file');
        $this->objectManager->expects($this->once())->method('create')
            ->willReturn($this->sampleModel);

        $this->sample->execute();
    }

    /**
     * Execute download sample url action
     */
    public function testExecuteUrl()
    {
        $this->request->expects($this->at(0))->method('getParam')->with('id', 0)
            ->willReturn(1);
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('clearBody')
            ->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')
            ->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')
            ->willReturnSelf();
        $this->objectManager->expects($this->at(1))->method('get')->with(\Magento\Downloadable\Helper\Download::class)
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
        $this->sampleModel->expects($this->any())->method('getSampleType')
            ->willReturn('url');
        $this->objectManager->expects($this->once())->method('create')
            ->willReturn($this->sampleModel);

        $this->sample->execute();
    }
}
