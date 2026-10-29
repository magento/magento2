<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use PHPUnit\Framework\Constraint\StringContains;

/**
 * Class verifies invoice send email functionality.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/invoice.php
 */
class EmailTest extends AbstractInvoiceControllerTest
{
    /**
     * @var string
     */
    protected $uri = 'backend/sales/order_invoice/email';

    /**
     * @return void
     */
    public function testSendInvoiceEmail(): void
    {
        $order = $this->getOrder('100000001');
        $invoice = $this->getInvoiceByOrder($order);

        $this->getRequest()->setParams(['invoice_id' => $invoice->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/email');

        $this->assertSessionMessages(
            $this->equalTo([(string)__('You sent the message.')]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );

        $redirectUrl = sprintf(
            'sales/invoice/view/order_id/%s/invoice_id/%s',
            $order->getEntityId(),
            $invoice->getEntityId()
        );
        $this->assertRedirect($this->stringContains($redirectUrl));

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
        $this->assertThat(quoted_printable_decode($message->getBody()->bodyToString()), $messageConstraint);
    }

    /**
     * @inheritdoc
     */
    public function testAclHasAccess()
    {
        $order = $this->getOrder('100000001');
        $invoice = $this->getInvoiceByOrder($order);
        $this->uri .= '/invoice_id/' . $invoice->getEntityId();

        parent::testAclHasAccess();
    }

    /**
     * @inheritdoc
     */
    public function testAclNoAccess()
    {
        $order = $this->getOrder('100000001');
        $invoice = $this->getInvoiceByOrder($order);
        $this->uri .= '/invoice_id/' . $invoice->getEntityId();

        parent::testAclNoAccess();
    }
}
