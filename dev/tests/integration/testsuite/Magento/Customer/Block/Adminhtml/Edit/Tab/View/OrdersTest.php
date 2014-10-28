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
            array('coreRegistry' => $this->coreRegistry)
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
        $row = new \Magento\Framework\Object(array('id' => 1));
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
