<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class OrdersTest
 *
 * @magentoAppArea adminhtml
 */
class OrdersTest extends \PHPUnit\Framework\TestCase
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
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);

        $this->block = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\Orders::class,
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
     * Verify that a valid Url is returned for a given sales order row.
     */
    public function testGetRowUrl()
    {
        $row = new \Magento\Framework\DataObject(['id' => 1]);
        $this->assertContains('sales/order/view/order_id/1', $this->block->getRowUrl($row));
    }

    /**
     * Verify that a valid grid Url is returned.
     */
    public function testGetGridUrl()
    {
        $this->assertContains('customer/index/orders', $this->block->getGridUrl());
    }

    /**
     * Verify that the sales order grid Html is valid and contains no records.
     */
    public function testToHtml()
    {
        $this->assertContains("We couldn't find any records.", $this->block->toHtml());
    }
}
