<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

use \Magento\Catalog\Model\Product\Url;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Url
     */
    protected $model;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filter;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlFinder;

    /**
     * @var \Magento\Catalog\Helper\Category|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $catalogCategory;

    /**
     * @var \Magento\Framework\Url|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $url;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sidResolver;

    protected function setUp(): void
    {
        $this->filter = $this->getMockBuilder(
            \Magento\Framework\Filter\FilterManager::class
        )->disableOriginalConstructor()->setMethods(
            ['translitUrl']
        )->getMock();

        $this->urlFinder = $this->getMockBuilder(
            \Magento\UrlRewrite\Model\UrlFinderInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->url = $this->getMockBuilder(
            \Magento\Framework\Url::class
        )->disableOriginalConstructor()->setMethods(
            ['setScope', 'getUrl']
        )->getMock();

        $this->sidResolver = $this->createMock(\Magento\Framework\Session\SidResolverInterface::class);

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup']);
        $store->expects($this->any())->method('getId')->willReturn(1);
        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $urlFactory = $this->getMockBuilder(\Magento\Framework\UrlFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlFactory->method('create')
            ->willReturn($this->url);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Url::class,
            [
                'filter' => $this->filter,
                'catalogCategory' => $this->catalogCategory,
                'storeManager' => $storeManager,
                'urlFactory' => $urlFactory,
                'sidResolver' => $this->sidResolver,
            ]
        );
    }

    public function testFormatUrlKey()
    {
        $strIn = 'Some string';
        $resultString = 'some';

        $this->filter->expects(
            $this->once()
        )->method(
            'translitUrl'
        )->with(
            $strIn
        )->willReturn(
            $resultString
        );

        $this->assertEquals($resultString, $this->model->formatUrlKey($strIn));
    }

    /**
     * @dataProvider getUrlDataProvider
     * @covers \Magento\Catalog\Model\Product\Url::getUrl
     * @covers \Magento\Catalog\Model\Product\Url::getUrlInStore
     * @covers \Magento\Catalog\Model\Product\Url::getProductUrl
     *
     * @param $getUrlMethod
     * @param $routePath
     * @param $requestPathProduct
     * @param $storeId
     * @param $categoryId
     * @param $routeParams
     * @param $routeParamsUrl
     * @param $productId
     * @param $productUrlKey
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testGetUrl(
        $getUrlMethod,
        $routePath,
        $requestPathProduct,
        $storeId,
        $categoryId,
        $routeParams,
        $routeParamsUrl,
        $productId,
        $productUrlKey
    ) {
        $product = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()->setMethods(
            ['getStoreId', 'getEntityId', 'getId', 'getUrlKey', 'setRequestPath', 'hasUrlDataObject', 'getRequestPath',
                'getCategoryId', 'getDoNotUseCategoryId', '__wakeup', ]
        )->getMock();
        $product->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $product->expects($this->any())->method('getCategoryId')->willReturn($categoryId);
        $product->expects($this->any())->method('getRequestPath')->willReturn($requestPathProduct);
        $product->expects($this->any())
            ->method('setRequestPath')
            ->with(false)
            ->willReturnSelf();
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $product->expects($this->any())->method('getUrlKey')->willReturn($productUrlKey);
        $this->url->expects($this->any())->method('setScope')->with($storeId)->willReturnSelf();
        $this->url->expects($this->any())
            ->method('getUrl')
            ->with($routePath, $routeParamsUrl)
            ->willReturn($requestPathProduct);
        $this->urlFinder->expects($this->any())->method('findOneByData')->willReturn(false);

        switch ($getUrlMethod) {
            case 'getUrl':
                $this->assertEquals($requestPathProduct, $this->model->getUrl($product, $routeParams));
                break;
            case 'getUrlInStore':
                $this->assertEquals($requestPathProduct, $this->model->getUrlInStore($product, $routeParams));
                break;
            case 'getProductUrl':
                $this->assertEquals($requestPathProduct, $this->model->getProductUrl($product, null));
                $this->sidResolver
                    ->expects($this->never())
                    ->method('getUseSessionInUrl')
                    ->willReturn(true);
                break;
        }
    }

    /**
     * @return array
     */
    public function getUrlDataProvider()
    {
        return [
            [
                'getUrl',
                '',
                '/product/url/path',
                1,
                1,
                ['_scope' => 1],
                ['_scope' => 1, '_direct' => '/product/url/path', '_query' => []],
                null,
                null,
            ], [
                'getUrl',
                'catalog/product/view',
                false,
                1,
                1,
                ['_scope' => 1],
                ['_scope' => 1, '_query' => [], 'id' => 1, 's' => 'urlKey', 'category' => 1],
                1,
                'urlKey',
            ], [
                'getUrlInStore',
                '',
                '/product/url/path',
                1,
                1,
                ['_scope' => 1],
                ['_scope' => 1, '_direct' => '/product/url/path', '_query' => [], '_scope_to_url' => true],
                null,
                null,
            ], [
                'getProductUrl',
                '',
                '/product/url/path',
                1,
                1,
                [],
                ['_direct' => '/product/url/path', '_query' => [], '_nosid' => true],
                null,
                null,
            ]
        ];
    }
}
