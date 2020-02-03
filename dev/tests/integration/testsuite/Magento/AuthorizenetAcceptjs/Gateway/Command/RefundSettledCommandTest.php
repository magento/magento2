<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\AbstractTest;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollectionFactory;

class RefundSettledCommandTest extends AbstractTest
{
    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/full_order_with_capture.php
     */
    public function testRefundSettledCommand()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('refund_settled');

        $order = $this->getOrderWithIncrementId('100000001');
        $payment = $order->getPayment();
        $payment->setCreditmemo($this->getCreditmemo($order));

        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/refund.php';
        $response = include __DIR__ . '/../../_files/response/refund.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $command->execute(
            [
                'payment' => $paymentDO,
                'amount' => 100.00
            ]
        );

        /** @var Payment $payment */
        $this->assertTrue($payment->getIsTransactionClosed());
        $this->assertSame('5678', $payment->getTransactionId());
    }

    /**
     * Retrieve creditmemo from order.
     *
     * @param Order $order
     * @return CreditmemoInterface
     */
    private function getCreditmemo(Order $order): CreditmemoInterface
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $creditMemoCollection */
        $creditMemoCollection = $this->objectManager->create(CreditmemoCollectionFactory::class)->create();

        /** @var CreditmemoInterface $creditMemo */
        $creditMemo = $creditMemoCollection
            ->setOrderFilter($order)
            ->setPageSize(1)
            ->getFirstItem();

        return $creditMemo;
    }
}
