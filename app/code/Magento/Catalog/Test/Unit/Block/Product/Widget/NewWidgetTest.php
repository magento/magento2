<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\Widget;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Block\Product\Widget\NewWidget as NewWidget;

class NewWidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Widget\NewWidget|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /** @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var ObjectManagerHelper */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->context = $this->getMockBuilder('Magento\Catalog\Block\Product\Context')
            ->setMethods(
                [
                    'getEventManager', 'getScopeConfig', 'getLayout',
                    'getRequest', 'getCacheState', 'getCatalogConfig',
                    'getLocaleDate'
                ]
            )
            ->disableOriginalConstructor()
            ->disableArgumentCloning()
            ->getMock();
        $this->layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->block = $this->objectManager->getObject(
            'Magento\Catalog\Block\Product\Widget\NewWidget',
            [
                'context' => $this->context
            ]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetProductPriceHtml()
    {
        $id = 6;
        $expectedHtml = '
        <div class="price-box price-final_price">
            <span class="regular-price" id="product-price-' . $id . '">
                <span class="price">$0.00</span>
            </span>
        </div>';
        $type = 'widget-new-list';
        $productMock = $this->getMock('Magento\Catalog\Model\Product', ['getId'], [], '', false, false);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $arguments = [
            'price_id' => 'old-price-' . $id . '-' . $type,
            'display_minimal_price' => true,
            'include_container' => true,
            'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        ];

        $priceBoxMock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false, false);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($this->equalTo('product.price.render.default'))
            ->willReturn($priceBoxMock);

        $priceBoxMock->expects($this->once())
            ->method('render')
            ->with($this->equalTo('final_price'), $this->equalTo($productMock), $this->equalTo($arguments))
            ->willReturn($expectedHtml);

        $result = $this->block->getProductPriceHtml($productMock, $type);
        $this->assertEquals($expectedHtml, $result);
    }

    /**
     * @param int $pageNumber
     * @param int $expectedResult
     * @dataProvider getCurrentPageDataProvider
     */
    public function testGetCurrentPage($pageNumber, $expectedResult)
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with(\Magento\Catalog\Block\Product\Widget\NewWidget::PAGE_VAR_NAME)
            ->willReturn($pageNumber);

        $this->assertEquals($expectedResult, $this->block->getCurrentPage());
    }

    public function getCurrentPageDataProvider()
    {
        return [
            [1, 1],
            [5, 5],
            [10, 10]
        ];
    }

    public function testGetProductsCount()
    {
        $this->assertEquals(10, $this->block->getProductsCount());
        $this->block->setProductsCount(2);
        $this->assertEquals(2, $this->block->getProductsCount());
    }

    /**
     * Test protected `__getProductCollection` and `getPageSize` methods via public `toHtml` method.
     *
     * @param $displayType
     * @param $pagerEnable
     * @param $productsCount
     * @param $productsPerPage
     * @param $expectedPageSize
     * @dataProvider getProductCollectionDataProvider
     */
    public function testGetProductCollection(
        $displayType,
        $pagerEnable,
        $productsCount,
        $productsPerPage,
        $expectedPageSize
    ) {
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', ['dispatch'], [], '', false, false);
        $eventManager->expects($this->once())->method('dispatch')->will($this->returnValue(true));

        $scopeConfig = $this->getMock('Magento\Framework\App\Config', ['getValue'], [], '', false, false);
        $scopeConfig->expects($this->once())->method('getValue')->withAnyParameters()->willReturn(false);

        $cacheState = $this->getMock('Magento\Framework\App\Cache\State', ['isEnabled'], [], '', false, false);
        $cacheState->expects($this->atLeastOnce())->method('isEnabled')->withAnyParameters()->willReturn(false);

        $catalogConfig = $this->getMock('Magento\Catalog\Model\Config',['getProductAttributes'], [], '', false, false);
        $catalogConfig->expects($this->once())->method('getProductAttributes')->willReturn([]);

        $localDate = $this->getMock('Magento\Framework\Stdlib\DateTime\Timezone', [], [], '', false, false);
        $localDate->expects($this->any())->method('date')->willReturn(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->context->expects($this->once())->method('getEventManager')->willReturn($eventManager);
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($scopeConfig);
        $this->context->expects($this->once())->method('getCacheState')->willReturn($cacheState);
        $this->context->expects($this->once())->method('getCatalogConfig')->willReturn($catalogConfig);
        $this->context->expects($this->once())->method('getLocaleDate')->willReturn($localDate);

        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->setMethods(
                [
                    'setVisibility', 'addMinimalPrice', 'addFinalPrice',
                    'addTaxPercents', 'addAttributeToSelect', 'addUrlRewrite',
                    'addStoreFilter', 'addAttributeToSort', 'setPageSize',
                    'setCurPage', 'addAttributeToFilter'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->once())->method('setVisibility')
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addMinimalPrice')
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addFinalPrice')
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addTaxPercents')
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSelect')
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addUrlRewrite')
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addStoreFilter')
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSort')
            ->willReturnSelf();

        if (NewWidget::DISPLAY_TYPE_NEW_PRODUCTS === $displayType) {
            $productCollection->expects($this->exactly(2))->method('setPageSize')
                ->withConsecutive(
                    [$productsCount],
                    [$expectedPageSize]
                )
                ->willReturnSelf();
        } else {
            $productCollection->expects($this->atLeastOnce())->method('setPageSize')->with($expectedPageSize)
                ->willReturnSelf();
        }

        $productCollection->expects($this->atLeastOnce())->method('setCurPage')
            ->willReturnSelf();
        $productCollection->expects($this->any())->method('addAttributeToFilter')
            ->willReturnSelf();

        $productCollectionFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false,
            false
        );
        $productCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($productCollection);

        $this->block = $this->objectManager->getObject(
            'Magento\Catalog\Block\Product\Widget\NewWidget',
            [
                'context' => $this->context,
                'productCollectionFactory' => $productCollectionFactory
            ]
        );

        if (null === $productsPerPage) {
            $this->block->unsetData('products_per_page');
        } else {
            $this->block->setData('products_per_page', $productsPerPage);
        }

        $this->block->setData('show_pager', $pagerEnable);
        $this->block->setData('display_type', $displayType);
        $this->block->setProductsCount($productsCount);
        $this->block->toHtml();
    }

    public function getProductCollectionDataProvider()
    {
        return [
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, true, 1, null, 5],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, true, 5, null, 5],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, true, 10, null, 5],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, true, 1, 2, 2],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, true, 5, 3, 3],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, true, 10, 7, 7],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, false, 1, null, 1],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, false, 3, null, 3],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, false, 5, null, 5],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, false, 1, 3, 1],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, false, 3, 5, 3],
            [NewWidget::DISPLAY_TYPE_NEW_PRODUCTS, false, 5, 10, 5],

            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, true, 1, null, 5],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, true, 5, null, 5],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, true, 10, null, 5],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, true, 1, 2, 2],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, true, 5, 3, 3],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, true, 10, 7, 7],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, false, 1, null, 1],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, false, 3, null, 3],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, false, 5, null, 5],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, false, 1, 3, 1],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, false, 3, 5, 3],
            [NewWidget::DISPLAY_TYPE_ALL_PRODUCTS, false, 5, 10, 5]
        ];
    }
}
