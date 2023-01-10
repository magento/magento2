<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Widget\Html\Pager;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\CatalogWidget\Model\Rule;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Condition\Sql\Builder;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Widget\Helper\Conditions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductsListTest extends TestCase
{
    /**
     * @var ProductsList
     */
    protected $productsList;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var Visibility|MockObject
     */
    protected $visibility;

    /**
     * @var Context|MockObject
     */
    protected $httpContext;

    /**
     * @var Builder|MockObject
     */
    protected $builder;

    /**
     * @var Rule|MockObject
     */
    protected $rule;

    /**
     * @var Conditions|MockObject
     */
    protected $widgetConditionsHelper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var Json|MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->collectionFactory =
            $this->getMockBuilder(CollectionFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->visibility = $this->getMockBuilder(Visibility::class)
            ->setMethods(['getVisibleInCatalogIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpContext = $this->createMock(Context::class);
        $this->builder = $this->createMock(Builder::class);
        $this->rule = $this->createMock(Rule::class);
        $this->serializer = $this->createMock(Json::class);
        $this->widgetConditionsHelper = $this->getMockBuilder(Conditions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->design = $this->getMockForAbstractClass(DesignInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            ProductsList::class,
            [
                'productCollectionFactory' => $this->collectionFactory,
                'catalogProductVisibility' => $this->visibility,
                'httpContext' => $this->httpContext,
                'sqlBuilder' => $this->builder,
                'rule' => $this->rule,
                'conditionsHelper' => $this->widgetConditionsHelper,
                'storeManager' => $this->storeManager,
                'design' => $this->design,
                'json' => $this->serializer
            ]
        );
        $this->request = $arguments['context']->getRequest();
        $this->layout = $arguments['context']->getLayout();
        $this->priceCurrency = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->productsList = $objectManagerHelper->getObject(
            ProductsList::class,
            $arguments
        );
        $objectManagerHelper->setBackwardCompatibleProperty($this->productsList, 'priceCurrency', $this->priceCurrency);
    }

    public function testGetCacheKeyInfo()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])->getMock();
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);

        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())->method('getId')->willReturn('blank');
        $this->design->expects($this->once())->method('getDesignTheme')->willReturn($theme);

        $this->httpContext->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [$this->equalTo(\Magento\Customer\Model\Context::CONTEXT_GROUP)],
                [$this->equalTo('tax_rates')]
            )
            ->willReturnOnConsecutiveCalls('context_group', [10]);

        $this->productsList->setData('conditions', 'some_serialized_conditions');

        $this->productsList->setData('page_var_name', 'page_number');
        $this->productsList->setTemplate('test_template');
        $this->productsList->setData('title', 'test_title');
        $this->request->expects($this->once())->method('getParam')->with('page_number')->willReturn(1);

        $this->request->expects($this->once())->method('getParams')->willReturn('request_params');
        $currency = $this->createMock(Currency::class);
        $currency->expects($this->once())->method('getCode')->willReturn('USD');
        $this->priceCurrency->expects($this->once())->method('getCurrency')->willReturn($currency);

        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(function ($value) {
                return json_encode($value);
            });

        $cacheKey = [
            'CATALOG_PRODUCTS_LIST_WIDGET',
            'USD',
            1,
            'blank',
            'context_group',
            '[10]',
            1,
            5,
            10,
            'some_serialized_conditions',
            json_encode('request_params'),
            'test_template',
            'test_title'
        ];
        $this->assertEquals($cacheKey, $this->productsList->getCacheKeyInfo());
    }

    public function testGetProductPriceHtml()
    {
        $product = $this->getMockBuilder(Product::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getId')->willReturn(1);

        $priceRenderer = $this->getMockBuilder(Render::class)
            ->setMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
        $priceRenderer->expects($this->once())
            ->method('render')
            ->with('final_price', $product, [
                'include_container' => false,
                'display_minimal_price' => false,
                'zone' => 'item_list',
                'price_id' => 'old-price-1-some-price-type'
            ])
            ->willReturn('<html>');
        $this->layout->expects($this->once())->method('getBlock')->willReturn($priceRenderer);

        $this->assertEquals('<html>', $this->productsList->getProductPriceHtml(
            $product,
            'some-price-type',
            Render::ZONE_ITEM_LIST,
            [
                'include_container' => false,
                'display_minimal_price' => false
            ]
        ));
    }

    public function testGetPagerHtmlEmpty()
    {
        $this->assertEquals('', $this->productsList->getPagerHtml());
    }

    public function testGetPagerHtml()
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())->method('getSize')->willReturn(3);

        $this->productsList->setData('show_pager', true);
        $this->productsList->setData('products_per_page', 2);
        $this->productsList->setData('product_collection', $collection);

        $pagerBlock = $this->getMockBuilder(Pager::class)
            ->setMethods([
                'toHtml',
                'setUseContainer',
                'setShowAmounts',
                'setShowPerPage',
                'setPageVarName',
                'setLimit',
                'setTotalLimit',
                'setCollection',
            ])->disableOriginalConstructor()
            ->getMock();

        $pagerBlock->expects($this->once())->method('setUseContainer')->willReturnSelf();
        $pagerBlock->expects($this->once())->method('setShowAmounts')->willReturnSelf();
        $pagerBlock->expects($this->once())->method('setShowPerPage')->willReturnSelf();
        $pagerBlock->expects($this->once())->method('setPageVarName')->willReturnSelf();
        $pagerBlock->expects($this->once())->method('setLimit')->willReturnSelf();
        $pagerBlock->expects($this->once())->method('setTotalLimit')->willReturnSelf();
        $pagerBlock->expects($this->once())->method('setCollection')->with($collection)->willReturnSelf();

        $pagerBlock->expects($this->once())->method('toHtml')->willReturn('<pager_html>');
        $this->layout->expects($this->once())->method('createBlock')->willReturn($pagerBlock);
        $this->assertEquals('<pager_html>', $this->productsList->getPagerHtml());
    }

    /**
     * Test public `createCollection` method and protected `getPageSize` method via `createCollection`
     *
     * @param bool $pagerEnable
     * @param int  $productsCount
     * @param int  $productsPerPage
     * @param int  $expectedPageSize
     *
     * @dataProvider createCollectionDataProvider
     */
    public function testCreateCollection($pagerEnable, $productsCount, $productsPerPage, $expectedPageSize)
    {
        $this->visibility->expects($this->once())->method('getVisibleInCatalogIds')
            ->willReturn([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]);
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods([
                'setVisibility',
                'addMinimalPrice',
                'addFinalPrice',
                'addTaxPercents',
                'addAttributeToSelect',
                'addUrlRewrite',
                'addStoreFilter',
                'addAttributeToSort',
                'setPageSize',
                'setCurPage',
                'distinct'
            ])->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())->method('setVisibility')
            ->with([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
            ->willReturnSelf();
        $collection->expects($this->once())->method('addMinimalPrice')->willReturnSelf();
        $collection->expects($this->once())->method('addFinalPrice')->willReturnSelf();
        $collection->expects($this->once())->method('addTaxPercents')->willReturnSelf();
        $collection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $collection->expects($this->once())->method('addUrlRewrite')->willReturnSelf();
        $collection->expects($this->once())->method('addStoreFilter')->willReturnSelf();
        $collection->expects($this->once())->method('addAttributeToSort')->with('entity_id', 'desc')->willReturnSelf();
        $collection->expects($this->once())->method('setPageSize')->with($expectedPageSize)->willReturnSelf();
        $collection->expects($this->once())->method('setCurPage')->willReturnSelf();
        $collection->expects($this->once())->method('distinct')->willReturnSelf();

        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->productsList->setData('conditions_encoded', 'some_serialized_conditions');

        $this->widgetConditionsHelper->expects($this->once())
            ->method('decode')
            ->with('some_serialized_conditions')
            ->willReturn([]);

        $this->builder->expects($this->once())->method('attachConditionToCollection')
            ->with($collection, $this->getConditionsForCollection($collection))
            ->willReturnSelf();

        if ($productsPerPage) {
            $this->productsList->setData('products_per_page', $productsPerPage);
        } else {
            $this->productsList->unsetData('products_per_page');
        }

        $this->productsList->setData('show_pager', $pagerEnable);
        $this->productsList->setData('products_count', $productsCount);

        $this->assertSame($collection, $this->productsList->createCollection());
    }

    /**
     * @return array
     */
    public function createCollectionDataProvider()
    {
        return [
            [true, 1, null, 5],
            [true, 5, null, 5],
            [true, 10, null, 5],
            [true, 1, 2, 2],
            [true, 5, 3, 3],
            [true, 10, 7, 7],
            [false, 1, null, 1],
            [false, 3, null, 3],
            [false, 5, null, 5],
            [false, 1, 3, 1],
            [false, 3, 5, 3],
            [false, 5, 10, 5]
        ];
    }

    public function testGetProductsCount()
    {
        $this->assertEquals(10, $this->productsList->getProductsCount());
        $this->productsList->setProductsCount(2);
        $this->assertEquals(2, $this->productsList->getProductsCount());
    }

    public function testGetProductsPerPage()
    {
        $this->productsList->setData('products_per_page', 2);
        $this->assertEquals(2, $this->productsList->getProductsPerPage());
    }

    public function testGetDefaultProductsPerPage()
    {
        $this->assertEquals(ProductsList::DEFAULT_PRODUCTS_PER_PAGE, $this->productsList->getProductsPerPage());
    }

    public function testShowPager()
    {
        $this->assertFalse($this->productsList->showPager());
        $this->productsList->setData('show_pager', true);
        $this->assertTrue($this->productsList->showPager());
    }

    public function testGetIdentities()
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods([
                'addAttributeToSelect',
                'getIterator',
            ])->disableOriginalConstructor()
            ->getMock();

        $product = $this->createPartialMock(IdentityInterface::class, ['getIdentities']);
        $notProduct = $this->getMockBuilder('NotProduct')
            ->setMethods(['getIdentities'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getIdentities')->willReturn(['product_identity']);
        $collection->expects($this->once())->method('getIterator')->willReturn(
            new \ArrayIterator([$product, $notProduct])
        );
        $this->productsList->setData('product_collection', $collection);

        $this->assertEquals(
            ['product_identity'],
            $this->productsList->getIdentities()
        );
    }

    /**
     * @param $collection
     *
     * @return MockObject
     */
    private function getConditionsForCollection($collection)
    {
        $conditions = $this->getMockBuilder(Combine::class)
            ->setMethods(['collectValidatedAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $conditions->expects($this->once())->method('collectValidatedAttributes')
            ->with($collection)
            ->willReturnSelf();

        $this->rule->expects($this->once())->method('loadPost')->willReturnSelf();
        $this->rule->expects($this->once())->method('getConditions')->willReturn($conditions);
        return $conditions;
    }

    public function testGetTitle()
    {
        $this->assertEmpty($this->productsList->getTitle());
    }

    public function testGetNonDefaultTitle()
    {
        $this->productsList->setTitle('Custom Title');
        $this->assertEquals('Custom Title', $this->productsList->getTitle());
    }

    public function testScope()
    {
        $this->assertFalse($this->productsList->isScopePrivate());
    }
}
