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
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
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

        $this->assertSubtotal($blockTotals->toHtml(), (float) $order->getSubtotalInclTax());
        $this->assertShipping($blockTotals->toHtml(), (float) $order->getShippingInclTax());
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
