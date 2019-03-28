<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml;

use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Totals
 */
class TotalsTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /** @var LayoutInterface */
    private $layout;

    /** @var Totals */
    private $block;

    /** @var OrderFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->layout = $this->_objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Totals::class, 'totals_block');
        $this->orderFactory = $this->_objectManager->get(OrderFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_free_shipping_by_coupon.php
     */
    public function testShowShippingCoupon()
    {
        /** @var Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->block->setOrder($order);
        $this->block->toHtml();

        $shippingTotal = $this->block->getTotal('shipping');
        $this->assertNotFalse($shippingTotal, 'Shipping method is absent on the total\'s block.');
        $this->assertContains(
            '1234567890',
            $shippingTotal->getLabel(),
            'Coupon code is absent in the shipping method label name.'
        );
    }
}
