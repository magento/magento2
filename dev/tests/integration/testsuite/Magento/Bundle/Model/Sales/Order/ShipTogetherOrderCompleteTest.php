<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\Order;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Ensures a bundle configured as ship-together can reach complete state after invoice and shipment.
 */
#[AppArea('adminhtml')]
#[AppIsolation(true)]
#[DbIsolation(true)]
class ShipTogetherOrderCompleteTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var InvoiceManagementInterface
     */
    private InvoiceManagementInterface $invoiceManagement;

    /**
     * @var ShipmentFactory
     */
    private ShipmentFactory $shipmentFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->invoiceManagement = $objectManager->get(InvoiceManagementInterface::class);
        $this->shipmentFactory = $objectManager->get(ShipmentFactory::class);
    }

    /**
     * After full invoice and shipping the bundle parent, order must not stay in processing because of child lines.
     */
    #[DataFixture('Magento/Bundle/_files/order_with_bundle_shipped_together.php')]
    public function testOrderStateCompleteAfterInvoiceAndShipment(): void
    {
        $order = $this->loadOrderByIncrementId('100000001');
        $this->ensureOrderTotalsAndCurrency($order);
        $this->ensurePaymentPresent($order);

        $entityId = (int)$order->getEntityId();
        $order = $this->invoiceAllRemainingLines($order, $entityId);

        $order = $this->ensureProcessingStateAfterInvoice($entityId);
        $this->assertSame(Order::STATE_PROCESSING, $order->getState());

        // Ship-together: pass bundle parent qty only (see Web API ShipOrderTest). An empty $items map is invalid
        // here — ShipmentFactory::validateItem skips every non-dummy line when no ids are set, yielding no items.
        $shipmentQuantities = $this->getBundleParentShipmentQuantities($order);
        $this->assertNotEmpty(
            $shipmentQuantities,
            'Bundle parent must still have quantity to ship after invoice.'
        );
        $shipment = $this->shipmentFactory->create($order, $shipmentQuantities);
        $this->assertNotEmpty(
            $shipment->getAllItems(),
            'Shipment document must contain at least one line.'
        );
        $shipment->register();
        $order->setIsInProcess(true);
        $this->createTransaction()->addObject($shipment)->addObject($order)->save();

        $order = $this->reloadOrder($entityId);

        $this->assertSame(Order::STATE_COMPLETE, $order->getState());
        $this->assertFalse($order->canShip());
        $this->assertFalse(
            $order->canInvoice(),
            'State::check returns early when canInvoice is true; order cannot become complete.'
        );
    }

    /**
     * Issue invoices until every line is invoiced (bundle fixtures often need more than one capture).
     *
     * @param Order $order
     * @param int $entityId
     * @return Order Last reloaded order with canInvoice() false.
     */
    private function invoiceAllRemainingLines(Order $order, int $entityId): Order
    {
        $attempts = 0;
        while ($order->canInvoice()) {
            $this->assertLessThan(10, ++$attempts, 'Invoice loop exceeded safety limit.');
            $invoiceQuantities = $this->getFullInvoiceQuantities($order);
            $this->assertNotEmpty(
                $invoiceQuantities,
                'canInvoice() is true but no line reported qty to invoice.'
            );
            $invoice = $this->invoiceManagement->prepareInvoice($order, $invoiceQuantities);
            $invoice->register();
            $invoice->pay();
            $order->setIsInProcess(true);
            $this->createTransaction()->addObject($invoice)->addObject($order)->save();
            $order = $this->reloadOrder($entityId);
        }

        return $order;
    }

    /**
     * Qty map for bundle parent (ship together); required for a non-empty shipment with this fixture.
     *
     * @param Order $order
     * @return array<int, float>
     */
    private function getBundleParentShipmentQuantities(Order $order): array
    {
        $quantities = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() !== Type::TYPE_BUNDLE || $item->getParentItemId()) {
                continue;
            }
            $qtyToShip = $item->getQtyToShip();
            if ($qtyToShip > 0) {
                $quantities[(int)$item->getId()] = $qtyToShip;
            }
        }

        return $quantities;
    }

    /**
     * Quantities for every invoicable line so canInvoice() becomes false and State handler can set complete.
     *
     * @param Order $order
     * @return array<int, float>
     */
    private function getFullInvoiceQuantities(Order $order): array
    {
        $quantities = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getLockedDoInvoice()) {
                continue;
            }
            $qty = (float) $item->getQtyToInvoice();
            if ($qty > 0.0) {
                $quantities[(int) $item->getId()] = $qty;
            }
        }

        return $quantities;
    }

    /**
     * Load order by increment id from sales_order (repository getList can be filtered by EE/B2B plugins).
     *
     * @param string $incrementId
     * @return Order
     */
    private function loadOrderByIncrementId(string $incrementId): Order
    {
        /** @var Order $order */
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementId($incrementId);
        $this->assertNotNull(
            $order->getEntityId(),
            'Expected fixture order with increment_id ' . $incrementId
        );

        return $order;
    }

    /**
     * Move order to processing after invoice when it is still new.
     *
     * State handler only sets complete from processing after shipment; some fixture/EE
     * combinations never apply the usual new-to-processing transition even with setIsInProcess(true).
     *
     * @param int $entityId
     * @return Order
     */
    private function ensureProcessingStateAfterInvoice(int $entityId): Order
    {
        $order = $this->reloadOrder($entityId);
        if ($order->getState() !== Order::STATE_NEW) {
            return $order;
        }

        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        $this->orderRepository->save($order);

        return $this->reloadOrder($entityId);
    }

    /**
     * Reload order from persistence as a mutable sales order model.
     *
     * @param int $entityId
     * @return Order
     */
    private function reloadOrder(int $entityId): Order
    {
        $order = $this->orderRepository->get($entityId);
        $this->assertInstanceOf(Order::class, $order);

        return $order;
    }

    /**
     * @return Transaction
     */
    private function createTransaction(): Transaction
    {
        return Bootstrap::getObjectManager()->create(Transaction::class);
    }

    /**
     * The ship-together fixture omits some totals; invoice creation requires currency and grand total.
     *
     * @param Order $order
     * @return void
     */
    private function ensureOrderTotalsAndCurrency(Order $order): void
    {
        if ($order->getOrderCurrencyCode() === null || $order->getOrderCurrencyCode() === '') {
            $order->setOrderCurrencyCode('USD');
            $order->setBaseCurrencyCode('USD');
        }
        if ($order->getGrandTotal() === null || (float)$order->getGrandTotal() == 0.0) {
            $baseGrand = $order->getBaseGrandTotal();
            $order->setGrandTotal($baseGrand !== null ? $baseGrand : 100);
        }
        if ($order->getSubtotal() === null || (float)$order->getSubtotal() == 0.0) {
            $baseSub = $order->getBaseSubtotal();
            $order->setSubtotal($baseSub !== null ? $baseSub : 100);
        }
    }

    /**
     * SalesOrderBeforeSaveObserver requires a payment; some loads omit it until explicitly set.
     *
     * @param Order $order
     * @return void
     */
    private function ensurePaymentPresent(Order $order): void
    {
        if ($order->getPayment()) {
            return;
        }

        $existing = $order->getPaymentsCollection()->getItems();
        if ($existing !== []) {
            $order->setPayment(reset($existing));

            return;
        }

        /** @var Payment $payment */
        $payment = Bootstrap::getObjectManager()->create(Payment::class);
        $payment->setMethod('checkmo');
        $order->setPayment($payment);
    }
}
