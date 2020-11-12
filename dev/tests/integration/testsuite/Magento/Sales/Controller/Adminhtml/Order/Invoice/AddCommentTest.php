<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\Constraint\StringContains;

/**
 * Class verifies invoice add comment functionality.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/invoice.php
 */
class AddCommentTest extends AbstractInvoiceControllerTest
{
    /**
     * @var string
     */
    protected $uri = 'backend/sales/order_invoice/addComment';

    /**
     * @return void
     */
    public function testSendEmailOnAddInvoiceComment(): void
    {
        $comment = 'Test Invoice Comment';
        $order = $this->getOrder('100000001');
        $invoice = $this->getInvoiceByOrder($order);
        $this->prepareRequest(
            ['comment' => ['comment' => $comment, 'is_customer_notified' => true]],
            ['id' => $invoice->getEntityId()]
        );
        $this->dispatch('backend/sales/order_invoice/addComment');

        $html = $this->getResponse()->getBody();
        $this->assertStringContainsString($comment, $html);

        $message = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($message);
        $subject = __('Update to your %1 invoice', $order->getStore()->getFrontendName())->render();
        $messageConstraint = $this->logicalAnd(
            new StringContains($order->getCustomerName()),
            new RegularExpression(
                sprintf(
                    "/Your order #%s has been updated with a status of.*%s/",
                    $order->getIncrementId(),
                    $order->getFrontendStatusLabel()
                )
            ),
            new StringContains($comment)
        );

        $this->assertEquals($message->getSubject(), $subject);
        $bodyParts = $message->getBody()->getParts();
        $this->assertThat(reset($bodyParts)->getRawContent(), $messageConstraint);
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
}
