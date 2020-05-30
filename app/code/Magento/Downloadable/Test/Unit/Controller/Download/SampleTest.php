<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Controller\Download;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\SalabilityChecker;
use Magento\Downloadable\Controller\Download\Sample;
use Magento\Downloadable\Helper\Data;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Helper\File;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Downloadable\Controller\Download\Sample.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleTest extends TestCase
{
    /** @var \Magento\Downloadable\Controller\Download\Sample */
    protected $sample;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var MockObject|Http
     */
    protected $request;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $response;

    /**
     * @var MockObject|\Magento\Framework\ObjectManager\ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject|ManagerInterface
     */
    protected $messageManager;

    /**
     * @var MockObject|RedirectInterface
     */
    protected $redirect;

    /**
     * @var MockObject|Data
     */
    protected $helperData;

    /**
     * @var MockObject|\Magento\Downloadable\Helper\Download
     */
    protected $downloadHelper;

    /**
     * @var MockObject|Product
     */
    protected $product;

    /**
     * @var MockObject|UrlInterface
     */
    protected $urlInterface;

    /**
     * @var SalabilityChecker|MockObject
     */
    private $salabilityCheckerMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setHttpResponseCode', 'clearBody', 'sendHeaders', 'setHeader', 'setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();

        $this->helperData = $this->createPartialMock(
            Data::class,
            ['getIsShareable']
        );
        $this->downloadHelper = $this->createPartialMock(
            Download::class,
            [
                'setResource',
                'getFilename',
                'getContentType',
                'getFileSize',
                'getContentDisposition',
                'output'
            ]
        );
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['_wakeup'])
            ->onlyMethods(['load', 'getId', 'getProductUrl', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->urlInterface = $this->getMockForAbstractClass(UrlInterface::class);
        $this->salabilityCheckerMock = $this->createMock(SalabilityChecker::class);
        $this->objectManager = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create', 'get']
        );
        $this->sample = $this->objectManagerHelper->getObject(
            Sample::class,
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
            Download::LINK_TYPE_URL
        );
        $sampleMock->expects($this->once())->method('getSampleUrl')->willReturn('sample_url');
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with(Download::class)
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
        $fileHelperMock = $this->getMockBuilder(File::class)
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
            Download::LINK_TYPE_FILE
        );
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with(File::class)
            ->willReturn($fileHelperMock);
        $fileHelperMock->expects($this->once())->method('getFilePath')->willReturn('file_path');
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(Download::class)
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
