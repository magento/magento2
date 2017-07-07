<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Link */
    protected $link;

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
     * @var \Magento\Downloadable\Model\Link
     */
    protected $linkModel;

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

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            [
                'setHttpResponseCode',
                'clearBody',
                'sendHeaders',
                'sendResponse',
                'setHeader'
            ]
        );
        $this->fileHelper = $this->getMock(
            \Magento\Downloadable\Helper\File::class,
            [
                'getFilePath'
            ],
            [],
            '',
            false
        );
        $this->downloadHelper = $this->getMock(
            \Magento\Downloadable\Helper\Download::class,
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
        $this->linkModel = $this->getMock(
            \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Link::class,
            [
                'load',
                'getId',
                'getLinkType',
                'getLinkUrl',
                'getSampleUrl',
                'getSampleType',
                'getBasePath',
                'getBaseSamplePath',
                'getLinkFile',
                'getSampleFile'
            ],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            [
                'create',
                'get'
            ],
            [],
            '',
            false
        );

        $this->link = $this->objectManagerHelper->getObject(
            \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Link::class,
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    /**
     * @dataProvider executeDataProvider
     * @param string $fileType
     */
    public function testExecuteFile($fileType)
    {
        $this->request->expects($this->at(0))->method('getParam')->with('id', 0)
            ->will($this->returnValue(1));
        $this->request->expects($this->at(1))->method('getParam')->with('type', 0)
            ->will($this->returnValue($fileType));
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('clearBody')
            ->will($this->returnSelf());
        $this->response->expects($this->any())->method('setHeader')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('sendHeaders')
            ->will($this->returnSelf());
        $this->objectManager->expects($this->at(1))->method('get')->with(\Magento\Downloadable\Helper\File::class)
            ->will($this->returnValue($this->fileHelper));
        $this->objectManager->expects($this->at(2))->method('get')->with(\Magento\Downloadable\Model\Link::class)
            ->will($this->returnValue($this->linkModel));
        $this->objectManager->expects($this->at(3))->method('get')->with(\Magento\Downloadable\Helper\Download::class)
            ->will($this->returnValue($this->downloadHelper));
        $this->fileHelper->expects($this->once())->method('getFilePath')
            ->will($this->returnValue('filepath/' . $fileType . '.jpg'));
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->will($this->returnSelf());
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->will($this->returnValue('link.jpg'));
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->will($this->returnSelf('file'));
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('output')
            ->will($this->returnSelf());
        $this->linkModel->expects($this->once())->method('load')
            ->will($this->returnSelf());
        $this->linkModel->expects($this->once())->method('getId')
        ->will($this->returnValue('1'));
        $this->linkModel->expects($this->any())->method('get' . $fileType . 'Type')
            ->will($this->returnValue('file'));
        $this->objectManager->expects($this->once())->method('create')
            ->will($this->returnValue($this->linkModel));

        $this->link->execute();
    }

    /**
     * @dataProvider executeDataProvider
     * @param string $fileType
     */
    public function testExecuteUrl($fileType)
    {
        $this->request->expects($this->at(0))->method('getParam')
            ->with('id', 0)->will($this->returnValue(1));
        $this->request->expects($this->at(1))->method('getParam')
            ->with('type', 0)->will($this->returnValue($fileType));
        $this->response->expects($this->once())->method('setHttpResponseCode')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('clearBody')
            ->will($this->returnSelf());
        $this->response->expects($this->any())->method('setHeader')
            ->will($this->returnSelf());
        $this->response->expects($this->once())->method('sendHeaders')
            ->will($this->returnSelf());
        $this->objectManager->expects($this->at(1))->method('get')->with(\Magento\Downloadable\Helper\Download::class)
            ->will($this->returnValue($this->downloadHelper));
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->will($this->returnSelf());
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->will($this->returnValue('link.jpg'));
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->will($this->returnSelf('url'));
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->will($this->returnValue(null));
        $this->downloadHelper->expects($this->once())->method('output')
            ->will($this->returnSelf());
        $this->linkModel->expects($this->once())->method('load')
            ->will($this->returnSelf());
        $this->linkModel->expects($this->once())->method('getId')
            ->will($this->returnValue('1'));
        $this->linkModel->expects($this->once())->method('get' . $fileType . 'Type')
            ->will($this->returnValue('url'));
        $this->linkModel->expects($this->once())->method('get' . $fileType . 'Url')
            ->will($this->returnValue('http://url.magento.com'));
        $this->objectManager->expects($this->once())->method('create')
            ->will($this->returnValue($this->linkModel));

        $this->link->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['link'],
            ['sample']
        ];
    }
}
