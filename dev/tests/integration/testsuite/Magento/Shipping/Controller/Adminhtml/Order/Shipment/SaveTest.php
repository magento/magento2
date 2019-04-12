<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use PHPUnit\Framework\Constraint\StringContains;

/**
 * Class verifies shipment creation functionality.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class SaveTest extends AbstractShipmentControllerTest
{
    /**
     * @var string
     */
    protected $uri = 'backend/admin/order_shipment/save';

    /**
     * @return void
     */
    public function testSendEmailOnShipmentSave()
    {
        $order = $this->prepareRequest(['shipment' => ['send_email' => true]]);
        $this->dispatch('backend/admin/order_shipment/save');

        $this->assertSessionMessages(
            $this->equalTo([(string)__('The shipment has been created.')]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('sales/order/view/order_id/' . $order->getEntityId()));

        $shipment = $this->getShipment($order);
        $message = $this->transportBuilder->getSentMessage();
        $subject = __('Your %1 order has shipped', $order->getStore()->getFrontendName())->render();
        $messageConstraint = $this->logicalAnd(
            new StringContains($order->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $shipment->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Shipment #{$shipment->getIncrementId()} for Order #{$order->getIncrementId()}"
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
