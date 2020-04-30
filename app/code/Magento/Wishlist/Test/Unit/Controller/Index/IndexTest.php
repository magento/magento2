<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Url;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Store\App\Response\Redirect;
use Magento\Wishlist\Controller\Index\Index;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $response;

    /**
     * @var WishlistProvider|MockObject
     */
    protected $wishlistProvider;

    /**
     * @var Redirect|MockObject
     */
    protected $redirect;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Layout|MockObject
     */
    protected $layoutMock;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(Http::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->wishlistProvider = $this->createMock(WishlistProvider::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE, [])
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
    }

    protected function prepareContext()
    {
        $om = $this->createMock(ObjectManager::class);
        $eventManager = $this->createMock(Manager::class);
        $url = $this->createMock(Url::class);
        $actionFlag = $this->createMock(ActionFlag::class);
        $messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($om);
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context
            ->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $this->context
            ->expects($this->any())
            ->method('getUrl')
            ->willReturn($url);
        $this->context
            ->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($actionFlag);
        $this->context
            ->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($messageManager);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
    }

    /**
     * @return Index
     */
    public function getController()
    {
        $this->prepareContext();
        return new Index(
            $this->context,
            $this->wishlistProvider
        );
    }

    public function testExecuteWithoutWishlist()
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);

        $this->getController()->execute();
    }

    public function testExecutePassed()
    {
        $wishlist = $this->createMock(Wishlist::class);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->assertSame($this->resultPageMock, $this->getController()->execute());
    }
}
