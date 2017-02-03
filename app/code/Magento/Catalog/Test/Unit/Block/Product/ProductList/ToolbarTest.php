<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

class ToolbarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product\ProductList\Toolbar | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Url | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Catalog\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Catalog\Helper\Product\ProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productListHelper;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Theme\Block\Html\Pager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pagerBlock;

    protected function setUp()
    {
        $this->model = $this->getMock(
            'Magento\Catalog\Model\Product\ProductList\Toolbar',
            [
                'getDirection',
                'getOrder',
                'getMode',
                'getLimit',
                'getCurrentPage'
            ],
            [],
            '',
            false
        );
        $this->layout = $this->getMock('Magento\Framework\View\Layout', ['getChildName', 'getBlock'], [], '', false);
        $this->pagerBlock = $this->getMock(
            'Magento\Theme\Block\Html\Pager',
            [
                'setUseContainer',
                'setShowPerPage',
                'setShowAmounts',
                'setFrameLength',
                'setJump',
                'setLimit',
                'setCollection',
                'toHtml'
            ],
            [],
            '',
            false
        );
        $this->urlBuilder = $this->getMock('Magento\Framework\Url', ['getUrl'], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $scopeConfig = [
            [\Magento\Catalog\Model\Config::XML_PATH_LIST_DEFAULT_SORT_BY, null, 'name'],
            [\Magento\Catalog\Helper\Product\ProductList::XML_PATH_LIST_MODE, null, 'grid-list'],
            ['catalog/frontend/list_per_page_values', null, '10,20,30'],
            ['catalog/frontend/grid_per_page_values', null, '10,20,30'],
            ['catalog/frontend/list_allow_all', null, false]
        ];

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($scopeConfig));

        $this->catalogConfig = $this->getMock(
            'Magento\Catalog\Model\Config',
            ['getAttributeUsedForSortByArray'],
            [],
            '',
            false
        );

        $context = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            ['getUrlBuilder', 'getScopeConfig', 'getLayout'],
            [],
            '',
            false
        );
        $context->expects($this->any())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilder));
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfig));
        $context->expects($this->any())
            ->method('getlayout')
            ->will($this->returnValue($this->layout));
        $this->productListHelper = $this->getMock(
            'Magento\Catalog\Helper\Product\ProductList',
            [],
            [],
            '',
            false
        );

        $this->urlEncoder = $this->getMock('Magento\Framework\Url\EncoderInterface', ['encode'], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            'Magento\Catalog\Block\Product\ProductList\Toolbar',
            [
                'context' => $context,
                'catalogConfig' => $this->catalogConfig,
                'toolbarModel' => $this->model,
                'urlEncoder' => $this->urlEncoder,
                'productListHelper' => $this->productListHelper
            ]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetCurrentPage()
    {
        $page = 3;

        $this->model->expects($this->once())
            ->method('getCurrentPage')
            ->will($this->returnValue($page));
        $this->assertEquals($page, $this->block->getCurrentPage());
    }

    public function testGetPagerEncodedUrl()
    {
        $url = 'url';
        $encodedUrl = '123';

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($url));
        $this->urlEncoder->expects($this->once())
            ->method('encode')
            ->with($url)
            ->will($this->returnValue($encodedUrl));
        $this->assertEquals($encodedUrl, $this->block->getPagerEncodedUrl());
    }

    public function testGetCurrentOrder()
    {
        $order = 'price';
        $this->model->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $this->catalogConfig->expects($this->once())
            ->method('getAttributeUsedForSortByArray')
            ->will($this->returnValue(['name' => [], 'price' => []]));

        $this->assertEquals($order, $this->block->getCurrentOrder());
    }

    public function testGetCurrentDirection()
    {
        $direction = 'desc';

        $this->model->expects($this->once())
            ->method('getDirection')
            ->will($this->returnValue($direction));

        $this->assertEquals($direction, $this->block->getCurrentDirection());
    }

    public function testGetCurrentMode()
    {
        $mode = 'list';

        $this->productListHelper->expects($this->once())
            ->method('getAvailableViewMode')
            ->will($this->returnValue(['list' => 'List']));
        $this->model->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($mode));

        $this->assertEquals($mode, $this->block->getCurrentMode());
    }

    public function testGetModes()
    {
        $mode = ['list' => 'List'];
        $this->productListHelper->expects($this->once())
            ->method('getAvailableViewMode')
            ->will($this->returnValue($mode));

        $this->assertEquals($mode, $this->block->getModes());
        $this->assertEquals($mode, $this->block->getModes());
    }

    /**
     * @param string[] $mode
     * @param string[] $expected
     * @dataProvider setModesDataProvider
     */
    public function testSetModes($mode, $expected)
    {
        $this->productListHelper->expects($this->once())
            ->method('getAvailableViewMode')
            ->will($this->returnValue($mode));

        $block = $this->block->setModes(['mode' => 'mode']);
        $this->assertEquals($expected, $block->getModes());
    }

    public function setModesDataProvider()
    {
        return [
            [['list' => 'List'], ['list' => 'List']],
            [null, ['mode' => 'mode']],
        ];
    }

    public function testGetLimit()
    {
        $mode = 'list';
        $limit = 10;

        $this->model->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($mode));

        $this->model->expects($this->once())
            ->method('getLimit')
            ->will($this->returnValue($limit));
        $this->productListHelper->expects($this->once())
            ->method('getAvailableLimit')
            ->will($this->returnValue([10 => 10, 20 => 20]));
        $this->productListHelper->expects($this->once())
            ->method('getDefaultLimitPerPageValue')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(10));
        $this->productListHelper->expects($this->any())
            ->method('getAvailableViewMode')
            ->will($this->returnValue(['list' => 'List']));

        $this->assertEquals($limit, $this->block->getLimit());
    }

    public function testGetPagerHtml()
    {
        $limit = 10;

        $this->layout->expects($this->once())
            ->method('getChildName')
            ->will($this->returnValue('product_list_toolbar_pager'));
        $this->layout->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($this->pagerBlock));
        $this->productListHelper->expects($this->exactly(2))
            ->method('getAvailableLimit')
            ->will($this->returnValue([10 => 10, 20 => 20]));
        $this->model->expects($this->once())
            ->method('getLimit')
            ->will($this->returnValue($limit));
        $this->pagerBlock->expects($this->once())
            ->method('setUseContainer')
            ->will($this->returnValue($this->pagerBlock));
        $this->pagerBlock->expects($this->once())
            ->method('setShowPerPage')
            ->will($this->returnValue($this->pagerBlock));
        $this->pagerBlock->expects($this->once())
            ->method('setShowAmounts')
            ->will($this->returnValue($this->pagerBlock));
        $this->pagerBlock->expects($this->once())
            ->method('setFrameLength')
            ->will($this->returnValue($this->pagerBlock));
        $this->pagerBlock->expects($this->once())
            ->method('setJump')
            ->will($this->returnValue($this->pagerBlock));
        $this->pagerBlock->expects($this->once())
            ->method('setLimit')
            ->with($limit)
            ->will($this->returnValue($this->pagerBlock));
        $this->pagerBlock->expects($this->once())
            ->method('setCollection')
            ->will($this->returnValue($this->pagerBlock));
        $this->pagerBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue(true));

        $this->assertTrue($this->block->getPagerHtml());
    }

    public function testSetDefaultOrder()
    {
        $this->catalogConfig->expects($this->atLeastOnce())
            ->method('getAttributeUsedForSortByArray')
            ->will($this->returnValue(['name' => [], 'price' => []]));

        $this->block->setDefaultOrder('field');
    }

    public function testGetAvailableOrders()
    {
        $data = ['name' => [], 'price' => []];
        $this->catalogConfig->expects($this->once())
            ->method('getAttributeUsedForSortByArray')
            ->will($this->returnValue($data));

        $this->assertEquals($data, $this->block->getAvailableOrders());
        $this->assertEquals($data, $this->block->getAvailableOrders());
    }

    public function testAddOrderToAvailableOrders()
    {
        $data = ['name' => [], 'price' => []];
        $this->catalogConfig->expects($this->once())
            ->method('getAttributeUsedForSortByArray')
            ->will($this->returnValue($data));
        $expected = $data;
        $expected['order'] = 'value';
        $toolbar = $this->block->addOrderToAvailableOrders('order', 'value');
        $this->assertEquals($expected, $toolbar->getAvailableOrders());
    }

    public function testRemoveOrderFromAvailableOrders()
    {
        $data = ['name' => [], 'price' => []];
        $this->catalogConfig->expects($this->once())
            ->method('getAttributeUsedForSortByArray')
            ->will($this->returnValue($data));
        $toolbar = $this->block->removeOrderFromAvailableOrders('order', 'value');
        $this->assertEquals($data, $toolbar->getAvailableOrders());
        $toolbar2 = $this->block->removeOrderFromAvailableOrders('name');
        $this->assertEquals(['price' => []], $toolbar2->getAvailableOrders());
    }
}
