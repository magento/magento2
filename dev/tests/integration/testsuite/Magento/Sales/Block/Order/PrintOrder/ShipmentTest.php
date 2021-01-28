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
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for print shipment block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var PageFactory */
    private $pageFactory;

    /** @var CountryFactory */
    private $countryFactory;

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
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
        $this->countryFactory = $this->objectManager->get(CountryFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_order');
        $this->registry->unregister('current_shipment');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/shipment_for_two_items.php
     *
     * @return void
     */
    public function testPrintShipment(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $shipment = $order->getShipmentsCollection()->getFirstItem();
        $this->assertNotNull($shipment->getId());
        $this->registerOrder($order);
        $this->registerShipment($shipment);
        $blockHtml = $this->renderPrintShipmentBlock();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//div[contains(@class, 'order-title')]/strong[contains(text(), '%s')]",
                    __('Shipment #%1', $shipment->getIncrementId())
                ),
                $blockHtml
            ),
            sprintf('Title for %s was not found.', __('Shipment #%1', $shipment->getIncrementId()))
        );
        $this->assertOrderInformation($order, $blockHtml);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/shipment_for_order_with_customer.php
     *
     * @return void
     */
    public function testOrderInformation(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $block = $this->layout->createBlock(Shipment::class);
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
            $addressBoxXpath = ($address->getAddressType() == 'shipping')
                ? "//div[contains(@class, 'box-order-shipping-address')]//address[contains(., '%s')]"
                : "//div[contains(@class, 'box-order-billing-method')]//address[contains(., '%s')]";
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
     * Register shipment in registry.
     *
     * @param ShipmentInterface $shipment
     * @return void
     */
    private function registerShipment(ShipmentInterface $shipment): void
    {
        $this->registry->unregister('current_shipment');
        $this->registry->register('current_shipment', $shipment);
    }

    /**
     * Render print shipment block.
     *
     * @return string
     */
    private function renderPrintShipmentBlock(): string
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'sales_order_printshipment',
        ]);
        $page->getLayout()->generateXml();
        $printShipmentBlock = $page->getLayout()->getBlock('sales.order.print.shipment');
        $this->assertNotFalse($printShipmentBlock);

        return $printShipmentBlock->toHtml();
    }
}
