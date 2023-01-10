<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Item\Renderer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for default renderer order items.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DefaultRendererTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var DefaultRenderer */
    private $block;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var PageFactory */
    private $pageFactory;

    /** @var Registry */
    private $registry;

    /**
     * @var array
     */
    private $defaultFieldsToCheck = [
        'name' => "//td[contains(@class, 'name')]/strong[contains(text(), '%s')]",
        'sku' => "//td[contains(@class, 'sku') and contains(text(), '%s')]",
        'qty' => "//td[contains(@class, 'qty') and contains(text(), '%d')]",
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(DefaultRenderer::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_order');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/shipment_for_order_with_customer.php
     *
     * @return void
     */
    public function testDisplayingShipmentItem(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $shipment = $order->getShipmentsCollection()->getFirstItem();
        $this->assertNotNull($shipment->getId());
        $item = $shipment->getAllItems()[0] ?? null;
        $this->assertNotNull($item);
        $blockHtml = $this->block->setTemplate('Magento_Sales::order/shipment/items/renderer/default.phtml')
            ->setItem($item)->toHtml();
        foreach ($this->defaultFieldsToCheck as $key => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($xpath, $item->getData($key)), $blockHtml),
                sprintf('Item %s wasn\'t found or not equals to %s.', $key, $item->getData($key))
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/refunds_for_items.php
     *
     * @return void
     */
    public function testCreditmemoItemTotalAmount(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $creditmemo = $order->getCreditmemosCollection()->getFirstItem();
        $this->assertNotNull($creditmemo->getId());
        $item = $creditmemo->getItemsCollection()->getFirstItem();
        $this->assertNotNull($item->getId());
        $this->assertEquals(10.00, $this->block->getTotalAmount($item));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_two_items.php
     *
     * @return void
     */
    public function testPrintOrderItem(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $item = $order->getItemsCollection()->getFirstItem();
        $this->assertNotNull($item->getId());
        $block = $this->getBlock('sales_order_print', 'sales.order.print.renderers.default');
        $this->assertNotFalse($block);
        $blockHtml = $block->setItem($item)->toHtml();
        $fieldsToCheck = [
            'name' => "//td[contains(@class, 'name')]/strong[contains(text(), '%s')]",
            'sku' => "//td[contains(@class, 'sku') and contains(text(), '%s')]",
            'price' => "//td[contains(@class, 'price')]//span[contains(text(), '%01.2f')]",
            'qty_ordered' => "//td[contains(@class, 'qty')]//span[contains(text(), '" . __('Ordered')
                . "')]/following-sibling::span[contains(text(), '%d')]",
            'row_total' => "//td[contains(@class, 'subtotal')]//span[contains(text(), '%01.2f')]",
        ];
        foreach ($fieldsToCheck as $key => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($xpath, $item->getData($key)), $blockHtml),
                sprintf('Item %s wasn\'t found or not equals to %s.', $key, $item->getData($key))
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoices_for_items.php
     *
     * @return void
     */
    public function testPrintInvoiceItem(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertNotNull($invoice->getId());
        $item = $invoice->getItemsCollection()->getFirstItem();
        $this->assertNotNull($item->getId());
        $block = $this->getBlock('sales_order_printinvoice', 'sales.order.print.invoice.renderers.default');
        $this->assertNotFalse($block);
        $blockHtml = $block->setItem($item)->toHtml();
        $additionalFields = [
            'price' => "//td[contains(@class, 'price')]//span[contains(text(), '%01.2f')]",
            'qty' => "//td[contains(@class, 'qty')]/span[contains(text(), '%d')]",
            'row_total' => "//td[contains(@class, 'subtotal')]//span[contains(text(), '%01.2f')]",
        ];
        $this->defaultFieldsToCheck = array_merge($this->defaultFieldsToCheck, $additionalFields);
        foreach ($this->defaultFieldsToCheck as $key => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($xpath, $item->getData($key)), $blockHtml),
                sprintf('Item %s wasn\'t found or not equals to %s.', $key, $item->getData($key))
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/shipment_for_order_with_customer.php
     *
     * @return void
     */
    public function testPrintShipmentItem(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $shipment = $order->getShipmentsCollection()->getFirstItem();
        $this->assertNotNull($shipment->getId());
        $item = $shipment->getAllItems()[0] ?? null;
        $this->assertNotNull($item);
        $block = $this->getBlock('sales_order_printshipment', 'sales.order.print.shipment.renderers.default');
        $this->assertNotFalse($block);
        $blockHtml = $block->setItem($item)->toHtml();
        foreach ($this->defaultFieldsToCheck as $key => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($xpath, $item->getData($key)), $blockHtml),
                sprintf('Item %s wasn\'t found or not equals to %s.', $key, $item->getData($key))
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/refunds_for_items.php
     *
     * @return void
     */
    public function testPrintCreditmemoItem(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $creditmemo = $order->getCreditmemosCollection()->getFirstItem();
        $this->assertNotNull($creditmemo->getId());
        $item = $creditmemo->getItemsCollection()->getFirstItem();
        $this->assertNotNull($item->getId());
        $block = $this->getBlock('sales_order_printcreditmemo', 'sales.order.print.creditmemo.renderers.default');
        $this->assertNotFalse($block);
        $blockHtml = $block->setItem($item)->toHtml();
        $additionalFields = [
            'price' => "//td[contains(@class, 'price')]//span[contains(text(), '%01.2f')]",
            'row_total' => "//td[contains(@class, 'subtotal')]//span[contains(text(), '%01.2f')]",
            'discount_amount' => "//td[contains(@class, 'discount')]/span[contains(text(), '%01.2f')]",
        ];
        $this->defaultFieldsToCheck = array_merge($this->defaultFieldsToCheck, $additionalFields);
        foreach ($this->defaultFieldsToCheck as $key => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($xpath, $item->getData($key)), $blockHtml),
                sprintf('Item %s wasn\'t found or not equals to %s.', $key, $item->getData($key))
            );
        }
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//td[contains(@class, 'total')]/span[contains(text(), '%01.2f')]",
                    $this->block->getTotalAmount($item)
                ),
                $blockHtml
            ),
            sprintf('Item total wasn\'t found or not equals to %s.', $this->block->getTotalAmount($item))
        );
    }

    /**
     * Get block.
     *
     * @param string $handle
     * @param string $blockName
     * @return AbstractBlock
     */
    private function getBlock(string $handle, string $blockName): AbstractBlock
    {
        $page = $this->pageFactory->create();
        $page->addHandle(['default', $handle]);
        $page->getLayout()->generateXml();

        return $page->getLayout()->getBlock($blockName);
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
