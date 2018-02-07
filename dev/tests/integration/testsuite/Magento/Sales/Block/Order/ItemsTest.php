<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Order;

/**
 * Test class for \Magento\Sales\Block\Order\Items
 */
class ItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\Items
     */
    private $model;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(\Magento\Framework\View\LayoutInterface::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @covers \Magento\Sales\Block\Order\Items::getItems
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testGetOrderItems()
    {
        $this->registerOrder();
        $this->model = $this->layout->createBlock(\Magento\Sales\Block\Order\Items::class);
        $this->assertTrue(count($this->model->getItems()) > 0);
    }

    /**
     * Test that pager is displayed for order items list.
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/sales/orders/items_per_page 3
     * @magentoDataFixture Magento/Sales/_files/order_item_list.php
     * @return void
     */
    public function testPagerIsDisplayed()
    {
        $this->registerOrder();

        /** @var \Magento\Sales\Block\Order\Items model */
        $this->model = $this->layout->createBlock(\Magento\Sales\Block\Order\Items::class, 'items_block');
        $this->layout->addBlock(
            $this->objectManager->get(\Magento\Theme\Block\Html\Pager::class),
            'sales_order_item_pager',
            'items_block'
        );
        $this->model->setLayout($this->layout);

        $this->assertTrue($this->model->isPagerDisplayed());
    }

    /**
     * Test that pager is not displayed for order items list.
     *
     * @magentoDataFixture Magento/Sales/_files/order_item_list.php
     * @return void
     */
    public function testPagerIsNotDisplayed()
    {
        $this->registerOrder();

        /** @var \Magento\Sales\Block\Order\Items model */
        $this->model = $this->layout->createBlock(\Magento\Sales\Block\Order\Items::class, 'items_block');
        $this->layout->addBlock(
            $this->objectManager->get(\Magento\Theme\Block\Html\Pager::class),
            'sales_order_item_pager',
            'items_block'
        );
        $this->model->setLayout($this->layout);

        $this->assertFalse($this->model->isPagerDisplayed());
    }

    /**
     * @covers \Magento\Sales\Block\Order\Items::getPagerHtml
     *
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture default/sales/orders/items_per_page 3
     * @magentoDataFixture Magento/Sales/_files/order_item_list.php
     * @return void
     */
    public function testGetPagerHtml()
    {
        $this->registerOrder();

        /** @var \Magento\Sales\Block\Order\Items model */
        $this->model = $this->layout->createBlock(\Magento\Sales\Block\Order\Items::class, 'items_block');
        $this->layout->addBlock(
            $this->objectManager->get(\Magento\Theme\Block\Html\Pager::class),
            'sales_order_item_pager',
            'items_block'
        );
        $this->model->setLayout($this->layout);

        $this->assertNotEmpty($this->model->getPagerHtml());
    }

    /**
     * @covers \Magento\Sales\Block\Order\Items::getOrder
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testGetOrder()
    {
        $order = $this->registerOrder();

        /** @var \Magento\Sales\Block\Order\Items model */
        $this->model = $this->layout->createBlock(\Magento\Sales\Block\Order\Items::class, 'items_block');
        $this->assertEquals($order, $this->model->getOrder());
    }

    /**
     * Register order in registry.
     *
     * @return \Magento\Sales\Model\Order
     */
    private function registerOrder()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->get(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $this->registry->register('current_order', $order);
        
        return $order;
    }
}
