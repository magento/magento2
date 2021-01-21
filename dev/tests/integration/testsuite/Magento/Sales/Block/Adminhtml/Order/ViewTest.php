<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Backend\Model\Search\AuthorizationMock;
use Magento\Framework\Authorization;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Checks order create block
 *
 * @see \Magento\Sales\Block\Adminhtml\Order\View
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Registry */
    private $registry;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->addSharedInstance(
            $this->objectManager->get(AuthorizationMock::class),
            Authorization::class
        );
        $this->registry = $this->objectManager->get(Registry::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('sales_order');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testInvoiceButton(): void
    {
        $this->registerOrder('100000001');
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//button[@id=\'order_invoice\']',
                $this->layout->createBlock(View::class)->getButtonsHtml()
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_bundle_and_invoiced.php
     *
     * @return void
     */
    public function testInvoiceButtonIsNotVisible(): void
    {
        $this->registerOrder('100000001');
        $this->assertEmpty(
            Xpath::getElementsCountForXpath(
                '//button[@id=\'order_invoice\']',
                $this->layout->createBlock(View::class)->getButtonsHtml()
            )
        );
    }

    /**
     * Register order
     *
     * @param string $orderIncrementId
     * @return void
     */
    private function registerOrder(string $orderIncrementId): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        $this->registry->unregister('sales_order');
        $this->registry->register('sales_order', $order);
    }
}
