<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class Relation
 * @deprecated
 */
class Refund implements RelationInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Process relations for CreditMemo
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws \Exception
     * @return void
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $object */
        if ($object->getState() == \Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED) {
            $this->prepareOrder($object);
            if ($object->getInvoice()) {
                $this->prepareInvoice($object);
                $this->invoiceRepository->save($object->getInvoice());
            }
            $this->preparePayment($object);
            $this->orderRepository->save($object->getOrder());
        }
    }

    /**
     * Prepare order data for refund
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    protected function prepareOrder(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $baseOrderRefund = $this->priceCurrency->round(
            $order->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
        );
        $orderRefund = $this->priceCurrency->round(
            $order->getTotalRefunded() + $creditmemo->getGrandTotal()
        );
        $order->setBaseTotalRefunded($baseOrderRefund);
        $order->setTotalRefunded($orderRefund);

        $order->setBaseSubtotalRefunded($order->getBaseSubtotalRefunded() + $creditmemo->getBaseSubtotal());
        $order->setSubtotalRefunded($order->getSubtotalRefunded() + $creditmemo->getSubtotal());

        $order->setBaseTaxRefunded($order->getBaseTaxRefunded() + $creditmemo->getBaseTaxAmount());
        $order->setTaxRefunded($order->getTaxRefunded() + $creditmemo->getTaxAmount());
        $order->setBaseDiscountTaxCompensationRefunded(
            $order->getBaseDiscountTaxCompensationRefunded() + $creditmemo->getBaseDiscountTaxCompensationAmount()
        );
        $order->setDiscountTaxCompensationRefunded(
            $order->getDiscountTaxCompensationRefunded() + $creditmemo->getDiscountTaxCompensationAmount()
        );

        $order->setBaseShippingRefunded($order->getBaseShippingRefunded() + $creditmemo->getBaseShippingAmount());
        $order->setShippingRefunded($order->getShippingRefunded() + $creditmemo->getShippingAmount());

        $order->setBaseShippingTaxRefunded(
            $order->getBaseShippingTaxRefunded() + $creditmemo->getBaseShippingTaxAmount()
        );
        $order->setShippingTaxRefunded($order->getShippingTaxRefunded() + $creditmemo->getShippingTaxAmount());

        $order->setAdjustmentPositive($order->getAdjustmentPositive() + $creditmemo->getAdjustmentPositive());
        $order->setBaseAdjustmentPositive(
            $order->getBaseAdjustmentPositive() + $creditmemo->getBaseAdjustmentPositive()
        );

        $order->setAdjustmentNegative($order->getAdjustmentNegative() + $creditmemo->getAdjustmentNegative());
        $order->setBaseAdjustmentNegative(
            $order->getBaseAdjustmentNegative() + $creditmemo->getBaseAdjustmentNegative()
        );

        $order->setDiscountRefunded($order->getDiscountRefunded() + $creditmemo->getDiscountAmount());
        $order->setBaseDiscountRefunded($order->getBaseDiscountRefunded() + $creditmemo->getBaseDiscountAmount());

        if ($creditmemo->getDoTransaction()) {
            $order->setTotalOnlineRefunded($order->getTotalOnlineRefunded() + $creditmemo->getGrandTotal());
            $order->setBaseTotalOnlineRefunded($order->getBaseTotalOnlineRefunded() + $creditmemo->getBaseGrandTotal());
        } else {
            $order->setTotalOfflineRefunded($order->getTotalOfflineRefunded() + $creditmemo->getGrandTotal());
            $order->setBaseTotalOfflineRefunded(
                $order->getBaseTotalOfflineRefunded() + $creditmemo->getBaseGrandTotal()
            );
        }

        $order->setBaseTotalInvoicedCost(
            $order->getBaseTotalInvoicedCost() - $creditmemo->getBaseCost()
        );
    }

    /**
     * Prepare invoice data for refund
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    protected function prepareInvoice(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        if ($creditmemo->getInvoice()) {
            $creditmemo->getInvoice()->setIsUsedForRefund(true);
            $creditmemo->getInvoice()->setBaseTotalRefunded(
                $creditmemo->getInvoice()->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
            );
            $creditmemo->setInvoiceId($creditmemo->getInvoice()->getId());
        }
    }

    /**
     * Prepare payment data for refund
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    protected function preparePayment(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        if (!$creditmemo->getPaymentRefundDisallowed()) {
            $creditmemo->getOrder()->getPayment()->refund($creditmemo);
        }
    }
}
