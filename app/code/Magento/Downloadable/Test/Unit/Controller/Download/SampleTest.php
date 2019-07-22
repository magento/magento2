<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Controller\Download;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for \Magento\Downloadable\Controller\Download\Sample.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Downloadable\Controller\Download\Sample */
    protected $sample;

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
     * @var \Magento\Catalog\Model\Product\SalabilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $salabilityCheckerMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            [
                'setHttpResponseCode',
                'clearBody',
                'sendHeaders',
                'sendResponse',
                'setHeader',
                'setRedirect'
            ]
        );

        $this->helperData = $this->createPartialMock(\Magento\Downloadable\Helper\Data::class, [
                'getIsShareable'
            ]);
        $this->downloadHelper = $this->createPartialMock(\Magento\Downloadable\Helper\Download::class, [
                'setResource',
                'getFilename',
                'getContentType',
                'getFileSize',
                'getContentDisposition',
                'output'
            ]);
        $this->product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, [
                '_wakeup',
                'load',
                'getId',
                'getProductUrl',
                'getName'
            ]);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->redirect = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->urlInterface = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->salabilityCheckerMock = $this->createMock(\Magento\Catalog\Model\Product\SalabilityChecker::class);
        $this->objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, [
                'create',
                'get'
            ]);
        $this->sample = $this->objectManagerHelper->getObject(
            \Magento\Downloadable\Controller\Download\Sample::class,
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response,
                'messageManager' => $this->messageManager,
                'redirect' => $this->redirect,
                'salabilityChecker' => $this->salabilityCheckerMock,
            ]
        );
    }

    /**
     * Execute Download sample action with Sample Url.
     *
     * @return void
     */
    public function testExecuteSampleWithUrlType()
    {
        $sampleMock = $this->getMockBuilder(\Magento\Downloadable\Model\Sample::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'load', 'getSampleType', 'getSampleUrl'])
            ->getMock();

        $this->request->expects($this->once())->method('getParam')->with('sample_id', 0)->willReturn('some_sample_id');
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Downloadable\Model\Sample::class)
            ->willReturn($sampleMock);
        $sampleMock->expects($this->once())->method('load')->with('some_sample_id')->willReturnSelf();
        $sampleMock->expects($this->once())->method('getId')->willReturn('some_link_id');
        $this->salabilityCheckerMock->expects($this->once())->method('isSalable')->willReturn(true);
        $sampleMock->expects($this->once())->method('getSampleType')->willReturn(
            \Magento\Downloadable\Helper\Download::LINK_TYPE_URL
        );
        $sampleMock->expects($this->once())->method('getSampleUrl')->willReturn('sample_url');
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Downloadable\Helper\Download::class)
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

        $this->assertEquals($this->response, $this->sample->execute());
    }

    /**
     * Execute Download sample action with Sample File.
     *
     * @return void
     */
    public function testExecuteSampleWithFileType()
    {
        $sampleMock = $this->getMockBuilder(\Magento\Downloadable\Model\Sample::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'load', 'getSampleType', 'getSampleUrl', 'getBaseSamplePath'])
            ->getMock();
        $fileHelperMock = $this->getMockBuilder(\Magento\Downloadable\Helper\File::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilePath'])
            ->getMock();

        $this->request->expects($this->once())->method('getParam')->with('sample_id', 0)->willReturn('some_sample_id');
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Downloadable\Model\Sample::class)
            ->willReturn($sampleMock);
        $sampleMock->expects($this->once())->method('load')->with('some_sample_id')->willReturnSelf();
        $sampleMock->expects($this->once())->method('getId')->willReturn('some_sample_id');
        $this->salabilityCheckerMock->expects($this->once())->method('isSalable')->willReturn(true);
        $sampleMock->expects($this->any())->method('getSampleType')->willReturn(
            \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE
        );
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Downloadable\Helper\File::class)
            ->willReturn($fileHelperMock);
        $fileHelperMock->expects($this->once())->method('getFilePath')->willReturn('file_path');
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Downloadable\Helper\Download::class)
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

        $this->assertEquals($this->response, $this->sample->execute());
    }
}
