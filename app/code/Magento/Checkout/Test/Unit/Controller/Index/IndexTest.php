<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Index;

use Magento\Checkout\Controller\Index\Index;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Magento\Theme\Block\Html\Header;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $objectManagerMock;

    /**
     * @var Data|MockObject
     */
    private $data;

    /**
     * @var MockObject
     */
    private $quote;

    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var MockObject
     */
    private $onepageMock;

    /**
     * @var MockObject
     */
    private $layoutMock;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var MockObject
     */
    private $responseMock;

    /**
     * @var MockObject
     */
    private $redirectMock;

    /**
     * @var Index
     */
    private $model;

    /**
     * @var Page|MockObject
     */
    private $resultPage;

    /**
     * @var Config
     */
    private $pageConfigMock;

    /**
     * @var Title
     */
    private $titleMock;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    protected function setUp(): void
    {
        // mock objects
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->basicMock(ObjectManagerInterface::class);
        $this->data = $this->basicMock(Data::class);
        $this->quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getHasError', 'hasError'])
            ->onlyMethods(['hasItems', 'validateMinimumAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->basicMock(Context::class);
        $this->session = $this->basicMock(Session::class);
        $this->onepageMock = $this->basicMock(Onepage::class);
        $this->layoutMock = $this->basicMock(Layout::class);
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isSecure', 'getHeader'])
            ->getMock();
        $this->responseMock = $this->basicMock(ResponseInterface::class);
        $this->redirectMock = $this->basicMock(RedirectInterface::class);
        $this->resultPage = $this->basicMock(Page::class);
        $this->pageConfigMock = $this->basicMock(Config::class);
        $this->titleMock = $this->basicMock(Title::class);
        $this->url = $this->getMockForAbstractClass(UrlInterface::class);
        $this->resultRedirectMock = $this->basicMock(Redirect::class);

        $resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPage);

        $resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        // stubs
        $this->basicStub($this->onepageMock, 'getQuote')->willReturn($this->quote);
        $this->basicStub($this->resultPage, 'getLayout')->willReturn($this->layoutMock);

        $this->basicStub($this->layoutMock, 'getBlock')
            ->willReturn($this->basicMock(Header::class));
        $this->basicStub($this->resultPage, 'getConfig')->willReturn($this->pageConfigMock);
        $this->basicStub($this->pageConfigMock, 'getTitle')->willReturn($this->titleMock);
        $this->basicStub($this->titleMock, 'set')->willReturn($this->titleMock);

        // objectManagerMock
        $objectManagerReturns = [
            [Data::class, $this->data],
            [Onepage::class, $this->onepageMock],
            [\Magento\Checkout\Model\Session::class, $this->basicMock(\Magento\Checkout\Model\Session::class)],
            [Session::class, $this->basicMock(Session::class)],

        ];
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap($objectManagerReturns);
        $this->basicStub($this->objectManagerMock, 'create')
            ->willReturn($this->basicMock(UrlInterface::class));
        // context stubs
        $this->basicStub($this->contextMock, 'getObjectManager')->willReturn($this->objectManagerMock);
        $this->basicStub($this->contextMock, 'getRequest')->willReturn($this->request);
        $this->basicStub($this->contextMock, 'getResponse')->willReturn($this->responseMock);
        $this->basicStub($this->contextMock, 'getMessageManager')
            ->willReturn($this->basicMock(ManagerInterface::class));
        $this->basicStub($this->contextMock, 'getRedirect')->willReturn($this->redirectMock);
        $this->basicStub($this->contextMock, 'getUrl')->willReturn($this->url);
        $this->basicStub($this->contextMock, 'getResultRedirectFactory')->willReturn($resultRedirectFactoryMock);

        // SUT
        $this->model = $this->objectManager->getObject(
            Index::class,
            [
                'context' => $this->contextMock,
                'customerSession' => $this->session,
                'resultPageFactory' => $resultPageFactoryMock,
                'resultRedirectFactory' => $resultRedirectFactoryMock
            ]
        );
    }

    /**
     * Checks a case when session should be or not regenerated during the request.
     *
     * @param bool $secure
     * @param string $referer
     * @param InvokedCount $expectedCall
     * @dataProvider sessionRegenerationDataProvider
     */
    public function testRegenerateSessionIdOnExecute(
        bool $secure,
        ?string $referer,
        \PHPUnit\Framework\MockObject\Rule\InvokedCount $expectedCall
    ) {
        $this->data->method('canOnepageCheckout')
            ->willReturn(true);
        $this->quote->method('hasItems')
            ->willReturn(true);
        $this->quote->method('getHasError')
            ->willReturn(false);
        $this->quote->method('validateMinimumAmount')
            ->willReturn(true);
        $this->session->method('isLoggedIn')
            ->willReturn(true);
        $this->request->method('isSecure')
            ->willReturn($secure);
        $this->request->method('getHeader')
            ->with('referer')
            ->willReturn($referer);

        $this->session->expects($expectedCall)
            ->method('regenerateId');
        $this->assertSame($this->resultPage, $this->model->execute());
    }

    /**
     * Gets list of variations for generating new session.
     *
     * @return array
     */
    public static function sessionRegenerationDataProvider(): array
    {
        return [
            [
                'secure' => false,
                'referer' => 'https://test.domain.com/',
                'expectedCall' => self::once()
            ],
            [
                'secure' => true,
                'referer' => null,
                'expectedCall' => self::once()
            ],
            [
                'secure' => true,
                'referer' => 'http://test.domain.com/',
                'expectedCall' => self::once()
            ],
            // This is the only case in which session regeneration can be skipped
            [
                'secure' => true,
                'referer' => 'https://test.domain.com/',
                'expectedCall' => self::never()
            ],
        ];
    }

    public function testOnepageCheckoutNotAvailable()
    {
        $this->basicStub($this->data, 'canOnepageCheckout')->willReturn(false);
        $expectedPath = 'checkout/cart';

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($expectedPath)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    public function testInvalidQuote()
    {
        $this->basicStub($this->quote, 'hasError')->willReturn(true);

        $expectedPath = 'checkout/cart';
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($expectedPath)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    /**
     * @param MockObject $mock
     * @param string $method
     *
     * @return InvocationMocker
     */
    private function basicStub($mock, $method): InvocationMocker
    {
        return $mock->method($method)
            ->withAnyParameters();
    }

    /**
     * @param string $className
     * @return MockObject
     */
    private function basicMock(string $className): MockObject
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
