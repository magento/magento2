<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Invoice;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class TotalsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Totals */
    private $block;

    /** @var OrderFactory */
    private $orderFactory;

    /** @var Registry */
    private $registry;

    /** @var CollectionFactory */
    private $invoiceCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Totals::class);
        $this->orderFactory = $this->objectManager->get(OrderFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->invoiceCollectionFactory = $this->objectManager->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_invoice');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_free_shipping_by_coupon_and_invoice.php
     *
     * @return void
     */
    public function testCollectTotals(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $invoice = $this->invoiceCollectionFactory->create()->setOrderFilter($order)->setPageSize(1)->getFirstItem();
        $this->registry->unregister('current_invoice');
        $this->registry->register('current_invoice', $invoice);

        $this->block->toHtml();
        $totals = $this->block->getTotals();
        $this->assertArrayHasKey('shipping', $totals);
        $this->assertEquals('0.0000', $totals['shipping']['value']);
        $this->assertEquals('Shipping & Handling (1234567890)', $totals['shipping']['label']);
    }
}
