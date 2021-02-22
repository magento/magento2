<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Store\App\Response\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    protected function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->wishlistProvider = $this->createMock(\Magento\Wishlist\Controller\WishlistProvider::class);
        $this->redirect = $this->createMock(\Magento\Store\App\Response\Redirect::class);
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
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
        $om = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);
        $url = $this->createMock(\Magento\Framework\Url::class);
        $actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);
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
     * @return \Magento\Wishlist\Controller\Index\Index
     */
    public function getController()
    {
        $this->prepareContext();
        return new \Magento\Wishlist\Controller\Index\Index(
            $this->context,
            $this->wishlistProvider
        );
    }

    /**
     */
    public function testExecuteWithoutWishlist()
    {
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);

        $this->getController()->execute();
    }

    public function testExecutePassed()
    {
        $wishlist = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->assertSame($this->resultPageMock, $this->getController()->execute());
    }
}
