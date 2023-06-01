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
    /**
     * @var Link
     */
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

    /**
     * @inheritdoc
     */
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
        $this->linkModel = $this->getMockBuilder(Link::class)
            ->addMethods(
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
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->createPartialMock(
            ObjectManager::class,
            [
                'create',
                'get'
            ]
        );

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
     * @param string $fileType
     *
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecuteFile(string $fileType): void
    {
        $fileSize = 58493;
        $fileName = 'link.jpg';
        $this->request
            ->method('getParam')
            ->withConsecutive(['id', 0], ['type', 0])
            ->willReturnOnConsecutiveCalls(1, $fileType);
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
                    true
                ],
                ['Content-type', 'text/html'],
                ['Content-Length', $fileSize],
                ['Content-Disposition', 'attachment; filename=' . $fileName]
            )->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')->willReturnSelf();
        $this->objectManager
            ->method('get')
            ->withConsecutive([File::class], [\Magento\Downloadable\Model\Link::class], [Download::class])
            ->willReturnOnConsecutiveCalls($this->fileHelper, $this->linkModel, $this->downloadHelper);
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
     * @param string $fileType
     *
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecuteUrl(string $fileType): void
    {
        $this->request
            ->method('getParam')
            ->withConsecutive(['id', 0], ['type', 0])
            ->willReturnOnConsecutiveCalls(1, $fileType);
        $this->response->expects($this->once())->method('setHttpResponseCode')->willReturnSelf();
        $this->response->expects($this->once())->method('clearBody')->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')->willReturnSelf();
        $this->objectManager
            ->method('get')
            ->with(Download::class)
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
    public function executeDataProvider(): array
    {
        return [
            ['link'],
            ['sample']
        ];
    }
}
