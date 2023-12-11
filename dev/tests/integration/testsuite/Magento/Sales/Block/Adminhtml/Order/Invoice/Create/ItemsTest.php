<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Invoice\Create;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks invoiced items grid appearance
 *
 * @see \Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Items
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ItemsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Items */
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
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Items::class);
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
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     *
     * @return void
     */
    public function testGetUpdateButtonHtml(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $invoice = $this->invoiceCollectionFactory->create()->setOrderFilter($order)->setPageSize(1)->getFirstItem();
        $this->registry->unregister('current_invoice');
        $this->registry->register('current_invoice', $invoice);
        $this->block->toHtml();
        $button = $this->block->getChildBlock('update_button');
        $this->assertEquals((string)__('Update Qty\'s'), (string)$button->getLabel());
        $this->assertStringContainsString(
            sprintf('sales/index/updateQty/order_id/%u/', (int)$order->getEntityId()),
            $button->getOnClick()
        );
    }
}
