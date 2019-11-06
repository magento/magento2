<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use Zend\Stdlib\ParametersInterface;

/**
 * Test class for UrlRewrite Controller Router
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\UrlRewrite\Controller\Router
     */
    private $router;

    /**
     * @var \Magento\Framework\App\ActionFactory|MockObject
     */
    private $actionFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var \Magento\Framework\App\ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ParametersInterface|MockObject
     */
    private $requestQuery;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface|MockObject
     */
    private $urlFinder;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->actionFactory = $this->createMock(\Magento\Framework\App\ActionFactory::class);
        $this->url = $this->createMock(UrlInterface::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );
        $this->requestQuery = $this->createMock(ParametersInterface::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->request->method('getQuery')->willReturn($this->requestQuery);
        $this->urlFinder = $this->createMock(\Magento\UrlRewrite\Model\UrlFinderInterface::class);
        $this->store = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()->getMock();

        $this->router = $objectManager->getObject(
            \Magento\UrlRewrite\Controller\Router::class,
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
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue(null));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('current-store-id'));

        $this->assertNull($this->router->match($this->request));
    }

    /**
     * @return void
     */
    public function testRewriteAfterStoreSwitcher()
    {
        $initialRequestPath = 'request-path';
        $newRequestPath = 'new-request-path';
        $oldStoreAlias = 'old-store';
        $oldStoreId = 'old-store-id';
        $currentStoreId = 'current-store-id';
        $rewriteEntityType = 'entity-type';
        $rewriteEntityId = 42;
        $this->request
            ->expects($this->any())
            ->method('getParam')
            ->with('___from_store')
            ->willReturn($oldStoreAlias);
        $this->request
            ->expects($this->any())
            ->method('getPathInfo')
            ->willReturn($initialRequestPath);
        $oldStore = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldStore->expects($this->any())
            ->method('getId')
            ->willReturn($oldStoreId);
        $this->store
            ->expects($this->any())
            ->method('getId')
            ->willReturn($currentStoreId);
        $this->storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturnMap([[$oldStoreAlias, $oldStore], [null, $this->store]]);
        $oldUrlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldUrlRewrite->expects($this->any())
            ->method('getEntityType')
            ->willReturn($rewriteEntityType);
        $oldUrlRewrite->expects($this->any())
            ->method('getEntityId')
            ->willReturn($rewriteEntityId);
        $oldUrlRewrite->expects($this->any())
            ->method('getRedirectType')
            ->willReturn(0);
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->expects($this->any())
            ->method('getRequestPath')
            ->willReturn($newRequestPath);
        $this->urlFinder
            ->expects($this->any())
            ->method('findOneByData')
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
        $this->request->expects($this->any())->method('getPathInfo')->will($this->returnValue('request-path'));
        $this->request->expects($this->any())->method('getParam')->with('___from_store')
            ->will($this->returnValue('old-store'));
        $oldStore = $this->getMockBuilder(Store::class)->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getStore')
            ->will($this->returnValueMap([['old-store', $oldStore], [null, $this->store]]));
        $oldStore->expects($this->any())->method('getId')->will($this->returnValue('old-store-id'));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('current-store-id'));
        $oldUrlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $oldUrlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('entity-type'));
        $oldUrlRewrite->expects($this->any())->method('getEntityId')->will($this->returnValue('entity-id'));
        $oldUrlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('request-path'));
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('request-path'));

        $this->assertNull($this->router->match($this->request));
    }

    /**
     * @return void
     */
    public function testNoRewriteAfterStoreSwitcherWhenOldRewriteEqualsToNewOne()
    {
        $this->request->expects($this->any())->method('getPathInfo')->will($this->returnValue('request-path'));
        $this->request->expects($this->any())->method('getParam')->with('___from_store')
            ->will($this->returnValue('old-store'));
        $oldStore = $this->getMockBuilder(Store::class)->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getStore')
            ->will($this->returnValueMap([['old-store', $oldStore], [null, $this->store]]));
        $oldStore->expects($this->any())->method('getId')->will($this->returnValue('old-store-id'));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('current-store-id'));
        $oldUrlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $oldUrlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('entity-type'));
        $oldUrlRewrite->expects($this->any())->method('getEntityId')->will($this->returnValue('entity-id'));
        $oldUrlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('old-request-path'));
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('old-request-path'));

        $this->urlFinder->expects($this->any())->method('findOneByData')->will(
            $this->returnValueMap(
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
            )
        );

        $this->assertNull($this->router->match($this->request));
    }

    /**
     * @return void
     */
    public function testMatchWithRedirect()
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRedirectType')->will($this->returnValue('redirect-code'));
        $urlRewrite->expects($this->any())->method('getTargetPath')->will($this->returnValue('target-path'));
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue($urlRewrite));
        $this->response->expects($this->once())->method('setRedirect')
            ->with('new-target-path', 'redirect-code');
        $this->url->expects($this->once())->method('getUrl')->with('', ['_direct' => 'target-path'])
            ->will($this->returnValue('new-target-path'));
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->actionFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\App\Action\Redirect::class);

        $this->router->match($this->request);
    }

    /**
     * @return void
     */
    public function testMatchWithCustomInternalRedirect()
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('custom'));
        $urlRewrite->expects($this->any())->method('getRedirectType')->will($this->returnValue('redirect-code'));
        $urlRewrite->expects($this->any())->method('getTargetPath')->will($this->returnValue('target-path'));
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue($urlRewrite));
        $this->response->expects($this->once())->method('setRedirect')->with('a', 'redirect-code');
        $this->url->expects($this->once())->method('getUrl')->with('', ['_direct' => 'target-path'])->willReturn('a');
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->actionFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\App\Action\Redirect::class);

        $this->router->match($this->request);
    }

    /**
     * @param string $targetPath
     * @dataProvider externalRedirectTargetPathDataProvider
     */
    public function testMatchWithCustomExternalRedirect($targetPath)
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('custom'));
        $urlRewrite->expects($this->any())->method('getRedirectType')->will($this->returnValue('redirect-code'));
        $urlRewrite->expects($this->any())->method('getTargetPath')->will($this->returnValue($targetPath));
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue($urlRewrite));
        $this->response->expects($this->once())->method('setRedirect')->with($targetPath, 'redirect-code');
        $this->url->expects($this->never())->method('getUrl');
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->actionFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\App\Action\Redirect::class);

        $this->router->match($this->request);
    }

    /**
     * @return array
     */
    public function externalRedirectTargetPathDataProvider()
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
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRedirectType')->will($this->returnValue(0));
        $urlRewrite->expects($this->any())->method('getTargetPath')->will($this->returnValue('target-path'));
        $urlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('request-path'));
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue($urlRewrite));
        $this->request->expects($this->once())->method('setPathInfo')->with('/target-path');
        $this->request->expects($this->once())->method('setAlias')
            ->with(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, 'request-path');
        $this->actionFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\App\Action\Forward::class);

        $this->router->match($this->request);
    }
}
