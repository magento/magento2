<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Controller;

use Laminas\Stdlib\Parameters;
use Laminas\Stdlib\ParametersInterface;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Controller\Router;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for UrlRewrite Controller Router
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var ActionFactory|MockObject
     */
    private $actionFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $url;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ParametersInterface|MockObject
     */
    private $requestQuery;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $urlFinder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->actionFactory = $this->createMock(ActionFactory::class);
        $this->url = $this->getMockForAbstractClass(UrlInterface::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();
        $this->requestQuery = $this->getMockBuilder(Parameters::class)
            ->onlyMethods(['__serialize', '__unserialize'])
            ->getMock();
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->method('getQuery')->willReturn($this->requestQuery);
        $this->urlFinder = $this->getMockForAbstractClass(UrlFinderInterface::class);
        $this->store = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->router = $objectManager->getObject(
            Router::class,
            [
                'actionFactory' => $this->actionFactory,
                'url' => $this->url,
                'storeManager' => $this->storeManager,
                'response' => $this->response,
                'urlFinder' => $this->urlFinder
            ]
        );
    }

    /**
     * @return void
     */
    public function testNoRewriteExist()
    {
        $this->request->method('getPathInfo')
            ->willReturn('');
        $this->request->method('getRequestString')
            ->willReturn('');
        $this->urlFinder->method('findOneByData')
            ->willReturn(null);
        $this->storeManager->method('getStore')
            ->willReturn($this->store);
        $this->store->method('getId')
            ->willReturn(1);

        $this->assertNull($this->router->match($this->request));
    }

    /**
     * @return void
     */
    public function testRewriteAfterStoreSwitcher()
    {
        $initialRequestPath = 'request-path';
        $newRequestPath = 'new-request-path';
        $newTargetPath = 'new-target-path';
        $oldStoreAlias = 'old-store';
        $oldStoreId = 'old-store-id';
        $currentStoreId = 'current-store-id';
        $rewriteEntityType = 'entity-type';
        $rewriteEntityId = 42;
        $this->request->method('getParam')
            ->with('___from_store')
            ->willReturn($oldStoreAlias);
        $this->request->method('getPathInfo')
            ->willReturn($initialRequestPath);
        $this->request->method('getRequestString')
            ->willReturn($initialRequestPath);
        $oldStore = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldStore->method('getId')
            ->willReturn($oldStoreId);
        $this->store->method('getId')
            ->willReturn($currentStoreId);
        $this->storeManager->method('getStore')
            ->willReturnMap([[$oldStoreAlias, $oldStore], [null, $this->store]]);
        $oldUrlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldUrlRewrite->method('getEntityType')
            ->willReturn($rewriteEntityType);
        $oldUrlRewrite->method('getEntityId')
            ->willReturn($rewriteEntityId);
        $oldUrlRewrite->method('getRedirectType')
            ->willReturn(0);
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->method('getRequestPath')
            ->willReturn($newRequestPath);
        $urlRewrite->method('getTargetPath')
            ->willReturn($newTargetPath);
        $this->urlFinder->method('findOneByData')
            ->willReturnMap(
                [
                    [
                        [
                            UrlRewrite::REQUEST_PATH => $initialRequestPath,
                            UrlRewrite::STORE_ID     => $currentStoreId,
                        ],
                        $urlRewrite,
                    ]
                ]
            );
        $this->actionFactory
            ->expects($this->once())
            ->method('create')
            ->with(Forward::class);
        $this->router->match($this->request);
    }

    /**
     * @return void
     */
    public function testNoRewriteAfterStoreSwitcherWhenNoOldRewrite()
    {
        $this->request->method('getPathInfo')->willReturn('request-path');
        $this->request->method('getRequestString')->willReturn('request-path');
        $this->request->method('getParam')->with('___from_store')
            ->willReturn('old-store');
        $oldStore = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->method('getStore')
            ->willReturnMap([['old-store', $oldStore], [null, $this->store]]);
        $oldStore->method('getId')->willReturn('old-store-id');
        $this->store->method('getId')->willReturn('current-store-id');
        $oldUrlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldUrlRewrite->method('getEntityType')->willReturn('entity-type');
        $oldUrlRewrite->method('getEntityId')->willReturn('entity-id');
        $oldUrlRewrite->method('getRequestPath')->willReturn('request-path');
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->method('getRequestPath')->willReturn('request-path');

        $this->assertNull($this->router->match($this->request));
    }

    /**
     * @return void
     */
    public function testNoRewriteAfterStoreSwitcherWhenOldRewriteEqualsToNewOne()
    {
        $this->request->method('getPathInfo')->willReturn('request-path');
        $this->request->method('getRequestString')->willReturn('request-path');
        $this->request->method('getParam')->with('___from_store')
            ->willReturn('old-store');
        $oldStore = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->method('getStore')
            ->willReturnMap([['old-store', $oldStore], [null, $this->store]]);
        $oldStore->method('getId')->willReturn('old-store-id');
        $this->store->method('getId')->willReturn('current-store-id');
        $oldUrlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldUrlRewrite->method('getEntityType')->willReturn('entity-type');
        $oldUrlRewrite->method('getEntityId')->willReturn('entity-id');
        $oldUrlRewrite->method('getRequestPath')->willReturn('old-request-path');
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->method('getRequestPath')->willReturn('old-request-path');

        $this->urlFinder->method('findOneByData')->willReturnMap(
            [
                [
                    [UrlRewrite::REQUEST_PATH => 'request-path', UrlRewrite::STORE_ID => 'old-store-id'],
                    $oldUrlRewrite,
                ],
                [
                    [
                        UrlRewrite::ENTITY_TYPE => 'entity-type',
                        UrlRewrite::ENTITY_ID => 'entity-id',
                        UrlRewrite::STORE_ID => 'current-store-id',
                        UrlRewrite::IS_AUTOGENERATED => 1,
                    ],
                    $urlRewrite
                ],
            ]
        );

        $this->assertNull($this->router->match($this->request));
    }

    /**
     * @return void
     */
    public function testMatchWithRedirect()
    {
        $queryParams = [];
        $redirectType = 'redirect-code';
        $requestPath = 'request-path';
        $targetPath = 'target-path';
        $newTargetPath = 'new-target-path';
        $this->storeManager->method('getStore')
            ->willReturn($this->store);
        $this->request->method('getPathInfo')
            ->willReturn($requestPath);
        $this->request->method('getRequestString')
            ->willReturn($requestPath);
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->method('getRedirectType')->willReturn($redirectType);
        $urlRewrite->method('getRequestPath')->willReturn($requestPath);
        $urlRewrite->method('getTargetPath')->willReturn($targetPath);
        $this->urlFinder->method('findOneByData')->willReturn($urlRewrite);
        $this->response->expects($this->once())
            ->method('setRedirect')
            ->with($newTargetPath, $redirectType);
        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn($queryParams);
        $this->url->expects($this->once())
            ->method('getUrl')
            ->with(
                '',
                ['_direct' => $targetPath, '_query' => $queryParams]
            )
            ->willReturn($newTargetPath);
        $this->request->expects($this->once())
            ->method('setDispatched')
            ->with(true);
        $this->actionFactory->expects($this->once())
            ->method('create')
            ->with(Redirect::class);

        $this->router->match($this->request);
    }

    /**
     * @param string $requestPath
     * @param string $targetPath
     * @param bool $shouldRedirect
     * @dataProvider customInternalRedirectDataProvider
     */
    public function testMatchWithCustomInternalRedirect($requestPath, $targetPath, $shouldRedirect)
    {
        $queryParams = [];
        $redirectType = 'redirect-code';
        $this->storeManager->method('getStore')
            ->willReturn($this->store);
        $this->request->method('getPathInfo')
            ->willReturn($requestPath);
        $this->request->method('getRequestString')
            ->willReturn($requestPath);
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->method('getEntityType')->willReturn('custom');
        $urlRewrite->method('getRedirectType')->willReturn($redirectType);
        $urlRewrite->method('getRequestPath')->willReturn($requestPath);
        $urlRewrite->method('getTargetPath')->willReturn($targetPath);
        $this->urlFinder->method('findOneByData')->willReturn($urlRewrite);

        if ($shouldRedirect) {
            $this->request->method('getParams')->willReturn($queryParams);
            $this->response->expects($this->once())
                ->method('setRedirect')
                ->with('a', $redirectType);
            $this->url->expects($this->once())
                ->method('getUrl')
                ->with(
                    '',
                    ['_direct' => $targetPath, '_query' => $queryParams]
                )
                ->willReturn('a');
            $this->request->expects($this->once())
                ->method('setDispatched')
                ->with(true);
            $this->actionFactory->expects($this->once())
                ->method('create')
                ->with(Redirect::class);
        }

        $routerResult = $this->router->match($this->request);

        if (!$shouldRedirect) {
            $this->assertNull($routerResult);
        }
    }

    /**
     * @return array
     */
    public static function customInternalRedirectDataProvider()
    {
        return [
            ['request-path', 'target-path', true],
            ['/', '/', false],
        ];
    }

    /**
     * @param string $targetPath
     * @dataProvider externalRedirectTargetPathDataProvider
     */
    public function testMatchWithCustomExternalRedirect($targetPath)
    {
        $requestPath = 'request-path';
        $this->storeManager->method('getStore')->willReturn($this->store);
        $this->request->method('getPathInfo')
            ->willReturn($requestPath);
        $this->request->method('getRequestString')
            ->willReturn($requestPath);
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->method('getEntityType')->willReturn('custom');
        $urlRewrite->method('getRedirectType')->willReturn('redirect-code');
        $urlRewrite->method('getRequestPath')->willReturn($requestPath);
        $urlRewrite->method('getTargetPath')->willReturn($targetPath);
        $this->urlFinder->method('findOneByData')->willReturn($urlRewrite);
        $this->response->expects($this->once())
            ->method('setRedirect')
            ->with($targetPath, 'redirect-code');
        $this->request->expects($this->never())->method('getParams');
        $this->url->expects($this->never())->method('getUrl');
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->actionFactory->expects($this->once())->method('create')
            ->with(Redirect::class);

        $this->router->match($this->request);
    }

    /**
     * @return array
     */
    public static function externalRedirectTargetPathDataProvider()
    {
        return [
            ['http://example.com'],
            ['https://example.com'],
        ];
    }

    /**
     * @return void
     */
    public function testMatch()
    {
        $requestPath = 'request-path';
        $this->storeManager->method('getStore')->willReturn($this->store);
        $this->request->method('getPathInfo')
            ->willReturn($requestPath);
        $this->request->method('getRequestString')
            ->willReturn($requestPath);
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->method('getRedirectType')->willReturn(0);
        $urlRewrite->method('getRequestPath')->willReturn($requestPath);
        $urlRewrite->method('getTargetPath')->willReturn('target-path');
        $this->urlFinder->method('findOneByData')->willReturn($urlRewrite);
        $this->request->expects($this->once())->method('setPathInfo')->with('/target-path');
        $this->request->expects($this->once())->method('setAlias')
            ->with(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, 'request-path');
        $this->actionFactory->expects($this->once())->method('create')
            ->with(Forward::class);

        $this->router->match($this->request);
    }
}
