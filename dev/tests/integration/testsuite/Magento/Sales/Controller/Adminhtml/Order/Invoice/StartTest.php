<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for invoice start action
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Invoice\Start
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class StartTest extends AbstractBackendController
{
    /** @var OrderFactory */
    private $orderFactory;

    /** @var Session */
    private $session;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderFactory = $this->_objectManager->get(OrderFactory::class);
        $this->session = $this->_objectManager->get(Session::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->session->getInvoiceItemQtys(true);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testExecute(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->session->setInvoiceItemQtys('test');
        $this->getRequest()->setMethod(Http::METHOD_GET)->setParams(['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/start');
        $this->assertRedirect($this->stringContains('sales/order_invoice/new'));
        $this->assertNull($this->session->getInvoiceItemQtys());
    }
}
