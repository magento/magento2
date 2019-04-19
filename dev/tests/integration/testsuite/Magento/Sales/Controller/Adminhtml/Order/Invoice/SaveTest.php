<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use PHPUnit\Framework\Constraint\StringContains;

/**
 * Class tests invoice creation in backend.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class SaveTest extends AbstractInvoiceControllerTest
{
    /**
     * @var string
     */
    protected $uri = 'backend/sales/order_invoice/save';

    /**
     * @return void
     */
    public function testSendEmailOnInvoiceSave()
    {
        $order = $this->prepareRequest(['invoice' => ['send_email' => true]]);
        $this->dispatch('backend/sales/order_invoice/save');

        $this->assertSessionMessages(
            $this->equalTo([(string)__('The invoice has been created.')]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('sales/order/view/order_id/' . $order->getEntityId()));

        $invoice = $this->getInvoiceByOrder($order);
        $message = $this->transportBuilder->getSentMessage();
        $subject = __('Invoice for your %1 order', $order->getStore()->getFrontendName())->render();
        $messageConstraint = $this->logicalAnd(
            new StringContains($invoice->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $invoice->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Invoice #{$invoice->getIncrementId()} for Order #{$order->getIncrementId()}"
            )
        );

        $this->assertEquals($message->getSubject(), $subject);
        $this->assertThat($message->getRawMessage(), $messageConstraint);
    }

    /**
     * @inheritdoc
     */
    public function testAclHasAccess()
    {
        $this->prepareRequest();

        parent::testAclHasAccess();
    }

    /**
     * @inheritdoc
     */
    public function testAclNoAccess()
    {
        $this->prepareRequest();

        parent::testAclNoAccess();
    }

    /**
     * @param array $params
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    private function prepareRequest(array $params = [])
    {
        $order = $this->getOrder('100000001');
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setParams(
            [
                'order_id' => $order->getEntityId(),
                'form_key' => $this->formKey->getFormKey(),
            ]
        );

        $data = $params ?? [];
        $this->getRequest()->setPostValue($data);

        return $order;
    }
}
