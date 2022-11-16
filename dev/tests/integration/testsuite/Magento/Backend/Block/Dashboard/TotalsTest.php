<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard;

use DOMDocument;
use DOMXPath;
use Magento\Backend\Block\Dashboard\Totals;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Model\Order;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

class TotalsTest extends AbstractBackendController
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        Bootstrap::getInstance()->loadArea(Area::AREA_ADMINHTML);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 100], 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$']),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart2.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart2.id$'], 'order2'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order2.id$']),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order2.id$']),
    ]
    public function testTotals(): void
    {
        $orderData = $this->fixtures->get('order');
        $order = $this->objectManager->get(Order::class);
        $order->loadByIncrementId($orderData->getIncrementId());
        $dateTime = new \DateTime('now');
        $order->setCreatedAt($dateTime->modify('-23 hours')->format(DateTime::DATETIME_PHP_FORMAT));
        $order->save();
        $order2Data = $this->fixtures->get('order2');
        $order2 = $this->objectManager->get(Order::class);
        $order2->loadByIncrementId($order2Data->getIncrementId());
        $dateTime2 = new \DateTime('now');
        $order2->setCreatedAt($dateTime2->modify('-1 hour')->format(DateTime::DATETIME_PHP_FORMAT));
        $order2->save();
        $layout = $this->objectManager->get(LayoutInterface::class);
        $totalsDefaultBlock = $layout->createBlock(Totals::class);
        $totalsDefaults = $totalsDefaultBlock->getTotals();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('block', 'totals');
        $this->getRequest()->setParam('period', Period::PERIOD_24_HOURS);
        $this->dispatch('backend/admin/dashboard/ajaxBlock/');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $actualHtml = $this->getResponse()->getBody();
        $dom = new DOMDocument();
        $dom->loadHTML($actualHtml);
        $totals24Hours = (new DOMXPath($dom))->query('//*[@id="dashboard_diagram_totals"]/ul/li[1]/strong/span[1]')->item(0)->textContent;
        $totalDefaultRevenue = ltrim(strip_tags($totalsDefaults[0]['value']), '$');
        $total24HoursRevenue = ltrim(strip_tags($totals24Hours), '$');
        $this->assertEquals(100.00, $totalDefaultRevenue);
        $this->assertEquals(200.00, $total24HoursRevenue);
    }
}
