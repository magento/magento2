<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Link;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Helper\File;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /** @var Link */
    protected $link;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $linkModel;

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

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setHttpResponseCode', 'clearBody', 'sendHeaders', 'setHeader'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();
        $this->fileHelper = $this->createPartialMock(File::class, [
            'getFilePath'
        ]);
        $this->downloadHelper = $this->createPartialMock(Download::class, [
            'setResource',
            'getFilename',
            'getContentType',
            'output',
            'getFileSize',
            'getContentDisposition'
        ]);
        $this->linkModel = $this->getMockBuilder(Link::class)
            ->addMethods([
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
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->createPartialMock(ObjectManager::class, [
            'create',
            'get'
        ]);

        $this->link = $this->objectManagerHelper->getObject(
            Link::class,
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
        $fileSize = 58493;
        $fileName = 'link.jpg';
        $this->request->expects($this->at(0))->method('getParam')->with('id', 0)
            ->willReturn(1);
        $this->request->expects($this->at(1))->method('getParam')->with('type', 0)
            ->willReturn($fileType);
        $this->response->expects($this->once())->method('setHttpResponseCode')->willReturnSelf();
        $this->response->expects($this->once())->method('clearBody')->willReturnSelf();
        $this->response
            ->expects($this->any())
            ->method('setHeader')
            ->withConsecutive(
                ['Pragma', 'public', true],
                [
                    'Cache-Control',
                    'must-revalidate, post-check=0, pre-check=0',
                    true,
                ],
                ['Content-type', 'text/html'],
                ['Content-Length', $fileSize],
                ['Content-Disposition', 'attachment; filename=' . $fileName]
            )->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')->willReturnSelf();
        $this->objectManager->expects($this->at(1))->method('get')->with(File::class)
            ->willReturn($this->fileHelper);
        $this->objectManager->expects($this->at(2))->method('get')->with(\Magento\Downloadable\Model\Link::class)
            ->willReturn($this->linkModel);
        $this->objectManager->expects($this->at(3))->method('get')->with(Download::class)
            ->willReturn($this->downloadHelper);
        $this->fileHelper->expects($this->once())->method('getFilePath')
            ->willReturn('filepath/' . $fileType . '.jpg');
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->willReturn($fileName);
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->willReturn('text/html');
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->willReturn($fileSize);
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->willReturn('inline');
        $this->downloadHelper->expects($this->once())->method('output')
            ->willReturnSelf();
        $this->linkModel->expects($this->once())->method('load')
            ->willReturnSelf();
        $this->linkModel->expects($this->once())->method('getId')
            ->willReturn('1');
        $this->linkModel->expects($this->any())->method('get' . $fileType . 'Type')
            ->willReturn('file');
        $this->objectManager->expects($this->once())->method('create')
            ->willReturn($this->linkModel);

        $this->link->execute();
    }

    /**
     * @dataProvider executeDataProvider
     * @param string $fileType
     */
    public function testExecuteUrl($fileType)
    {
        $this->request->expects($this->at(0))->method('getParam')
            ->with('id', 0)->willReturn(1);
        $this->request->expects($this->at(1))->method('getParam')
            ->with('type', 0)->willReturn($fileType);
        $this->response->expects($this->once())->method('setHttpResponseCode')->willReturnSelf();
        $this->response->expects($this->once())->method('clearBody')->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')->willReturnSelf();
        $this->objectManager->expects($this->at(1))->method('get')->with(Download::class)
            ->willReturn($this->downloadHelper);
        $this->downloadHelper->expects($this->once())->method('setResource')
            ->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('getFilename')
            ->willReturn('link.jpg');
        $this->downloadHelper->expects($this->once())->method('getContentType')
            ->willReturnSelf('url');
        $this->downloadHelper->expects($this->once())->method('getFileSize')
            ->willReturn(null);
        $this->downloadHelper->expects($this->once())->method('getContentDisposition')
            ->willReturn(null);
        $this->downloadHelper->expects($this->once())->method('output')
            ->willReturnSelf();
        $this->linkModel->expects($this->once())->method('load')
            ->willReturnSelf();
        $this->linkModel->expects($this->once())->method('getId')
            ->willReturn('1');
        $this->linkModel->expects($this->once())->method('get' . $fileType . 'Type')
            ->willReturn('url');
        $this->linkModel->expects($this->once())->method('get' . $fileType . 'Url')
            ->willReturn('http://url.magento.com');
        $this->objectManager->expects($this->once())->method('create')
            ->willReturn($this->linkModel);

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
