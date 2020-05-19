<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for order info block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class InfoTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var LayoutInterface */
    private $layout;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

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
        $this->countryFactory = $this->objectManager->get(CountryFactory::class);
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
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testOrderStatus(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $block = $this->layout->createBlock(Info::class)->setTemplate('Magento_Sales::order/order_status.phtml');
        $this->assertStringContainsString((string)__($order->getStatusLabel()), strip_tags($block->toHtml()));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testOrderDate(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->registerOrder($order);
        $block = $this->layout->createBlock(Info::class)->setTemplate('Magento_Sales::order/order_date.phtml');
        $this->assertStringContainsString(
            (string)__('Order Date: %1', $block->formatDate($order->getCreatedAt(), \IntlDateFormatter::LONG)),
            strip_tags($block->toHtml())
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_two_items.php
     *
     * @return void
     */
    public function testOrderInformation(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $blockHtml = $this->layout->createBlock(Info::class)->toHtml();
        $this->assertOrderAddress($order->getShippingAddress(), $blockHtml);
        $this->assertOrderAddress($order->getBillingAddress(), $blockHtml);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//div[contains(@class, 'box-order-shipping-method') and contains(.//span, '%s')]"
                    . "//div[contains(text(), '%s')]",
                    __('Shipping Method'),
                    $order->getShippingDescription()
                ),
                $blockHtml
            ),
            'Shipping method for order wasn\'t found.'
        );
    }

    /**
     * Assert order address.
     *
     * @param OrderAddressInterface $address
     * @param string $html
     * @return void
     */
    private function assertOrderAddress(OrderAddressInterface $address, string $html): void
    {
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
