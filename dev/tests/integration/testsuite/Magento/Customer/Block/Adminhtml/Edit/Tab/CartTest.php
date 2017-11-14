<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
 *
 * @magentoAppArea adminhtml
 */
class CartTest extends \PHPUnit_Framework_TestCase
{
    const CUSTOMER_ID_VALUE = 1234;

    /** @var \Magento\Backend\Block\Template\Context */
    private $_context;

    /** @var \Magento\Framework\Registry */
    private $_coreRegistry;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $_storeManager;

    /** @var Cart */
    private $_block;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManager::class);
        $this->_context = $this->_objectManager->get(
            \Magento\Backend\Block\Template\Context::class,
            ['storeManager' => $this->_storeManager]
        );

        $this->_coreRegistry = $this->_objectManager->get(\Magento\Framework\Registry::class);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, self::CUSTOMER_ID_VALUE);

        $this->_block = $this->_objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart::class,
            '',
            ['context' => $this->_context, 'registry' => $this->_coreRegistry]
        );
    }

    public function tearDown()
    {
        $this->_coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    public function testGetCustomerId()
    {
        $this->assertEquals(self::CUSTOMER_ID_VALUE, $this->_block->getCustomerId());
    }

    public function testGetGridUrl()
    {
        $this->assertContains('/backend/customer/index/cart', $this->_block->getGridUrl());
    }

    public function testGetGridParentHtml()
    {
        $this->_block = $this->_objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart::class,
            '',
            []
        );
        $mockCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_block->setCollection($mockCollection);
        $this->assertContains(
            "<div class=\"admin__data-grid-header admin__data-grid-toolbar\"",
            $this->_block->getGridParentHtml()
        );
    }

    public function testGetRowUrl()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setProductId(1);
        $this->assertContains('/backend/catalog/product/edit/id/1', $this->_block->getRowUrl($row));
    }

    public function testGetHtml()
    {
        $html = $this->_block->toHtml();
        $this->assertContains("<div id=\"customer_cart_grid\"", $html);
        $this->assertContains("<div class=\"admin__data-grid-header admin__data-grid-toolbar\"", $html);
        $this->assertContains("customer_cart_gridJsObject = new varienGrid(\"customer_cart_grid\",", $html);
        $this->assertContains(
            'backend\u002Fcustomer\u002Fcart_product_composite_cart\u002Fconfigure\u002Fcustomer_id\u002F'
            . self::CUSTOMER_ID_VALUE,
            $html
        );
    }
}
