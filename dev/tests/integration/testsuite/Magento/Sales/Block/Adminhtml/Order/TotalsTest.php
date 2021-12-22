<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Block\Adminhtml\Order\Totals\Tax;
use Magento\Sales\Model\Order;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\Collection as TaxCollection;
use Magento\Tax\Model\Sales\Order\Tax as SalesTax;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class TotalsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $om;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->om = Bootstrap::getObjectManager();
        $this->layout = $this->om->get(LayoutInterface::class);
        $this->orderFactory = $this->om->get(OrderInterfaceFactory::class);
    }

    /**
     * Test block totals including tax.
     *
     * @magentoConfigFixture default_store tax/sales_display/subtotal 2
     * @magentoConfigFixture default_store tax/sales_display/shipping 2
     * @magentoConfigFixture default_store tax/sales_display/full_summary 1
     *
     * @magentoDataFixture Magento/Tax/_files/order_with_tax.php
     *
     * @return void
     */
    public function testTotalsInclTax(): void
    {
        $order = $this->prepareOrderInclTax('100000001');

        $blockTotals = $this->getBlockTotals()->setOrder($order);
        $this->assertSubtotal($blockTotals->toHtml(), (float) $order->getSubtotal());
        $this->assertShipping($blockTotals->toHtml(), (float) $order->getShippingAmount());

        $blockTax = $this->getBlockTax();
        $blockTotals->setChild('child_tax_block', $blockTax);
        $blockTax->initTotals();

        /** @var TaxCollection $taxCollection */
        $taxCollection = $this->om->get(TaxCollection::class);
        $tax = $taxCollection->loadByOrder($order)->getItems();
        $tax = array_pop($tax);
        $this->assertTaxRate($blockTax->toHtml(), $tax);
        $this->assertSubtotal($blockTotals->toHtml(), (float) $order->getSubtotalInclTax());
        $this->assertShipping($blockTotals->toHtml(), (float) $order->getShippingInclTax());
    }

    /**
     * Test block totals including canceled amount.
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testTotalCanceled(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $order->cancel();
        $blockTotals = $this->getBlockTotals()->setOrder($order);
        $this->assertCanceled($blockTotals->toHtml(), $order->getTotalCanceled());
    }

    /**
     * Check if canceled amount present in block.
     *
     * @param string $blockTotalsHtml
     * @param float $amount
     * @return void
     */
    private function assertCanceled(string $blockTotalsHtml, float $amount): void
    {
        $this->assertTrue(
            $this->isBlockContainsTotalAmount($blockTotalsHtml, __('Total Canceled'), $amount),
            'Canceled amount is missing or incorrect.'
        );
    }

    /**
     * Check if subtotal amount present in block.
     *
     * @param string $blockTotalsHtml
     * @param float $amount
     * @return void
     */
    private function assertSubtotal(string $blockTotalsHtml, float $amount): void
    {
        $this->assertTrue(
            $this->isBlockContainsTotalAmount($blockTotalsHtml, __('Subtotal'), $amount),
            'Subtotal amount is missing or incorrect.'
        );
    }

    /**
     * Check if shipping amount present in block.
     *
     * @param string $blockTotalsHtml
     * @param float $amount
     * @return void
     */
    private function assertShipping(string $blockTotalsHtml, float $amount): void
    {
        $this->assertTrue(
            $this->isBlockContainsTotalAmount($blockTotalsHtml, __('Shipping & Handling'), $amount),
            'Shipping & Handling amount is missing or incorrect.'
        );
    }

    /**
     * Check if tax rate present in block.
     *
     * @param string $blockTaxHtml
     * @param SalesTax $tax
     * @return void
     */
    private function assertTaxRate(string $blockTaxHtml, SalesTax $tax)
    {
        $this->assertStringContainsString(
            $tax->getTitle() . ' (' . (int)$tax->getAmount() . '%)',
            $blockTaxHtml
        );
    }

    /**
     * Prepare order for test.
     *
     * @param string $incrementId
     * @return Order
     */
    private function prepareOrderInclTax(string $incrementId): Order
    {
        /** @var Order $order */
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        $order->setSubtotalInclTax(110);
        $order->setBaseSubtotalInclTax(110);

        $order->setShippingAmount(10);
        $order->setBaseShippingAmount(10);
        $order->setShippingInclTax(11);
        $order->setBaseShippingInclTax(11);

        return $order;
    }

    /**
     * Create block totals.
     *
     * @return Totals
     */
    private function getBlockTotals(): Totals
    {
        /** @var Totals $block */
        $block = $this->layout->createBlock(Totals::class, 'block_totals');
        $block->setTemplate('Magento_Sales::order/totals.phtml');

        return $block;
    }

    /**
     * Create block tax.
     *
     * @return Tax
     */
    private function getBlockTax(): Tax
    {
        /** @var Tax $block */
        $block = $this->layout->createBlock(Tax::class, 'block_tax');
        $block->setTemplate('Magento_Sales::order/totals/tax.phtml');

        return $block;
    }

    /**
     * Check if amount present in appropriate block node.
     *
     * @param string $blockTotalsHtml
     * @param Phrase $totalLabel
     * @param float $totalAmount
     * @return bool
     */
    private function isBlockContainsTotalAmount(
        string $blockTotalsHtml,
        Phrase $totalLabel,
        float $totalAmount
    ): bool {
        $dom = new \DOMDocument();
        $dom->loadHTML($blockTotalsHtml);
        $query = sprintf(
            "//tr[contains(., '%s')]//span[contains(text(), '%01.2f')]",
            $totalLabel,
            $totalAmount
        );

        return (bool) (new \DOMXPath($dom))->query($query)->count();
    }
}
