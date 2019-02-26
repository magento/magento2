<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\Constraint\StringContains;

/**
 * Class verifies shipment add comment functionality.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/shipment.php
 */
class AddCommentTest extends AbstractShipmentControllerTest
{
    /**
     * @var string
     */
    protected $uri = 'backend/admin/order_shipment/addComment';

    /**
     * @return void
     */
    public function testSendEmailOnShipmentCommentAdd()
    {
        $comment = 'Test Shipment Comment';
        $order = $this->prepareRequest(
            [
                'comment' => ['comment' => $comment, 'is_customer_notified' => true],
            ]
        );
        $this->dispatch('backend/admin/order_shipment/addComment');
        $html = $this->getResponse()->getBody();
        $this->assertContains($comment, $html);

        $message = $this->transportBuilder->getSentMessage();
        $subject =__('Update to your %1 shipment', $order->getStore()->getFrontendName())->render();
        $messageConstraint = $this->logicalAnd(
            new StringContains($order->getBillingAddress()->getName()),
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
        $this->assertThat($message->getRawMessage(), $messageConstraint);
    }

    /**
     * @inheritdoc
     */
    public function testAclHasAccess()
    {
        $this->prepareRequest(['comment', ['comment' => 'Comment']]);

        parent::testAclHasAccess();
    }

    /**
     * @inheritdoc
     */
    public function testAclNoAccess()
    {
        $this->prepareRequest(['comment', ['comment' => 'Comment']]);

        parent::testAclNoAccess();
    }

    /**
     * @param array $params
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    private function prepareRequest(array $params = [])
    {
        $order = $this->getOrder('100000001');
        $shipment = $this->getShipment($order);

        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setParams(
            [
                'id' => $shipment->getEntityId(),
                'form_key' => $this->formKey->getFormKey(),
            ]
        );

        $data = $params ?? [];
        $this->getRequest()->setPostValue($data);

        return $order;
    }
}
