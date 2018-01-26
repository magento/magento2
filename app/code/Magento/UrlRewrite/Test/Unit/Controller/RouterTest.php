<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Unit\Controller;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\UrlRewrite\Controller\Router */
    protected $router;

    /** @var \Magento\Framework\App\ActionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFactory;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $url;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $store;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\UrlRewrite\Model\UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlFinder;

    protected function setUp()
    {
        $this->actionFactory = $this->getMock('Magento\Framework\App\ActionFactory', [], [], '', false);
        $this->url = $this->getMock('Magento\Framework\UrlInterface');
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface', ['setRedirect', 'sendResponse']);
        $this->request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->urlFinder = $this->getMock('Magento\UrlRewrite\Model\UrlFinderInterface');
        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();

        $this->router = (new ObjectManager($this))->getObject(
            'Magento\UrlRewrite\Controller\Router',
            [
                'actionFactory' => $this->actionFactory,
                'url' => $this->url,
                'storeManager' => $this->storeManager,
                'response' => $this->response,
                'urlFinder' => $this->urlFinder
            ]
        );
    }

    public function testNoRewriteExist()
    {
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue(null));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('current-store-id'));

        $this->assertNull($this->router->match($this->request));
    }

    public function testRewriteAfterStoreSwitcher()
    {
        $this->request->expects($this->any())->method('getPathInfo')->will($this->returnValue('request-path'));
        $this->request->expects($this->any())->method('getParam')->with('___from_store')
            ->will($this->returnValue('old-store'));
        $oldStore = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getStore')
            ->will($this->returnValueMap([['old-store', $oldStore], [null, $this->store]]));
        $oldStore->expects($this->any())->method('getId')->will($this->returnValue('old-store-id'));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('current-store-id'));
        $oldUrlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $oldUrlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('entity-type'));
        $oldUrlRewrite->expects($this->any())->method('getEntityId')->will($this->returnValue('entity-id'));
        $oldUrlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('old-request-path'));
        $urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('new-request-path'));

        $this->urlFinder->expects($this->any())->method('findOneByData')->will(
            $this->returnValueMap([
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
            ])
        );
        $this->response->expects($this->once())->method('setRedirect')
            ->with('new-request-path', OptionProvider::TEMPORARY);
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->actionFactory->expects($this->once())->method('create')
            ->with('Magento\Framework\App\Action\Redirect');

        $this->router->match($this->request);
    }

    public function testNoRewriteAfterStoreSwitcherWhenNoOldRewrite()
    {
        $this->request->expects($this->any())->method('getPathInfo')->will($this->returnValue('request-path'));
        $this->request->expects($this->any())->method('getParam')->with('___from_store')
            ->will($this->returnValue('old-store'));
        $oldStore = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getStore')
            ->will($this->returnValueMap([['old-store', $oldStore], [null, $this->store]]));
        $oldStore->expects($this->any())->method('getId')->will($this->returnValue('old-store-id'));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('current-store-id'));
        $oldUrlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $oldUrlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('entity-type'));
        $oldUrlRewrite->expects($this->any())->method('getEntityId')->will($this->returnValue('entity-id'));
        $oldUrlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('request-path'));
        $urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('request-path'));

        $this->assertNull($this->router->match($this->request));
    }

    public function testNoRewriteAfterStoreSwitcherWhenOldRewriteEqualsToNewOne()
    {
        $this->request->expects($this->any())->method('getPathInfo')->will($this->returnValue('request-path'));
        $this->request->expects($this->any())->method('getParam')->with('___from_store')
            ->will($this->returnValue('old-store'));
        $oldStore = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getStore')
            ->will($this->returnValueMap([['old-store', $oldStore], [null, $this->store]]));
        $oldStore->expects($this->any())->method('getId')->will($this->returnValue('old-store-id'));
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('current-store-id'));
        $oldUrlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $oldUrlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('entity-type'));
        $oldUrlRewrite->expects($this->any())->method('getEntityId')->will($this->returnValue('entity-id'));
        $oldUrlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('old-request-path'));
        $urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('old-request-path'));

        $this->urlFinder->expects($this->any())->method('findOneByData')->will(
            $this->returnValueMap([
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
            ])
        );

        $this->assertNull($this->router->match($this->request));
    }

    public function testMatchWithRedirect()
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
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
            ->with('Magento\Framework\App\Action\Redirect');

        $this->router->match($this->request);
    }

    public function testMatchWithCustomInternalRedirect()
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('custom'));
        $urlRewrite->expects($this->any())->method('getRedirectType')->will($this->returnValue('redirect-code'));
        $urlRewrite->expects($this->any())->method('getTargetPath')->will($this->returnValue('target-path'));
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue($urlRewrite));
        $this->response->expects($this->once())->method('setRedirect')->with('a', 'redirect-code');
        $this->url->expects($this->once())->method('getUrl')->with('', ['_direct' => 'target-path'])->willReturn('a');
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->actionFactory->expects($this->once())->method('create')
            ->with('Magento\Framework\App\Action\Redirect');

        $this->router->match($this->request);
    }

    /**
     * @param string $targetPath
     * @dataProvider externalRedirectTargetPathDataProvider
     */
    public function testMatchWithCustomExternalRedirect($targetPath)
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getEntityType')->will($this->returnValue('custom'));
        $urlRewrite->expects($this->any())->method('getRedirectType')->will($this->returnValue('redirect-code'));
        $urlRewrite->expects($this->any())->method('getTargetPath')->will($this->returnValue($targetPath));
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue($urlRewrite));
        $this->response->expects($this->once())->method('setRedirect')->with($targetPath, 'redirect-code');
        $this->url->expects($this->never())->method('getUrl');
        $this->request->expects($this->once())->method('setDispatched')->with(true);
        $this->actionFactory->expects($this->once())->method('create')
            ->with('Magento\Framework\App\Action\Redirect');

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

    public function testMatch()
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $urlRewrite->expects($this->any())->method('getRedirectType')->will($this->returnValue(0));
        $urlRewrite->expects($this->any())->method('getTargetPath')->will($this->returnValue('target-path'));
        $urlRewrite->expects($this->any())->method('getRequestPath')->will($this->returnValue('request-path'));
        $this->urlFinder->expects($this->any())->method('findOneByData')->will($this->returnValue($urlRewrite));
        $this->request->expects($this->once())->method('setPathInfo')->with('/target-path');
        $this->request->expects($this->once())->method('setAlias')
            ->with(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, 'request-path');
        $this->actionFactory->expects($this->once())->method('create')
            ->with('Magento\Framework\App\Action\Forward');

        $this->router->match($this->request);
    }
}
