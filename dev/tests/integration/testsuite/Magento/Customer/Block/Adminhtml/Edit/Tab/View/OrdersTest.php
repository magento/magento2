<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class OrdersTest
 *
 * @magentoAppArea adminhtml
 */
class OrdersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The orders block under test.
     *
     * @var Orders
     */
    private $block;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get('Magento\Framework\Registry');
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);

        $this->block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\View\Orders',
            '',
            ['coreRegistry' => $this->coreRegistry]
        );
        $this->block->getPreparedCollection();
    }

    /**
     * Execute post test cleanup.
     */
    public function tearDown()
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->block->setCollection(null);
    }

    /**
     * Verify that the correct Url is return for a row in the orders grid.
     */
    public function testGetRowUrl()
    {
        $row = new \Magento\Framework\Object(['id' => 1]);
        $this->assertContains('sales/order/view/order_id/1', $this->block->getRowUrl($row));
    }

    /**
     * Verify that the grid headers are visible.
     */
    public function testGetHeadersVisibility()
    {
        $this->assertTrue($this->block->getHeadersVisibility());
    }

    /**
     * Verify the integrity of the orders collection.
     */
    public function testGetCollection()
    {
        $collection = $this->block->getCollection();
        $this->assertEquals(0, $collection->getSize());
        $this->assertEquals(5, $collection->getPageSize());
        $this->assertEquals(1, $collection->getCurPage());
    }

    /**
     * Check the empty grid Html.
     */
    public function testToHtmlEmptyOrders()
    {
        $this->assertEquals(0, $this->block->getCollection()->getSize());
        $this->assertContains("We couldn't find any records.", $this->block->toHtml());
    }

    /**
     * Verify the contents of the grid Html when there is a sales order.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Customer/_files/sales_order.php
     */
    public function testToHtmlWithOrders()
    {
        $html = $this->block->toHtml();
        $this->assertContains('100000001', $html);
        $this->assertContains('firstname lastname', $html);
        $this->assertEquals(1, $this->block->getCollection()->getSize());
    }
}
