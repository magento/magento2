<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Controller\Download;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Downloadable\Controller\Download\Link */
    protected $link;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\Link\Purchased\Item
     */
    protected $linkPurchasedItem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\Link\Purchased
     */
    protected $linkPurchased;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Session
     */
    protected $session;

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
        $this->session = $this->createPartialMock(\Magento\Customer\Model\Session::class, [
                'getCustomerId',
                'authenticate',
                'setBeforeAuthUrl'
            ]);
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
        $this->linkPurchasedItem = $this->createPartialMock(\Magento\Downloadable\Model\Link\Purchased\Item::class, [
                'load',
                'getId',
                'getProductId',
                'getPurchasedId',
                'getNumberOfDownloadsBought',
                'getNumberOfDownloadsUsed',
                'getStatus',
                'getLinkType',
                'getLinkUrl',
                'getLinkFile',
                'setNumberOfDownloadsUsed',
                'setStatus',
                'save',
            ]);
        $this->linkPurchased = $this->createPartialMock(\Magento\Downloadable\Model\Link\Purchased::class, [
                'load',
                'getCustomerId'
            ]);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->redirect = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->urlInterface = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, [
                'create',
                'get'
            ]);
        $this->link = $this->objectManagerHelper->getObject(
            \Magento\Downloadable\Controller\Download\Link::class,
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
            ->with(\Magento\Customer\Model\Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Downloadable\Model\Link\Purchased\Item::class)
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
            ->with(\Magento\Customer\Model\Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Downloadable\Model\Link\Purchased\Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Downloadable\Helper\Data::class)
            ->willReturn($this->helperData);
        $this->helperData->expects($this->once())
            ->method('getIsShareable')
            ->with($this->linkPurchasedItem)
            ->willReturn(false);
        $this->session->expects($this->once())->method('getCustomerId')->willReturn(null);
        $this->objectManager->expects($this->at(3))
            ->method('create')
            ->with(\Magento\Catalog\Model\Product::class)
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
            ->with(\Magento\Framework\UrlInterface::class)
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
            ->with(\Magento\Customer\Model\Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Downloadable\Model\Link\Purchased\Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Downloadable\Helper\Data::class)
            ->willReturn($this->helperData);
        $this->helperData->expects($this->once())
            ->method('getIsShareable')
            ->with($this->linkPurchasedItem)
            ->willReturn(false);
        $this->session->expects($this->once())->method('getCustomerId')->willReturn('customer_id');
        $this->objectManager->expects($this->at(3))
            ->method('create')
            ->with(\Magento\Downloadable\Model\Link\Purchased::class)
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
            ->with(\Magento\Customer\Model\Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Downloadable\Model\Link\Purchased\Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Downloadable\Helper\Data::class)
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
            ->method('addError')
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
            ->with(\Magento\Downloadable\Helper\Download::class)
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
            ->with(\Magento\Customer\Model\Session::class)
            ->willReturn($this->session);
        $this->request->expects($this->once())->method('getParam')->with('id', 0)->willReturn('some_id');
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Downloadable\Model\Link\Purchased\Item::class)
            ->willReturn($this->linkPurchasedItem);
        $this->linkPurchasedItem->expects($this->once())
            ->method('load')
            ->with('some_id', 'link_hash')
            ->willReturnSelf();
        $this->linkPurchasedItem->expects($this->once())->method('getId')->willReturn(5);
        $this->objectManager->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Downloadable\Helper\Data::class)
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
     * @return array
     */
    public function linkNotAvailableDataProvider()
    {
        return [
            ['addNotice', 'expired', 'The link has expired.'],
            ['addNotice', 'pending', 'The link is not available.'],
            ['addNotice', 'payment_review', 'The link is not available.'],
            ['addError', 'wrong_status', 'Something went wrong while getting the requested content.']
        ];
    }

    /**
     * @return array
     */
    public function downloadTypesDataProvider()
    {
        return [
            ['mimeType' => 'text/html',  'disposition' => \Zend_Mime::DISPOSITION_ATTACHMENT],
            ['mimeType' => 'image/jpeg', 'disposition' => \Zend_Mime::DISPOSITION_INLINE],
        ];
    }
}
