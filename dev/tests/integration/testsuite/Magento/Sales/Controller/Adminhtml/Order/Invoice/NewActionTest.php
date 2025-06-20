<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for new invoice action
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Invoice\NewAction
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class NewActionTest extends AbstractBackendController
{
    /** @var OrderFactory */
    private $orderFactory;

    /** @var Escaper */
    private $escaper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderFactory = $this->_objectManager->get(OrderFactory::class);
        $this->escaper = $this->_objectManager->get(Escaper::class);
    }

    /**
     * @return void
     */
    public function testWithNoExistingOrder(): void
    {
        $this->dispatchWithOrderId(863521);
        $expectedMessage = (string)__("The entity that was requested doesn't exist. Verify the entity and try again.");
        $this->assertSessionMessages($this->containsEqual($this->escaper->escapeHtml($expectedMessage)));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_bundle_and_invoiced.php
     *
     * @return void
     */
    public function testCanNotInvoice(): void
    {
        $expectedMessage = __('The order does not allow an invoice to be created.');
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->dispatchWithOrderId((int)$order->getEntityId());
        $this->assertSessionMessages($this->containsEqual((string)$expectedMessage), MessageInterface::TYPE_ERROR);
    }

    /**
     * Dispatch request with order_id param
     *
     * @param int $orderId
     * @return void
     */
    private function dispatchWithOrderId(int $orderId): void
    {
        $this->getRequest()->setMethod(Http::METHOD_GET)
            ->setParams(['order_id' => $orderId]);
        $this->dispatch('backend/sales/order_invoice/new');
    }
}
