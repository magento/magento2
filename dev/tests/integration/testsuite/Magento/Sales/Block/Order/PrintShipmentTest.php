<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for print shipment block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class PrintShipmentTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var Layout */
    private $layout;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/shipment_for_order_with_customer.php
     *
     * @return void
     */
    public function testOrderInformation(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $block = $this->layout->createBlock(PrintShipment::class);
        $orderDate = $block->formatDate($order->getCreatedAt(), \IntlDateFormatter::LONG);
        $templates = [
            'Order status' => [
                'template' => 'Magento_Sales::order/order_status.phtml',
                'expected_data' => (string)__($order->getStatusLabel()),
            ],
            'Order date' => [
                'template' => 'Magento_Sales::order/order_date.phtml',
                'expected_data' => (string)__('Order Date: %1', $orderDate),
            ],
        ];
        foreach ($templates as $key => $data) {
            $this->assertStringContainsString(
                $data['expected_data'],
                strip_tags($block->setTemplate($data['template'])->toHtml()),
                sprintf('%s wasn\'t found.', $key)
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
