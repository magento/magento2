<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Auth;

use Laminas\Uri\Http;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\BackendAppList;
use Magento\Backend\Controller\Adminhtml\Auth\Login;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Url;
use Magento\Backend\Model\UrlFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Auth\Login.
 */
class LoginTest extends TestCase
{
    /**
     * @var Login
     */
    private $controller;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var Auth|MockObject
     */
    private $authMock;

    /**
     * @var Http|MockObject
     */
    private $uriMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var BackendAppList|MockObject
     */
    private $backendAppListMock;

    /**
     * @var UrlFactory|MockObject
     */
    private $backendUrlFactoryMock;

    /**
     * @var Url|MockObject
     */
    private $backendUrlMock;

    /**
     * @var FrontNameResolver|MockObject
     */
    private $frontNameResolverMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Data|MockObject
     */
    private $helperMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->helperMock = $this->createMock(Data::class);
        $this->requestMock = $this->getMockBuilder(Request::class)
            ->setMethods(['getUri', 'getRequestUri'])
            ->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->authMock = $this->createMock(Auth::class);
        $this->backendAppListMock = $this->createMock(BackendAppList::class);
        $this->backendUrlMock = $this->createMock(Url::class);
        $this->backendUrlFactoryMock = $this->createMock(UrlFactory::class);
        $this->frontNameResolverMock = $this->createMock(FrontNameResolver::class);
        $this->uriMock = $this->createMock(Http::class);

        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->backendUrlFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->backendUrlMock);
        $this->requestMock->expects($this->any())
            ->method('getUri')
            ->willReturn($this->uriMock);

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->once())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $contextMock->expects($this->once())
            ->method('getAuth')
            ->willReturn($this->authMock);

        $this->controller = $objectManagerHelper->getObject(
            Login::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'backendAppList' => $this->backendAppListMock,
                'backendUrlFactory' => $this->backendUrlFactoryMock,
                'frontNameResolver' => $this->frontNameResolverMock,
            ]
        );
    }

    /**
     * Test for isValidBackendUri method.
     *
     * @param string $requestUri
     * @param string $baseUrl
     * @param string $backendFrontName
     * @param bool $redirect
     *
     * @dataProvider isValidBackendUriDataProvider
     */
    public function testIsValidBackendUri(string $requestUri, string $baseUrl, string $backendFrontName, bool $redirect)
    {
        $this->uriMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->authMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->requestMock->expects($this->once())->method('getRequestUri')->willReturn($requestUri);
        $this->backendUrlMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->frontNameResolverMock->expects($this->once())->method('getFrontName')->willReturn($backendFrontName);

        $this->resultPageFactoryMock->expects($this->exactly($redirect ? 0 : 1))->method('create');
        $this->resultRedirectFactoryMock->expects($this->exactly($redirect ? 1 : 0))->method('create');

        $this->controller->execute();
    }

    /**
     * Data provider for testIsValidBackendUri.
     *
     * @return array[]
     */
    public function isValidBackendUriDataProvider()
    {
        return [
            'Rewrites on, valid url' => ['/index.php/admin', 'http://magento2.local/', 'admin', true],
            'Rewrites on, invalid url' => ['/admin', 'http://magento2.local/', 'admin', false],
            'Rewrites off, valid url' => ['/index.php/admin', 'http://magento2.local/index.php/', 'admin', false],
            'Rewrites off, invalid url' => ['/admin', 'http://magento2.local/index.php/', 'admin', true],
        ];
    }
}
