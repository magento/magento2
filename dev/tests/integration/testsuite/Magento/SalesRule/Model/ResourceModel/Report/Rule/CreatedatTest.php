<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report\Rule;

/**
 * Createdat test for check report totals calculate
 *
 * @magentoDataFixture Magento/SalesRule/_files/order_with_coupon.php
 */
class CreatedatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider orderParamsDataProvider()
     * @param $orderParams
     */
    public function testTotals($orderParams)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001')
            ->setBaseGrandTotal($orderParams['base_subtotal'])
            ->setSubtotal($orderParams['base_subtotal'])
            ->setBaseSubtotal($orderParams['base_subtotal'])
            ->setBaseDiscountAmount($orderParams['base_discount_amount'])
            ->setBaseTaxAmount($orderParams['base_tax_amount'])
            ->setBaseSubtotalInvoiced($orderParams['base_subtotal_invoiced'])
            ->setBaseDiscountInvoiced($orderParams['base_discount_invoiced'])
            ->setBaseTaxInvoiced($orderParams['base_tax_invoiced'])
            ->setBaseShippingAmount(0)
            ->setBaseToGlobalRate(1)
            ->setCouponCode('1234567890')
            ->setCreatedAt('2014-10-25 10:10:10')
            ->save();
        // refresh report statistics
        /** @var \Magento\SalesRule\Model\ResourceModel\Report\Rule $reportResource */
        $reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\SalesRule\Model\ResourceModel\Report\Rule'
        );
        $reportResource->aggregate();
        /** @var \Magento\SalesRule\Model\ResourceModel\Report\Collection $reportCollection */
        $reportCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\SalesRule\Model\ResourceModel\Report\Collection'
        );
        $salesRuleReportItem = $reportCollection->getFirstItem();
        $this->assertEquals($this->getTotalAmount($order), $salesRuleReportItem['total_amount']);
        $this->assertEquals($this->getTotalAmountActual($order), $salesRuleReportItem['total_amount_actual']);
    }

    /**
     * Repeat sql formula from \Magento\SalesRule\Model\ResourceModel\Report\Rule\Createdat::_aggregateByOrder
     *
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    private function getTotalAmount(\Magento\Sales\Model\Order $order)
    {
        return (
            $order->getBaseSubtotal() - $order->getBaseSubtotalCanceled()
            - (abs($order->getBaseDiscountAmount()) - abs($order->getBaseDiscountCanceled()))
            + ($order->getBaseTaxAmount() - $order->getBaseTaxCanceled())
        ) * $order->getBaseToGlobalRate();
    }

    /**
     * Repeat sql formula from \Magento\SalesRule\Model\ResourceModel\Report\Rule\Createdat::_aggregateByOrder
     *
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    private function getTotalAmountActual(\Magento\Sales\Model\Order $order)
    {
        return (
            $order->getBaseSubtotalInvoiced() - $order->getSubtotalRefunded()
            - abs($order->getBaseDiscountInvoiced()) - abs($order->getBaseDiscountRefunded())
            + $order->getBaseTaxInvoiced() - $order->getBaseTaxRefunded()
        ) * $order->getBaseToGlobalRate();
    }

    /**
     * @return array
     */
    public function orderParamsDataProvider()
    {
        return [
            [
                [
                    'base_discount_amount' => 98.80,
                    'base_subtotal' => 494,
                    'base_tax_amount' => 8.8,
                    'base_subtotal_invoiced' => 494,
                    'base_discount_invoiced' => 98.80,
                    'base_tax_invoiced' => 8.8
                ]
            ]
        ];
    }
}
