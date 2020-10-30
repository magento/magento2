<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\PrintOrder;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for print invoice block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var InvoiceInterfaceFactory */
    private $invoiceFactory;

    /** @var PageFactory */
    private $pageFactory;

    /** @var CountryFactory */
    private $countryFactory;

    /** @var OrderPaymentInterfaceFactory */
    private $orderPaymentFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->invoiceFactory = $this->objectManager->get(InvoiceInterfaceFactory::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
        $this->countryFactory = $this->objectManager->get(CountryFactory::class);
        $this->orderPaymentFactory = $this->objectManager->create(OrderPaymentInterfaceFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_order');
        $this->registry->unregister('current_invoice');

        parent::tearDown();
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGetInvoiceTotalsHtml(): void
    {
        $order = $this->orderFactory->create();
        $this->registerOrder($order);
        $payment = $this->orderPaymentFactory->create();
        $payment->setMethod('checkmo');
        $order->setPayment($payment);
        $block = $this->layout->createBlock(Invoice::class, 'block');
        $childBlock = $this->layout->addBlock(Text::class, 'invoice_totals', 'block');
        $expectedHtml = '<b>Any html</b>';
        $invoice = $this->invoiceFactory->create();
        $this->assertEmpty($childBlock->getInvoice());
        $this->assertNotEquals($expectedHtml, $block->getInvoiceTotalsHtml($invoice));
        $childBlock->setText($expectedHtml);
        $actualHtml = $block->getInvoiceTotalsHtml($invoice);
        $this->assertSame($invoice, $childBlock->getInvoice());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoices_for_items.php
     *
     * @return void
     */
    public function testPrintInvoice(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertNotNull($invoice->getId());
        $this->registerOrder($order);
        $this->registerInvoice($invoice);
        $blockHtml = $this->renderPrintInvoiceBlock();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//div[contains(@class, 'order-title')]/strong[contains(text(), '%s')]",
                    __('Invoice #%1', (int)$invoice->getIncrementId())
                ),
                $blockHtml
            ),
            sprintf('Title for %s was not found.', __('Invoice #%1', (int)$invoice->getIncrementId()))
        );
        $this->assertOrderInformation($order, $blockHtml);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoices_for_items.php
     *
     * @return void
     */
    public function testOrderInformation(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $block = $this->layout->createBlock(Invoice::class);
        $orderDate = $block->formatDate($order->getCreatedAt(), \IntlDateFormatter::LONG);
        $templates = [
            'Order status' => [
                'template' => 'Magento_Sales::order/order_status.phtml',
                'expected_data' => (string)__($order->getStatusLabel()),
            ],
            'Order date' => [
                'template' => 'Magento_Sales::order/order_date.phtml',
                'expected_data' => (string)__('Order Date: %1', $orderDate),
            ],
        ];
        foreach ($templates as $key => $data) {
            $this->assertStringContainsString(
                $data['expected_data'],
                strip_tags($block->setTemplate($data['template'])->toHtml()),
                sprintf('%s wasn\'t found.', $key)
            );
        }
    }

    /**
     * Assert order information block.
     *
     * @param OrderInterface $order
     * @param string $html
     * @return void
     */
    private function assertOrderInformation(OrderInterface $order, string $html): void
    {
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                "//div[contains(@class, 'block-order-details-view')]"
                . "//strong[contains(text(), '" . __('Order Information') . "')]",
                $html
            ),
            __('Order Information') . ' title wasn\'t found.'
        );
        foreach ([$order->getShippingAddress(), $order->getBillingAddress()] as $address) {
            $addressBoxXpath = sprintf("//div[contains(@class, 'box-order-%s-address')]", $address->getAddressType())
                . "//address[contains(., '%s')]";
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($addressBoxXpath, $address->getName()), $html),
                sprintf('Customer name for %s address wasn\'t found.', $address->getAddressType())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $addressBoxXpath,
                        $this->countryFactory->create()->loadByCode($address->getData('country_id'))->getName()
                    ),
                    $html
                ),
                sprintf('Country for %s address wasn\'t found.', $address->getAddressType())
            );
            $attributes = ['company', 'street', 'city', 'region', 'postcode', 'telephone'];
            foreach ($attributes as $key) {
                $this->assertEquals(
                    1,
                    Xpath::getElementsCountForXpath(sprintf($addressBoxXpath, $address->getData($key)), $html),
                    sprintf('%s for %s address wasn\'t found.', $key, $address->getAddressType())
                );
            }
        }
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//div[contains(@class, 'box-order-shipping-method') and contains(.//strong, '%s')]"
                    . "//div[contains(text(), '%s')]",
                    __('Shipping Method'),
                    $order->getShippingDescription()
                ),
                $html
            ),
            'Shipping method for order wasn\'t found.'
        );
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

    /**
     * Register invoice in registry.
     *
     * @param InvoiceInterface $invoice
     * @return void
     */
    private function registerInvoice(InvoiceInterface $invoice): void
    {
        $this->registry->unregister('current_invoice');
        $this->registry->register('current_invoice', $invoice);
    }

    /**
     * Render print invoice block.
     *
     * @return string
     */
    private function renderPrintInvoiceBlock(): string
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'sales_order_printinvoice',
        ]);
        $page->getLayout()->generateXml();
        $printInvoiceBlock = $page->getLayout()->getBlock('sales.order.print.invoice');
        $this->assertNotFalse($printInvoiceBlock);

        return $printInvoiceBlock->toHtml();
    }
}
