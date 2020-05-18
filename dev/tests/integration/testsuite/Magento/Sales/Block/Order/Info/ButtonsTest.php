<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Info;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Test for order action buttons.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ButtonsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var Session */
    private $customerSession;

    /** @var OrderInterface */
    private $order;

    /** @var Buttons */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->order = $this->objectManager->get(OrderInterface::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Buttons::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_order');
        $this->customerSession->logout();

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testDisplayingOrderActionButtons(): void
    {
        $this->customerSession->loginById(1);
        $order = $this->order->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $blockHtml = $this->block->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//a[contains(@data-post, 'sales\/order\/reorder\/order_id\/%s')]/span[contains(text(), '%s')]",
                    $order->getId(),
                    __('Reorder')
                ),
                $blockHtml
            ),
            sprintf('%s button wasn\'t found.', __('Reorder'))
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//a[contains(@href, 'sales/order/print/order_id/%s')]/span[contains(text(), '%s')]",
                    $order->getId(),
                    __('Print Order')
                ),
                $blockHtml
            ),
            sprintf('%s button wasn\'t found.', __('Print Order'))
        );
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
