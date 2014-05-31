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

namespace Magento\Catalog\Block\Product\ProductList;

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
     * @var \Magento\Catalog\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

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
            array(
                'getDirection',
                'getOrder',
                'getMode',
                'getLimit',
                'getCurrentPage'
            ),
            array(),
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
            false);
        $this->urlBuilder = $this->getMock('Magento\Framework\Url', ['getUrl'], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $scopeConfig = array(
            array(\Magento\Catalog\Model\Config::XML_PATH_LIST_DEFAULT_SORT_BY, null, 'name'),
            array(\Magento\Catalog\Helper\Product\ProductList::XML_PATH_LIST_MODE, null, 'grid-list'),
            array('catalog/frontend/list_per_page_values', null, '10,20,30'),
            array('catalog/frontend/grid_per_page_values', null, '10,20,30'),
            array('catalog/frontend/list_allow_all', null, false)
        );

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($scopeConfig));

        $this->catalogConfig = $this->getMock(
            'Magento\Catalog\Model\Config',
            array('getAttributeUsedForSortByArray'),
            array(),
            '',
            false
        );
        $this->catalogConfig->expects($this->any())
            ->method('getAttributeUsedForSortByArray')
            ->will($this->returnValue(array('name' => array(), 'price' => array())));

        $context = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            array('getUrlBuilder', 'getScopeConfig', 'getLayout'),
            array(),
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
        $this->productListHelper = $this->getMock('Magento\Catalog\Helper\Product\ProductList',
            array(),
            array(),
            '',
            false
        );
        $this->productListHelper->expects($this->any())
            ->method('getAvailableViewMode')
            ->will($this->returnValue(array('list' => 'List')));

        $this->helper = $this->getMock('Magento\Catalog\Helper\Data', array('urlEncode'), array(), '', false);
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            'Magento\Catalog\Block\Product\ProductList\Toolbar',
            array(
                'context' => $context,
                'catalogConfig' => $this->catalogConfig,
                'toolbarModel' => $this->model,
                'helper' => $this->helper,
                'productListHelper' => $this->productListHelper
            )
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
        $this->helper->expects($this->once())
            ->method('urlEncode')
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

        $this->model->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($mode));

        $this->assertEquals($mode, $this->block->getCurrentMode());
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
            ->will($this->returnValue(array(10 => 10, 20 => 20)));
        $this->productListHelper->expects($this->once())
            ->method('getDefaultLimitPerPageValue')
            ->with($this->equalTo('list'))
            ->will($this->returnValue(10));

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
            ->will($this->returnValue(array(10 => 10, 20 => 20)));
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
}
