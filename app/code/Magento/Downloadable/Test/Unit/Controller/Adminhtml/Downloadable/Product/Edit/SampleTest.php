<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SampleTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->getMock(
            '\Magento\Framework\App\ResponseInterface',
            [
                'setHttpResponseCode',
                'clearBody',
                'sendHeaders',
                'sendResponse',
                'setHeader'
            ]
        );
        $this->fileHelper = $this->getMock(
            '\Magento\Downloadable\Helper\File',
            [
                'getFilePath'
            ],
            [],
            '',
            false
        );
        $this->downloadHelper = $this->getMock(
            'Magento\Downloadable\Helper\Download',
            [
                'setResource',
                'getFilename',
                'getContentType',
                'output',
                'getFileSize',
                'getContentDisposition'
            ],
            [],
            '',
            false
        );
        $this->sampleModel = $this->getMock(
            '\Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Sample',
            [
                'load',
                'getId',
                'getSampleType',
                'getSampleUrl',
                'getBasePath',
                'getBaseSamplePath',
                'getSampleFile',
            ],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager\ObjectManager',
            [
                'create',
                'get'
            ],
            [],
            '',
            false
        );
        $this->sample = $this->objectManagerHelper->getObject(
            'Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Sample',
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
            ->will($this->returnValue(1));
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('clearBody')
            ->will($this->returnSelf());
        $this->response->expects($this->any())->method('setHeader')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('sendHeaders')
            ->will($this->returnSelf());
        $this->objectManager->expects($this->at(1))->method('get')->with('Magento\Downloadable\Helper\File')
            ->will($this->returnValue($this->fileHelper));
        $this->objectManager->expects($this->at(2))->method('get')->with('Magento\Downloadable\Model\Sample')
            ->will($this->returnValue($this->sampleModel));
        $this->objectManager->expects($this->at(3))->method('get')->with('Magento\Downloadable\Helper\Download')
            ->will($this->returnValue($this->downloadHelper));
        $this->fileHelper->expects($this->once())->method('getFilePath')
            ->will($this->returnValue('filepath/sample.jpg'));
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->will($this->returnSelf());
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->will($this->returnValue('sample.jpg'));
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->will($this->returnSelf('file'));
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('output')
            ->will($this->returnSelf());
        $this->sampleModel->expects($this->once())->method('load')
            ->will($this->returnSelf());
        $this->sampleModel->expects($this->once())->method('getId')
            ->will($this->returnValue('1'));
        $this->sampleModel->expects($this->any())->method('getSampleType')
            ->will($this->returnValue('file'));
        $this->objectManager->expects($this->once())->method('create')
            ->will($this->returnValue($this->sampleModel));

        $this->sample->execute();
    }

    /**
     * Execute download sample url action
     */
    public function testExecuteUrl()
    {
        $this->request->expects($this->at(0))->method('getParam')->with('id', 0)
            ->will($this->returnValue(1));
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('clearBody')
            ->will($this->returnSelf());
        $this->response->expects($this->any())->method('setHeader')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('sendHeaders')
            ->will($this->returnSelf());
        $this->objectManager->expects($this->at(1))->method('get')->with('Magento\Downloadable\Helper\Download')
            ->will($this->returnValue($this->downloadHelper));
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->will($this->returnSelf());
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->will($this->returnValue('sample.jpg'));
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->will($this->returnSelf('url'));
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('output')
            ->will($this->returnSelf());
        $this->sampleModel->expects($this->once())->method('load')
            ->will($this->returnSelf());
        $this->sampleModel->expects($this->once())->method('getId')
            ->will($this->returnValue('1'));
        $this->sampleModel->expects($this->any())->method('getSampleType')
            ->will($this->returnValue('url'));
        $this->objectManager->expects($this->once())->method('create')
            ->will($this->returnValue($this->sampleModel));

        $this->sample->execute();
    }
}
