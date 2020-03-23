<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\TestCase;

/**
 * Tests order items block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsTest extends TestCase
{
    /** @var Items */
    private $block;

    /** @var LayoutInterface */
    private $layout;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var PageFactory */
    private $pageFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->registry->unregister('current_order');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testGetOrderItems(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $this->block = $this->layout->createBlock(Items::class);
        $this->assertTrue(count($this->block->getItems()) > 0);
    }

    /**
     * @magentoConfigFixture default/sales/orders/items_per_page 3
     * @magentoDataFixture Magento/Sales/_files/order_item_list.php
     *
     * @return void
     */
    public function testPagerIsDisplayed(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $this->block = $this->layout->createBlock(Items::class, 'items_block');
        $this->layout->addBlock(
            $this->objectManager->get(Pager::class),
            'sales_order_item_pager',
            'items_block'
        );
        $this->block->setLayout($this->layout);
        $this->assertTrue($this->block->isPagerDisplayed());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_item_list.php
     *
     * @return void
     */
    public function testPagerIsNotDisplayed(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $this->block = $this->layout->createBlock(Items::class, 'items_block');
        $this->layout->addBlock(
            $this->objectManager->get(Pager::class),
            'sales_order_item_pager',
            'items_block'
        );
        $this->block->setLayout($this->layout);
        $this->assertFalse($this->block->isPagerDisplayed());
    }

    /**
     * @magentoConfigFixture default/sales/orders/items_per_page 3
     * @magentoDataFixture Magento/Sales/_files/order_item_list.php
     *
     * @return void
     */
    public function testGetPagerHtml(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $this->block = $this->layout->createBlock(Items::class, 'items_block');
        $this->layout->addBlock(
            $this->objectManager->get(Pager::class),
            'sales_order_item_pager',
            'items_block'
        );
        $this->block->setLayout($this->layout);
        $this->assertNotEmpty($this->block->getPagerHtml());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testGetOrder(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $this->block = $this->layout->createBlock(Items::class, 'items_block');
        $this->assertEquals($order, $this->block->getOrder());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_two_items_by_customer.php
     *
     * @return void
     */
    public function testDisplayingOrderItems(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $blockHtml = $this->renderOrderItemsBlock();
        $this->assertOrderItems($order->getItemsCollection(), $blockHtml);
    }

    /**
     * Render order items block.
     *
     * @return string
     */
    private function renderOrderItemsBlock(): string
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'sales_order_view',
        ]);
        $page->getLayout()->generateXml();
        $orderItemsBlock = $page->getLayout()->getBlock('order_items')->unsetChild('order_totals');

        return $orderItemsBlock->toHtml();
    }

    /**
     * Assert order items list.
     *
     * @param Collection $orderItems
     * @param string $blockHtml
     * @return void
     */
    private function assertOrderItems(Collection $orderItems, string $blockHtml): void
    {
        $this->assertNotCount(0, $orderItems, 'Order items collection is empty');
        foreach ($orderItems as $item) {
            $itemRowXpath = sprintf("//tr[@id='order-item-row-%s']", $item->getItemId());
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'name')]/strong[contains(text(), '%s')]",
                        $item->getName()
                    ),
                    $blockHtml
                ),
                sprintf('Item with name %s wasn\'t found.', $item->getName())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'sku') and contains(text(), '%s')]",
                        $item->getSku()
                    ),
                    $blockHtml
                ),
                sprintf('Item with sku %s wasn\'t found.', $item->getSku())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'price')]//span[contains(text(), '%01.2f')]",
                        $item->getPrice()
                    ),
                    $blockHtml
                ),
                sprintf('Price for item %s wasn\'t found or not equals to %s.', $item->getName(), $item->getPrice())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'qty')]//span[contains(text(), '%s')]"
                        . "/following-sibling::span[contains(text(), '%d')]",
                        __('Ordered'),
                        $item->getQtyOrdered()
                    ),
                    $blockHtml
                ),
                sprintf('Qty for item %s wasn\'t found or not equals to %s.', $item->getName(), $item->getQtyOrdered())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'subtotal')]//span[contains(text(), '%01.2f')]",
                        $item->getRowTotal()
                    ),
                    $blockHtml
                ),
                sprintf(
                    'Row total for item %s wasn\'t found or not equals to %s.',
                    $item->getName(),
                    $item->getRowTotal()
                )
            );
        }
    }

    /**
     * Register order in registry.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function registerOrder(OrderInterface $order): void
    {
        $this->registry->unregister('current_order');
        $this->registry->register('current_order', $order);
    }
}
