<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as InvocationMocker;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCount;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Magento\Checkout\Helper\Data;
use Magento\Quote\Model\Quote;
use Magento\Framework\View\Result\Page;
use Magento\Checkout\Controller\Index\Index;
use Magento\Framework\ObjectManagerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    private $titleMock;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|MockObject
     */
    private $resultRedirectMock;

    protected function setUp(): void
    {
        // mock objects
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->basicMock(ObjectManagerInterface::class);
        $this->data = $this->basicMock(Data::class);
        $this->quote = $this->createPartialMock(
            Quote::class,
            ['getHasError', 'hasItems', 'validateMinimumAmount', 'hasError']
        );
        $this->contextMock = $this->basicMock(\Magento\Framework\App\Action\Context::class);
        $this->session = $this->basicMock(Session::class);
        $this->onepageMock = $this->basicMock(\Magento\Checkout\Model\Type\Onepage::class);
        $this->layoutMock = $this->basicMock(\Magento\Framework\View\Layout::class);
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSecure', 'getHeader'])
            ->getMock();
        $this->responseMock = $this->basicMock(\Magento\Framework\App\ResponseInterface::class);
        $this->redirectMock = $this->basicMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->resultPage = $this->basicMock(Page::class);
        $this->pageConfigMock = $this->basicMock(\Magento\Framework\View\Page\Config::class);
        $this->titleMock = $this->basicMock(\Magento\Framework\View\Page\Title::class);
        $this->url = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->resultRedirectMock = $this->basicMock(\Magento\Framework\Controller\Result\Redirect::class);

        $resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPage);

        $resultRedirectFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        // stubs
        $this->basicStub($this->onepageMock, 'getQuote')->willReturn($this->quote);
        $this->basicStub($this->resultPage, 'getLayout')->willReturn($this->layoutMock);

        $this->basicStub($this->layoutMock, 'getBlock')
            ->willReturn($this->basicMock(\Magento\Theme\Block\Html\Header::class));
        $this->basicStub($this->resultPage, 'getConfig')->willReturn($this->pageConfigMock);
        $this->basicStub($this->pageConfigMock, 'getTitle')->willReturn($this->titleMock);
        $this->basicStub($this->titleMock, 'set')->willReturn($this->titleMock);

        // objectManagerMock
        $objectManagerReturns = [
            [Data::class, $this->data],
            [\Magento\Checkout\Model\Type\Onepage::class, $this->onepageMock],
            [\Magento\Checkout\Model\Session::class, $this->basicMock(\Magento\Checkout\Model\Session::class)],
            [Session::class, $this->basicMock(Session::class)],

        ];
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap($objectManagerReturns);
        $this->basicStub($this->objectManagerMock, 'create')
            ->willReturn($this->basicMock(\Magento\Framework\UrlInterface::class));
        // context stubs
        $this->basicStub($this->contextMock, 'getObjectManager')->willReturn($this->objectManagerMock);
        $this->basicStub($this->contextMock, 'getRequest')->willReturn($this->request);
        $this->basicStub($this->contextMock, 'getResponse')->willReturn($this->responseMock);
        $this->basicStub($this->contextMock, 'getMessageManager')
            ->willReturn($this->basicMock(\Magento\Framework\Message\ManagerInterface::class));
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
     * @param string|null $referer
     * @param InvokedCount $expectedCall
     * @dataProvider sessionRegenerationDataProvider
     */
    public function testRegenerateSessionIdOnExecute(bool $secure, ?string $referer, InvokedCount $expectedCall)
    {
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
    public function sessionRegenerationDataProvider(): array
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
