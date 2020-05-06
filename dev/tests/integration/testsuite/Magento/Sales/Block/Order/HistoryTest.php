<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer order grid.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class HistoryTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Session */
    private $customerSession;

    /** @var History */
    private $block;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(History::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->logout();

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testCustomerOrderGridWithoutOrders(): void
    {
        $this->customerSession->loginById(1);
        $this->assertStringContainsString(
            (string)$this->block->getEmptyOrdersMessage(),
            strip_tags($this->block->toHtml())
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testCustomerOrderGridWithOrder(): void
    {
        $this->customerSession->loginById(1);
        $this->assertCustomerOrderGrid(['100000001'], $this->block->toHtml());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/orders_with_customer.php
     *
     * @return void
     */
    public function testCustomerOrderGridWithSomeOrders(): void
    {
        $this->customerSession->loginById(1);
        $ordersToCheck = ['100000002', '100000003', '100000004', '100000005', '100000006'];
        $this->assertCustomerOrderGrid($ordersToCheck, $this->block->toHtml());
    }

    /**
     * Assert customer order grid.
     *
     * @param array $ordersToCheck
     * @param string $blockHtml
     * @return void
     */
    private function assertCustomerOrderGrid(array $ordersToCheck, string $blockHtml): void
    {
        foreach ($ordersToCheck as $orderIncrementId) {
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
            $rowXpath = sprintf("//td[contains(@class,'id') and contains(text(), '%s')]", $order->getRealOrderId());
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/following-sibling::td[contains(@class, 'date') and contains(text(), '%s')]",
                        $this->block->formatDate($order->getCreatedAt())
                    ),
                    $blockHtml
                ),
                sprintf('Created date for order #%s wasn\'t found in row.', $orderIncrementId)
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/following-sibling::td[contains(@class, 'total')]/span[contains(text(), '%s')]",
                        $order->getTotal()
                    ),
                    $blockHtml
                ),
                sprintf(
                    'Order Totals for order #%s wasn\'t found or not equal to "%s" in row.',
                    $orderIncrementId,
                    $order->getTotal()
                )
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/following-sibling::td[contains(@class, 'status') and contains(text(), '%s')]",
                        $order->getStatusLabel()
                    ),
                    $blockHtml
                ),
                sprintf(
                    'Order status for order #%s wasn\'t found or not equal to "%s" in row.',
                    $orderIncrementId,
                    $order->getStatusLabel()
                )
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/following-sibling::td/a[contains(@href, 'sales/order/view/order_id/%s')]"
                        . "/span[contains(text(), '%s')]",
                        $order->getId(),
                        __('View Order')
                    ),
                    $blockHtml
                ),
                sprintf('View order button for order #%s wasn\'t found.', $orderIncrementId)
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/following-sibling::td/a[contains(@data-post,"
                        . "'sales\/order\/reorder\/order_id\/%s')]/span[contains(text(), '%s')]",
                        $order->getId(),
                        __('Reorder')
                    ),
                    $blockHtml
                ),
                sprintf('Reorder button for order #%s wasn\'t found.', $orderIncrementId)
            );
        }
    }
}
