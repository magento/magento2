<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Product;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    protected $model;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlRewrite;

    /**
     * @var \Magento\Catalog\Helper\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogCategory;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $url;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidResolver;

    protected function setUp()
    {
        $this->filter = $this->getMockBuilder(
            'Magento\Framework\Filter\FilterManager'
        )->disableOriginalConstructor()->setMethods(
            ['translitUrl']
        )->getMock();

        $rewriteFactory = $this->getMockBuilder(
            'Magento\UrlRewrite\Model\UrlRewriteFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $this->urlRewrite = $this->getMockBuilder(
            'Magento\UrlRewrite\Model\UrlRewrite'
        )->disableOriginalConstructor()->setMethods(
            ['setStoreId', 'getRequestPath', 'loadByIdPath', 'getId', '__wakeup']
        )->getMock();
        $rewriteFactory->expects($this->once())->method('create')->will($this->returnValue($this->urlRewrite));

        $this->catalogCategory = $this->getMockBuilder(
            'Magento\Catalog\Helper\Category'
        )->disableOriginalConstructor()->setMethods(
            ['getCategoryUrlPath']
        )->getMock();

        $this->url = $this->getMockBuilder(
            'Magento\Framework\Url'
        )->disableOriginalConstructor()->setMethods(
            ['setScope', 'getUrl']
        )->getMock();

        $this->sidResolver = $this->getMock('Magento\Framework\Session\SidResolverInterface');

        $store = $this->getMock('Magento\Store\Model\Store', ['getId', '__wakeup'], [], '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $storeManager = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Product\Url',
            [
                'urlRewriteFactory' => $rewriteFactory,
                'filter' => $this->filter,
                'catalogCategory' => $this->catalogCategory,
                'storeManager' => $storeManager,
                'url' => $this->url,
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
        )->will(
            $this->returnValue($resultString)
        );

        $this->assertEquals($resultString, $this->model->formatUrlKey($strIn));
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Invalid category object supplied
     */
    public function testGetUrlPath()
    {
        $urlPathProduct = '/some/url/path';
        $urlPathCategory = '/some/url/path/category';

        $product = $this->getMockBuilder(
            'Magento\Catalog\Model\Product'
        )->disableOriginalConstructor()->setMethods(
            ['getData', '__wakeup']
        )->getMock();
        $product->expects($this->atLeastOnce())
            ->method('getData')
            ->with('url_path')
            ->will($this->returnValue($urlPathProduct));
        $category = $this->getMockBuilder(
            'Magento\Catalog\Model\Category'
        )->disableOriginalConstructor()->setMethods(
            ['getUrlPath', '__wakeup']
        )->getMock();
        $category->expects($this->atLeastOnce())->method('getUrlPath')->will($this->returnValue($urlPathCategory));
        $this->catalogCategory
            ->expects($this->atLeastOnce())
            ->method('getCategoryUrlPath')
            ->with($urlPathCategory)
            ->will($this->returnValue($urlPathCategory));

        $this->assertEquals($urlPathProduct, $this->model->getUrlPath($product));
        $this->assertEquals($urlPathCategory . '/' . $urlPathProduct, $this->model->getUrlPath($product, $category));
        $this->model->getUrlPath($product, 1);
    }

    /**
     * @dataProvider getUrlDataProvider
     * @covers Magento\Catalog\Model\Product\Url::getUrl
     * @covers Magento\Catalog\Model\Product\Url::getUrlInStore
     * @covers Magento\Catalog\Model\Product\Url::getProductUrl
     *
     * @param $getUrlMethod
     * @param $routePath
     * @param $requestPathProduct
     * @param $storeId
     * @param $categoryId
     * @param $routeParams
     * @param $routeParamsUrl
     * @param $entityId
     * @param $idPath
     * @param $requestPathUrlRewrite
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
        $entityId,
        $idPath,
        $requestPathUrlRewrite,
        $productId,
        $productUrlKey
    ) {
        $product = $this->getMockBuilder(
            'Magento\Catalog\Model\Product'
        )->disableOriginalConstructor()->setMethods(
            ['getStoreId', 'getEntityId', 'getId', 'getUrlKey', 'setRequestPath', 'hasUrlDataObject', 'getRequestPath',
                'getCategoryId', 'getDoNotUseCategoryId', '__wakeup']
        )->getMock();
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $product->expects($this->any())->method('getCategoryId')->will($this->returnValue($categoryId));
        $product->expects($this->any())->method('getRequestPath')->will($this->returnValue($requestPathProduct));
        $product->expects($this->any())->method('getEntityId')->will($this->returnValue($entityId));
        $product->expects($this->any())
            ->method('setRequestPath')
            ->with($requestPathUrlRewrite)
            ->will($this->returnSelf());
        $product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $product->expects($this->any())->method('getUrlKey')->will($this->returnValue($productUrlKey));
        $this->url->expects($this->any())->method('setScope')->with($storeId)->will($this->returnSelf());
        $this->url->expects($this->any())
            ->method('getUrl')
            ->with($routePath, $routeParamsUrl)
            ->will($this->returnValue($requestPathProduct));
        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('loadByIdPath')->with($idPath)->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('getId')->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->any())
            ->method('getRequestPath')
            ->will($this->returnValue($requestPathUrlRewrite));

        switch ($getUrlMethod) {
            case 'getUrl':
                $this->assertEquals($requestPathProduct, $this->model->getUrl($product, $routeParams));
                break;
            case 'getUrlInStore':
                $this->assertEquals($requestPathProduct, $this->model->getUrlInStore($product, $routeParams));
                break;
            case 'getProductUrl':
                $this->assertEquals($requestPathProduct, $this->model->getProductUrl($product, true));
                $this->sidResolver
                    ->expects($this->once())
                    ->method('getUseSessionInUrl')
                    ->will($this->returnValue(true));
                $this->assertEquals($requestPathProduct, $this->model->getProductUrl($product, null));
                break;
        }
    }

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
                '',
                null,
                null,
                null,
            ], [
                'getUrl',
                '',
                null,
                1,
                1,
                ['_scope' => 1],
                ['_scope' => 1, '_direct' => '/product_url_rewrite/url/path', '_query' => []],
                1,
                'product/1/1',
                '/product_url_rewrite/url/path',
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
                null,
                '',
                null,
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
                '',
                null,
                null,
                null,
            ], [
                'getProductUrl',
                '',
                '/product/url/path',
                1,
                1,
                [],
                ['_direct' => '/product/url/path', '_query' => []],
                null,
                '',
                null,
                null,
                null,
            ]
        ];
    }
}
