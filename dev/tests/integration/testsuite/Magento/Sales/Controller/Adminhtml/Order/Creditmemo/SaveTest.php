<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use PHPUnit\Framework\Constraint\StringContains;

/**
 * Class tests creditmemo creation in backend.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/invoice.php
 */
class SaveTest extends AbstractCreditmemoControllerTest
{
    /**
     * @var string
     */
    protected $uri = 'backend/sales/order_creditmemo/save';

    /**
     * @return void
     */
    public function testSendEmailOnCreditmemoSave()
    {
        $order = $this->prepareRequest(['creditmemo' => ['send_email' => true]]);
        $this->dispatch('backend/sales/order_creditmemo/save');

        $this->assertSessionMessages(
            $this->equalTo([(string)__('You created the credit memo.')]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('sales/order/view/order_id/' . $order->getEntityId()));

        $creditMemo = $this->getCreditMemo($order);
        $message = $this->transportBuilder->getSentMessage();
        $subject = __('Credit memo for your %1 order', $order->getStore()->getFrontendName())->render();
        $messageConstraint = $this->logicalAnd(
            new StringContains($order->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $creditMemo->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Credit Memo #{$creditMemo->getIncrementId()} for Order #{$order->getIncrementId()}"
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

        $data = ['creditmemo' => ['do_offline' => true]];
        $data = array_replace_recursive($data, $params);

        $this->getRequest()->setPostValue($data);

        return $order;
    }
}
