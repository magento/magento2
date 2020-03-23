<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Invoice;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for view invoice items block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /** @var Items */
    private $block;

    /** @var InvoiceInterfaceFactory */
    private $invoiceFactory;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var PageFactory */
    private $pageFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Items::class, 'block');
        $this->invoiceFactory = $this->objectManager->get(InvoiceInterfaceFactory::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->registry->unregister('current_order');

        parent::tearDown();
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGetInvoiceTotalsHtml(): void
    {
        $childBlock = $this->layout->addBlock(Text::class, 'invoice_totals', 'block');
        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getInvoice());
        $invoice = $this->invoiceFactory->create();
        $this->assertNotEquals($expectedHtml, $this->block->getInvoiceTotalsHtml($invoice));
        $childBlock->setText($expectedHtml);
        $actualHtml = $this->block->getInvoiceTotalsHtml($invoice);
        $this->assertSame($invoice, $childBlock->getInvoice());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGetInvoiceCommentsHtml(): void
    {
        $childBlock = $this->layout->addBlock(
            Text::class,
            'invoice_comments',
            'block'
        );
        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getEntity());
        $this->assertEmpty($childBlock->getTitle());
        $invoice = $this->invoiceFactory->create();
        $this->assertNotEquals($expectedHtml, $this->block->getInvoiceCommentsHtml($invoice));
        $childBlock->setText($expectedHtml);
        $actualHtml = $this->block->getInvoiceCommentsHtml($invoice);
        $this->assertSame($invoice, $childBlock->getEntity());
        $this->assertNotEmpty($childBlock->getTitle());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/two_invoices_for_items.php
     *
     * @return void
     */
    public function testDisplayingInvoices(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $blockHtml = $this->renderInvoiceItemsBlock();
        $this->assertInvoicesBlock($order, $blockHtml);
    }

    /**
     * Assert invoices block.
     *
     * @param OrderInterface $order
     * @param string $blockHtml
     * @return void
     */
    private function assertInvoicesBlock(OrderInterface $order, string $blockHtml): void
    {
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//a[contains(@href, 'sales/order/printInvoice/order_id/%s')]/span[contains(text(), '%s')]",
                    $order->getId(),
                    __('Print All Invoices')
                ),
                $blockHtml
            ),
            sprintf('%s button was not found.', __('Print All Invoices'))
        );
        $this->assertNotCount(0, $order->getInvoiceCollection(), 'Invoice collection is empty');
        foreach ($order->getInvoiceCollection() as $invoice) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        "//div[contains(@class, 'order-title')]/strong[contains(text(), '%s')]",
                        __('Invoice #') . $invoice->getIncrementId()
                    ),
                    $blockHtml
                ),
                sprintf('Title for %s was not found.', __('Invoice #') . $invoice->getIncrementId())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        "//a[contains(@href, 'sales/order/printInvoice/invoice_id/%s')]/span[contains(text(), '%s')]",
                        $invoice->getId(),
                        __('Print Invoice')
                    ),
                    $blockHtml
                ),
                sprintf('%s button for #%s was not found.', __('Print Invoice'), $invoice->getIncrementId())
            );
            $this->assertInvoiceItems($invoice, $blockHtml);
        }
    }

    /**
     * Assert invoice items list.
     *
     * @param InvoiceInterface $invoice
     * @param string $blockHtml
     * @return void
     */
    private function assertInvoiceItems(InvoiceInterface $invoice, string $blockHtml): void
    {
        $this->assertNotCount(0, $invoice->getItemsCollection(), 'Invoice items collection is empty');
        foreach ($invoice->getItemsCollection() as $item) {
            $itemRowXpath = sprintf(
                "//table[@id='my-invoice-table-%s']//tr[@id='order-item-row-%s']",
                $invoice->getId(),
                $item->getId()
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'name')]/strong[contains(text(), '%s')]",
                        $item->getName()
                    ),
                    $blockHtml
                ),
                sprintf('Item with name %s wasn\'t found.', $item->getName())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'sku') and contains(text(), '%s')]",
                        $item->getSku()
                    ),
                    $blockHtml
                ),
                sprintf('Item with sku %s wasn\'t found.', $item->getSku())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'price')]//span[contains(text(), '%01.2f')]",
                        $item->getPrice()
                    ),
                    $blockHtml
                ),
                sprintf('Price for item %s wasn\'t found or not equals to %s.', $item->getName(), $item->getPrice())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'qty')]/span[contains(text(), '%d')]",
                        $item->getQty()
                    ),
                    $blockHtml
                ),
                sprintf('Qty for item %s wasn\'t found or not equals to %s.', $item->getName(), $item->getQty())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $itemRowXpath . "/td[contains(@class, 'subtotal')]//span[contains(text(), '%01.2f')]",
                        $item->getRowTotal()
                    ),
                    $blockHtml
                ),
                sprintf(
                    'Row total for item %s wasn\'t found or not equals to %s.',
                    $item->getName(),
                    $item->getRowTotal()
                )
            );
        }
    }

    /**
     * Render invoice items block.
     *
     * @return string
     */
    private function renderInvoiceItemsBlock(): string
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'sales_order_invoice',
        ]);
        $page->getLayout()->generateXml();
        $invoiceItemsBlock = $page->getLayout()->getBlock('invoice_items')->unsetChild('invoice_totals');
        $invoiceItemsBlock->getRequest()->setRouteName('sales')->setControllerName('order')->setActionName('invoice');

        return $invoiceItemsBlock->toHtml();
    }

    /**
     * Register order in registry.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function registerOrder(OrderInterface $order): void
    {
        $this->registry->unregister('current_order');
        $this->registry->register('current_order', $order);
    }
}
