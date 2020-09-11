<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Controller\Download;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Downloadable\Controller\Download\Link;
use Magento\Downloadable\Helper\Data;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\HTTP\Mime;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkTest extends TestCase
{
    /** @var Link */
    protected $link;

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
     * @var MockObject|Item
     */
    protected $linkPurchasedItem;

    /**
     * @var MockObject|Purchased
     */
    protected $linkPurchased;

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
     * @var MockObject|Session
     */
    protected $session;

    /**
     * @var MockObject|Data
     */
    protected $helperData;

    /**
     * @var MockObject|Download
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
        $this->session = $this->createPartialMock(Session::class, [
            'getCustomerId',
            'authenticate',
            'setBeforeAuthUrl'
        ]);
        $this->helperData = $this->createPartialMock(Data::class, [
            'getIsShareable'
        ]);
        $this->downloadHelper = $this->createPartialMock(Download::class, [
            'setResource',
            'getFilename',
            'getContentType',
            'getFileSize',
            'getContentDisposition',
            'output'
        ]);
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['_wakeup'])
            ->onlyMethods(['load', 'getId', 'getProductUrl', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkPurchasedItem = $this->getMockBuilder(Item::class)
            ->addMethods([
                'getProductId',
                'getPurchasedId',
                'getNumberOfDownloadsBought',
                'getNumberOfDownloadsUsed',
                'getStatus',
                'getLinkType',
                'getLinkUrl',
                'getLinkFile',
                'setNumberOfDownloadsUsed',
                'setStatus'
            ])
            ->onlyMethods(['load', 'getId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkPurchased = $this->getMockBuilder(Purchased::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->urlInterface = $this->getMockForAbstractClass(UrlInterface::class);
        $this->objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, [
            'create',
            'get'
        ]);
        $this->link = $this->objectManagerHelper->getObject(
            Link::class,
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response,
                'messageManager' => $this->messageManager,
                'redirect' => $this->redirect
            ]
        );
    }

    public function testAbsentLinkId()
    {
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(null);
        $this->messageManager->expects($this->once())
            ->method('addNotice')
            ->with("We can't find the link you requested.");
        $this->redirect->expects($this->once())->method('redirect')->with($this->response, '*/customer/products', []);

        $this->assertEquals($this->response, $this->link->execute());
    }

    public function testGetLinkForGuestCustomer()
    {
        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->helperData);
        $this->helperData->expects($this->once())
            ->method('getIsShareable')
            ->with($this->linkPurchasedItem)
            ->willReturn(false);
        $this->session->expects($this->once())->method('getCustomerId')->willReturn(null);
        $this->objectManager->expects($this->at(3))
            ->method('create')
            ->with(Product::class)
            ->willReturn($this->product);
        $this->linkPurchasedItem->expects($this->once())->method('getProductId')->willReturn('product_id');
        $this->product->expects($this->once())->method('load')->with('product_id')->willReturnSelf();
        $this->product->expects($this->once())->method('getId')->willReturn('product_id');
        $this->product->expects($this->once())->method('getProductUrl')->willReturn('product_url');
        $this->product->expects($this->once())->method('getName')->willReturn('product_name');
        $this->messageManager->expects($this->once())
            ->method('addNotice')
            ->with('Please sign in to download your product or purchase <a href="product_url">product_name</a>.');
        $this->session->expects($this->once())->method('authenticate')->willReturn(true);
        $this->objectManager->expects($this->at(4))
            ->method('create')
            ->with(UrlInterface::class)
            ->willReturn($this->urlInterface);
        $this->urlInterface->expects($this->once())
            ->method('getUrl')
            ->with('downloadable/customer/products/', ['_secure' => true])
            ->willReturn('before_auth_url');
        $this->session->expects($this->once())->method('setBeforeAuthUrl')->with('before_auth_url')->willReturnSelf();

        $this->assertNull($this->link->execute());
    }

    public function testGetLinkForWrongCustomer()
    {
        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->helperData);
        $this->helperData->expects($this->once())
            ->method('getIsShareable')
            ->with($this->linkPurchasedItem)
            ->willReturn(false);
        $this->session->expects($this->once())->method('getCustomerId')->willReturn('customer_id');
        $this->objectManager->expects($this->at(3))
            ->method('create')
            ->with(Purchased::class)
            ->willReturn($this->linkPurchased);
        $this->linkPurchasedItem->expects($this->once())->method('getPurchasedId')->willReturn('purchased_id');
        $this->linkPurchased->expects($this->once())->method('load')->with('purchased_id')->willReturnSelf();
        $this->linkPurchased->expects($this->once())->method('getCustomerId')->willReturn('other_customer_id');
        $this->messageManager->expects($this->once())
            ->method('addNotice')
            ->with("We can't find the link you requested.");
        $this->redirect->expects($this->once())->method('redirect')->with($this->response, '*/customer/products', []);

        $this->assertEquals($this->response, $this->link->execute());
    }

    /**
     * @param string $mimeType
     * @param string $disposition
     * @dataProvider downloadTypesDataProvider
     * @return void
     */
    public function testExceptionInUpdateLinkStatus($mimeType, $disposition)
    {
        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->helperData);
        $this->helperData->expects($this->once())
            ->method('getIsShareable')
            ->with($this->linkPurchasedItem)
            ->willReturn(true);
        $this->linkPurchasedItem->expects($this->any())->method('getNumberOfDownloadsBought')->willReturn(10);
        $this->linkPurchasedItem->expects($this->any())->method('getNumberOfDownloadsUsed')->willReturn(9);
        $this->linkPurchasedItem->expects($this->once())->method('getStatus')->willReturn('available');
        $this->linkPurchasedItem->expects($this->once())->method('getLinkType')->willReturn('url');
        $this->linkPurchasedItem->expects($this->once())->method('getLinkUrl')->willReturn('link_url');

        $this->processDownload('link_url', 'url', $mimeType, $disposition);

        $this->linkPurchasedItem->expects($this->any())->method('setNumberOfDownloadsUsed')->willReturnSelf();
        $this->linkPurchasedItem->expects($this->any())->method('setStatus')->with('expired')->willReturnSelf();
        $this->linkPurchasedItem->expects($this->any())->method('save')->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Something went wrong while getting the requested content.')
            ->willReturnSelf();
        $this->redirect->expects($this->once())->method('redirect')->with($this->response, '*/customer/products', []);

        $this->assertEquals($this->response, $this->link->execute());
    }

    /**
     * @param string $resource
     * @param string $resourceType
     * @param string $mimeType
     * @param string $disposition
     * @return void
     */
    private function processDownload($resource, $resourceType, $mimeType, $disposition)
    {
        $fileSize = 58493;
        $fileName = 'link.jpg';

        $this->objectManager->expects($this->at(3))
            ->method('get')
            ->with(Download::class)
            ->willReturn($this->downloadHelper);
        $this->downloadHelper->expects($this->once())
            ->method('setResource')
            ->with($resource, $resourceType)
            ->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('getFilename')->willReturn($fileName);
        $this->downloadHelper->expects($this->once())->method('getContentType')->willReturn($mimeType);
        $this->response->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->response
            ->expects($this->any())
            ->method('setHeader')
            ->withConsecutive(
                ['Pragma', 'public', true],
                ['Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true],
                ['Content-type', $mimeType, true],
                ['Content-Length', $fileSize],
                ['Content-Disposition', $disposition . '; filename=' . $fileName]
            )
            ->willReturnSelf();

        $this->downloadHelper->expects($this->once())->method('getContentDisposition')->willReturn($disposition);
        $this->downloadHelper->expects($this->once())->method('getFileSize')->willReturn($fileSize);
        $this->response->expects($this->once())->method('clearBody')->willReturnSelf();
        $this->response->expects($this->once())->method('sendHeaders')->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('output');
    }

    /**
     * @param string $messageType
     * @param string $status
     * @param string $notice
     * @dataProvider linkNotAvailableDataProvider
     */
    public function testLinkNotAvailable($messageType, $status, $notice)
    {
        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->helperData);
        $this->helperData->expects($this->once())
            ->method('getIsShareable')
            ->with($this->linkPurchasedItem)
            ->willReturn(true);
        $this->linkPurchasedItem->expects($this->any())->method('getNumberOfDownloadsBought')->willReturn(10);
        $this->linkPurchasedItem->expects($this->any())->method('getNumberOfDownloadsUsed')->willReturn(9);
        $this->linkPurchasedItem->expects($this->once())->method('getStatus')->willReturn($status);
        $this->messageManager->expects($this->once())->method($messageType)->with($notice)->willReturnSelf();

        $this->assertEquals($this->response, $this->link->execute());
    }

    /**
     * @param string $mimeType
     * @param string $disposition
     * @dataProvider downloadTypesDataProvider
     * @return void
     */
    public function testContentDisposition($mimeType, $disposition)
    {
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [
                    Session::class,
                    $this->session,
                ],
                [
                    Data::class,
                    $this->helperData,
                ],
                [
                    Download::class,
                    $this->downloadHelper,
                ],
            ]);

        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->helperData->expects($this->once())
            ->method('getIsShareable')
            ->with($this->linkPurchasedItem)
            ->willReturn(true);
        $this->linkPurchasedItem->expects($this->any())->method('getNumberOfDownloadsBought')->willReturn(10);
        $this->linkPurchasedItem->expects($this->any())->method('getNumberOfDownloadsUsed')->willReturn(9);
        $this->linkPurchasedItem->expects($this->once())->method('getStatus')->willReturn('available');
        $this->linkPurchasedItem->expects($this->once())->method('getLinkType')->willReturn('url');
        $this->linkPurchasedItem->expects($this->once())->method('getLinkUrl')->willReturn('link_url');

        $fileSize = 58493;
        $fileName = 'link.jpg';

        $this->downloadHelper->expects($this->once())
            ->method('setResource')
            ->with('link_url', 'url')
            ->willReturnSelf();
        $this->downloadHelper->expects($this->once())->method('getFilename')->willReturn($fileName);
        $this->downloadHelper->expects($this->once())->method('getContentType')->willReturn($mimeType);
        $this->response->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->response
            ->expects($this->any())
            ->method('setHeader')
            ->withConsecutive(
                ['Pragma', 'public', true],
                ['Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true],
                ['Content-type', $mimeType, true],
                ['Content-Length', $fileSize],
                ['Content-Disposition', $disposition . '; filename=' . $fileName]
            )
            ->willReturnSelf();

        $this->assertEquals($this->response, $this->link->execute());
    }

    /**
     * @return array
     */
    public function linkNotAvailableDataProvider()
    {
        return [
            ['addNotice', 'expired', 'The link has expired.'],
            ['addNotice', 'pending', 'The link is not available.'],
            ['addNotice', 'payment_review', 'The link is not available.'],
            ['addErrorMessage', 'wrong_status', 'Something went wrong while getting the requested content.']
        ];
    }

    /**
     * @return array
     */
    public function downloadTypesDataProvider()
    {
        return [
            ['mimeType' => 'text/html',  'disposition' => Mime::DISPOSITION_ATTACHMENT],
            ['mimeType' => 'image/jpeg', 'disposition' => Mime::DISPOSITION_INLINE],
        ];
    }
}
