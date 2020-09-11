<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Verify order view block
 *
 * @magentoAppArea adminhtml
 */
class ViewTest extends AbstractBackendController
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Totals
     */
    private $block;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->layout = $this->_objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(View::class, 'sales_order_view');
        $this->orderFactory = $this->_objectManager->get(OrderFactory::class);
    }

    /**
     * Verify getBackUrl from order view page
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_free_shipping_by_coupon.php
     */
    public function testGetBackUrl(): void
    {
        /** @var Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');
        $this->block->setOrder($order);
        $this->dispatch('backend/sales/order/view/order_id/' . $order->getEntityId());

        $orderGridUrl = 'http://localhost/index.php/backend/sales/order/index/';
        $this->assertStringContainsString($orderGridUrl, $this->block->getBackUrl());
        $this->assertStringNotContainsString('order_id/' . $order->getEntityId(), $this->block->getBackUrl());
    }
}
