<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Model\Order\Payment;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction as TransactionResourceModel;
use Magento\Sales\Test\Fixture\Transaction as TransactionFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(GuestCartFixture::class, as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    DataFixture(TransactionFixture::class, ['order_id' => '$order.id$'], 'transaction'),
]
class TransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $repository;

    /**
     * @var TransactionResourceModel
     */
    private $resourceModel;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->repository = $objectManager->create(TransactionRepositoryInterface::class);
        $this->resourceModel = $objectManager->create(TransactionResourceModel::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->orderRepository = $objectManager->create(OrderRepositoryInterface::class);
    }

    public function testLoadByTxnId(): void
    {
        $transaction = $this->fixtures->get('transaction');
        $order = $this->orderRepository->get($this->fixtures->get('order')->getId());
        $payment = $order->getPayment();

        $model = $this->getTransaction(
            'invalid_transaction_id',
            $order->getId(),
            $payment->getId()
        );

        $this->assertNull($model->getId());

        $model = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );

        $this->assertNotNull($model->getId());
        $this->assertEquals($transaction->getId(), $model->getId());
    }

    public function testChildTransaction(): void
    {
        $parentTransaction = $this->fixtures->get('transaction');
        $order = $this->orderRepository->get($this->fixtures->get('order')->getId());
        $payment = $order->getPayment();
        $payment->setTransactionId($parentTransaction->getTxnId() . '-' . TransactionInterface::TYPE_CAPTURE);
        $payment->setParentTransactionId($parentTransaction->getTxnId());
        $childTransaction = $payment->addTransaction(TransactionInterface::TYPE_CAPTURE);
        $this->orderRepository->save($order);

        $model = $this->getTransaction(
            $childTransaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $this->assertNotNull($model->getId());
        $this->assertEquals($childTransaction->getId(), $model->getId());
        $this->assertEquals($parentTransaction->getId(), $model->getParentId());
        $this->assertEquals($parentTransaction->getTxnId(), $model->getParentTxnId());

        $model = $this->getTransaction(
            $parentTransaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $this->assertNotNull($model->getId());
        $this->assertEquals($parentTransaction->getId(), $model->getId());
        $this->assertNull($model->getParentId());
        $this->assertNull($model->getParentTxnId());

        $children = $parentTransaction->getChildTransactions();
        $this->assertIsArray($children);
        $this->assertCount(1, $children);
        $this->assertEquals($childTransaction->getId(), reset($children)->getId());
    }

    public function testUpdateAdditionalInformation(): void
    {
        $transaction = $this->fixtures->get('transaction');
        $order = $this->orderRepository->get($this->fixtures->get('order')->getId());
        $payment = $order->getPayment();
        $model = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );

        // check initial state
        $this->assertNotNull($model->getId());
        $this->assertNull($model->getParentId());
        $this->assertNull($model->getParentTxnId());
        $this->assertEquals([], $model->getAdditionalInformation());
        $this->assertEquals(TransactionInterface::TYPE_AUTH, $model->getTxnType());
        $this->assertEquals(0, $model->getIsClosed());

        // update additional_information and save
        $model->setAdditionalInformation('test_key', 'test_value');
        $this->repository->save($model);

        // check updated state
        $updatedModel = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $this->assertNull($model->getParentId());
        $this->assertNull($model->getParentTxnId());
        $this->assertEquals(['test_key' => 'test_value'], $updatedModel->getAdditionalInformation());
        $this->assertEquals(TransactionInterface::TYPE_AUTH, $model->getTxnType());
        $this->assertEquals(0, $model->getIsClosed());
    }

    public function testCloseWhenTransactionIdIsSameAsParentId(): void
    {
        $transaction = $this->fixtures->get('transaction');
        $order = $this->orderRepository->get($this->fixtures->get('order')->getId());
        $payment = $order->getPayment();
        $model = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $this->assertNotNull($model->getId());

        // simulate the case when transaction_id is same as parent_id
        $model->setParentId($model->getId());
        $this->repository->save($model);

        // attempt to close the transaction and test that it does not cause infinite recursion
        // reload the model to ensure we are working with the latest data
        $model = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $model->close(false);
        $this->repository->save($model);

        $model = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $this->assertEquals(1, $model->getIsClosed(), 'Transaction should be closed');
    }

    public function testGetChildTransactionsRecursivelyWhenTransactionIdIsSameAsParentId(): void
    {
        $transaction = $this->fixtures->get('transaction');
        $order = $this->orderRepository->get($this->fixtures->get('order')->getId());
        $payment = $order->getPayment();
        $model = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $this->assertNotNull($model->getId());

        // simulate the case when transaction_id is same as parent_id
        $model->setParentId($model->getId());
        $this->repository->save($model);

        // attempt to get child transactions and test that it does not cause infinite recursion
        // reload the model to ensure we are working with the latest data
        $model = $this->getTransaction(
            $transaction->getTxnId(),
            $order->getId(),
            $payment->getId()
        );
        $getChildrenRecursive = function ($transaction, $depth) use (&$getChildrenRecursive) {
            if ($depth <= 0) {
                $this->fail('Maximum recursion depth reached. Possible infinite loop detected.');
            }
            $children = $transaction->getChildTransactions();
            foreach ($children as $child) {
                $getChildrenRecursive($child, $depth - 1);
            }
        };
        $getChildrenRecursive($model, 10);
        $children = $transaction->getChildTransactions();
        $this->assertIsArray($children, 'Child transactions should be an array');
        $this->assertEmpty($children, 'Child transactions should be empty because of the recursive relationship');
    }

    private function getTransaction(string $txnId, int $orderId, int $paymentId): TransactionInterface
    {
        return $this->resourceModel->loadObjectByTxnId(
            $this->repository->create(),
            $orderId,
            $paymentId,
            $txnId
        );
    }
}
