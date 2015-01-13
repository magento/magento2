<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Block\Product;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class ProductsListTest
 */
class ProductsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogWidget\Block\Product\ProductsList
     */
    protected $productsList;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visibility;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $builder;

    /**
     * @var \Magento\CatalogWidget\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rule;

    /**
     * @var \Magento\Widget\Helper\Conditions|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $widgetConditionsHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    protected function setUp()
    {
        $this->collectionFactory = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\CollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->visibility = $this->getMockBuilder('Magento\Catalog\Model\Product\Visibility')
            ->setMethods(['getVisibleInCatalogIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpContext = $this->getMock('Magento\Framework\App\Http\Context');
        $this->builder = $this->getMock('Magento\Rule\Model\Condition\Sql\Builder', [], [], '', false);
        $this->rule = $this->getMock('Magento\CatalogWidget\Model\Rule', [], [], '', false);
        $this->widgetConditionsHelper = $this->getMock('Magento\Widget\Helper\Conditions');
        $this->storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->design = $this->getMock('\Magento\Framework\View\DesignInterface');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\CatalogWidget\Block\Product\ProductsList',
            [
                'productCollectionFactory' => $this->collectionFactory,
                'catalogProductVisibility' => $this->visibility,
                'httpContext' => $this->httpContext,
                'sqlBuilder' => $this->builder,
                'rule' => $this->rule,
                'conditionsHelper' => $this->widgetConditionsHelper,
                'storeManager' => $this->storeManager,
                'design' => $this->design
            ]
        );
        $this->request = $arguments['context']->getRequest();
        $this->layout = $arguments['context']->getLayout();

        $this->productsList = $objectManagerHelper->getObject(
            'Magento\CatalogWidget\Block\Product\ProductsList',
            $arguments
        );
    }

    public function testGetCacheKeyInfo()
    {
        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()->setMethods(['getId'])->getMock();
        $store->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $theme = $this->getMock('\Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getId')->will($this->returnValue('blank'));
        $this->design->expects($this->once())->method('getDesignTheme')->will($this->returnValue($theme));

        $this->httpContext->expects($this->once())->method('getValue')->will($this->returnValue('context_group'));
        $this->productsList->setData('conditions', 'some_serialized_conditions');

        $this->request->expects($this->once())->method('getParam')->with('np')->will($this->returnValue(1));

        $cacheKey = ['CATALOG_PRODUCTS_LIST_WIDGET', 1, 'blank', 'context_group', 1, 5, 'some_serialized_conditions'];
        $this->assertEquals($cacheKey, $this->productsList->getCacheKeyInfo());
    }

    public function testGetProductPriceHtml()
    {
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getId')->will($this->returnValue(1));

        $priceRenderer = $this->getMockBuilder('\Magento\Framework\Pricing\Render')
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
            ->will($this->returnValue('<html>'));
        $this->layout->expects($this->once())->method('getBlock')->will($this->returnValue($priceRenderer));

        $this->assertEquals('<html>', $this->productsList->getProductPriceHtml(
            $product,
            'some-price-type',
            \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
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
        $collection = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\Collection')
            ->setMethods(['getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())->method('getSize')->will($this->returnValue(3));

        $this->productsList->setData('show_pager', true);
        $this->productsList->setData('products_per_page', 2);
        $this->productsList->setData('product_collection', $collection);

        $pagerBlock = $this->getMockBuilder('Magento\Catalog\Block\Product\Widget\Html\Pager')
            ->setMethods([
                'toHtml',
                'setUseContainer',
                'setShowAmounts',
                'setShowPerPage',
                'setPageVarName',
                'setLimit',
                'setTotalLimit',
                'setCollection',
            ])->disableOriginalConstructor()->getMock();

        $pagerBlock->expects($this->once())->method('setUseContainer')->will($this->returnSelf());
        $pagerBlock->expects($this->once())->method('setShowAmounts')->will($this->returnSelf());
        $pagerBlock->expects($this->once())->method('setShowPerPage')->will($this->returnSelf());
        $pagerBlock->expects($this->once())->method('setPageVarName')->will($this->returnSelf());
        $pagerBlock->expects($this->once())->method('setLimit')->will($this->returnSelf());
        $pagerBlock->expects($this->once())->method('setTotalLimit')->will($this->returnSelf());
        $pagerBlock->expects($this->once())->method('setCollection')->with($collection)->will($this->returnSelf());

        $pagerBlock->expects($this->once())->method('toHtml')->will($this->returnValue('<pager_html>'));
        $this->layout->expects($this->once())->method('createBlock')->will($this->returnValue($pagerBlock));
        $this->assertEquals('<pager_html>', $this->productsList->getPagerHtml());
    }

    public function testCreateCollection()
    {
        $this->visibility->expects($this->once())->method('getVisibleInCatalogIds')
            ->will($this->returnValue([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]));
        $collection = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\Collection')
            ->setMethods([
                'setVisibility',
                'addMinimalPrice',
                'addFinalPrice',
                'addTaxPercents',
                'addAttributeToSelect',
                'addUrlRewrite',
                'addStoreFilter',
                'setPageSize',
                'setCurPage',
            ])->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())->method('setVisibility')
            ->with([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
            ->will($this->returnSelf());
        $collection->expects($this->once())->method('addMinimalPrice')->will($this->returnSelf());
        $collection->expects($this->once())->method('addFinalPrice')->will($this->returnSelf());
        $collection->expects($this->once())->method('addTaxPercents')->will($this->returnSelf());
        $collection->expects($this->once())->method('addAttributeToSelect')->will($this->returnSelf());
        $collection->expects($this->once())->method('addUrlRewrite')->will($this->returnSelf());
        $collection->expects($this->once())->method('addStoreFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('setPageSize')->will($this->returnSelf());
        $collection->expects($this->once())->method('setCurPage')->will($this->returnSelf());

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->productsList->setData('conditions_encoded', 'some_serialized_conditions');

        $conditions = $this->getMockBuilder('\Magento\Rule\Model\Condition\Combine')
            ->setMethods(['collectValidatedAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $conditions->expects($this->once())->method('collectValidatedAttributes')
            ->with($collection)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())->method('attachConditionToCollection')
            ->with($collection, $conditions)
            ->will($this->returnSelf());

        $this->rule->expects($this->once())->method('loadPost')->will($this->returnSelf());
        $this->rule->expects($this->once())->method('getConditions')->will($this->returnValue($conditions));

        $this->assertSame($collection, $this->productsList->createCollection());
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
        $this->assertEquals(false, $this->productsList->showPager());
        $this->productsList->setData('show_pager', true);
        $this->assertEquals(true, $this->productsList->showPager());
    }

    public function testGetIdentities()
    {
        $this->assertEquals([\Magento\Catalog\Model\Product::CACHE_TAG], $this->productsList->getIdentities());
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
}
