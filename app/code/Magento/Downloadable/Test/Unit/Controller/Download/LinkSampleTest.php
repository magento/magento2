<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Controller\Download;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class LinkSampleTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Downloadable\Controller\Download\LinkSample */
    protected $linkSample;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManager\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Helper\Data
     */
    protected $helperData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Helper\Download
     */
    protected $downloadHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->request = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->response = $this->getMock(
            '\Magento\Framework\App\ResponseInterface',
            [
                'setHttpResponseCode',
                'clearBody',
                'sendHeaders',
                'sendResponse',
                'setHeader',
                'setRedirect'
            ]
        );

        $this->helperData = $this->getMock(
            'Magento\Downloadable\Helper\Data',
            [
                'getIsShareable'
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
                'getFileSize',
                'getContentDisposition',
                'output'
            ],
            [],
            '',
            false
        );
        $this->product = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                '_wakeup',
                'load',
                'getId',
                'getProductUrl',
                'getName'
            ],
            [],
            '',
            false
        );
        $this->messageManager = $this->getMock(
            'Magento\Framework\Message\ManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->redirect = $this->getMock(
            'Magento\Framework\App\Response\RedirectInterface',
            [],
            [],
            '',
            false
        );
        $this->urlInterface = $this->getMock(
            'Magento\Framework\UrlInterface',
            [],
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
        $this->linkSample = $this->objectManagerHelper->getObject(
            'Magento\Downloadable\Controller\Download\LinkSample',
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response,
                'messageManager' => $this->messageManager,
                'redirect' => $this->redirect
            ]
        );
    }

    public function testExecuteLinkTypeUrl()
    {
        $linkMock = $this->getMockBuilder('Magento\Downloadable\Model\Link')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'load', 'getSampleType', 'getSampleUrl'])
            ->getMock();

        $this->request->expects($this->once())->method('getParam')->with('link_id', 0)->willReturn('some_link_id');
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Downloadable\Model\Link')
            ->willReturn($linkMock);
        $linkMock->expects($this->once())->method('load')->with('some_link_id')->willReturnSelf();
        $linkMock->expects($this->once())->method('getId')->willReturn('some_link_id');
        $linkMock->expects($this->once())->method('getSampleType')->willReturn(
            \Magento\Downloadable\Helper\Download::LINK_TYPE_URL
        );
        $linkMock->expects($this->once())->method('getSampleUrl')->willReturn('sample_url');
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with('Magento\Downloadable\Helper\Download')
            ->willReturn($this->downloadHelper);
        $this->response->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('output')->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('Sorry, there was an error getting requested content. Please contact the store owner.')
            ->willReturnSelf();
        $this->redirect->expects($this->once())->method('getRedirectUrl')->willReturn('redirect_url');
        $this->response->expects($this->once())->method('setRedirect')->with('redirect_url')->willReturnSelf();

        $this->assertEquals($this->response, $this->linkSample->execute());
    }

    public function testExecuteLinkTypeFile()
    {
        $linkMock = $this->getMockBuilder('Magento\Downloadable\Model\Link')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'load', 'getSampleType', 'getSampleUrl', 'getBaseSamplePath'])
            ->getMock();
        $fileMock = $this->getMockBuilder('Magento\Downloadable\Helper\File')
            ->disableOriginalConstructor()
            ->setMethods(['getFilePath', 'load', 'getSampleType', 'getSampleUrl'])
            ->getMock();

        $this->request->expects($this->once())->method('getParam')->with('link_id', 0)->willReturn('some_link_id');
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with('Magento\Downloadable\Model\Link')
            ->willReturn($linkMock);
        $linkMock->expects($this->once())->method('load')->with('some_link_id')->willReturnSelf();
        $linkMock->expects($this->once())->method('getId')->willReturn('some_link_id');
        $linkMock->expects($this->any())->method('getSampleType')->willReturn(
            \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE
        );
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with('Magento\Downloadable\Helper\File')
            ->willReturn($fileMock);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with('Magento\Downloadable\Model\Link')
            ->willReturn($linkMock);
        $linkMock->expects($this->once())->method('getBaseSamplePath')->willReturn('downloadable/files/link_samples');
        $this->objectManager->expects($this->at(3))
            ->method('get')
            ->with('Magento\Downloadable\Helper\Download')
            ->willReturn($this->downloadHelper);
        $this->response->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->response->expects($this->any())->method('setHeader')->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('output')->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('Sorry, there was an error getting requested content. Please contact the store owner.')
            ->willReturnSelf();
        $this->redirect->expects($this->once())->method('getRedirectUrl')->willReturn('redirect_url');
        $this->response->expects($this->once())->method('setRedirect')->with('redirect_url')->willReturnSelf();

        $this->assertEquals($this->response, $this->linkSample->execute());
    }
}
