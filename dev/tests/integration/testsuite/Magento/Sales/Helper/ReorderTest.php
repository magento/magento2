<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for reorder helper.
 *
 * @see \Magento\Sales\Helper\Reorder
 * @magentoDbIsolation enabled
 */
class ReorderTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Reorder */
    private $helper;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var Session */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->get(Reorder::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->customerSession = $this->objectManager->get(Session::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testCanReorderForGuest(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->assertTrue($this->helper->canReorder($order->getId()));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_two_items.php
     *
     * @return void
     */
    public function testCanReorderForLoggedCustomer(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->customerSession->setCustomerId($order->getCustomerId());
        $this->assertTrue($this->helper->canReorder($order->getId()));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/order_state_hold.php
     *
     * @return void
     */
    public function testCanReorderHoldOrderForLoggedCustomer(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->customerSession->setCustomerId(1);
        $this->assertFalse($this->helper->canReorder($order->getId()));
    }

    /**
     * @magentoConfigFixture current_store sales/reorder/allow 0
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testCanReorderConfigDisabled(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->assertFalse($this->helper->canReorder($order->getId()));
    }
}
