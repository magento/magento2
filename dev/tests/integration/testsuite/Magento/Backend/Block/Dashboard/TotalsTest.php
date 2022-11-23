<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\Dashboard;

use DOMDocument;
use DOMXPath;
use Magento\Backend\Model\Dashboard\Period;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
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
use Magento\TestFramework\TestCase\AbstractBackendController;

class TotalsTest extends AbstractBackendController
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = $this->_objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @magentoAppArea adminhtml
     */

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
        $this->updateOrderCreateDate($orderData->getIncrementId(), '-23 hours');
        $order2Data = $this->fixtures->get('order2');
        $this->updateOrderCreateDate($order2Data->getIncrementId(), '-1 hour');
        $layout = $this->_objectManager->get(LayoutInterface::class);
        $totalsDefaultBlock = $layout->createBlock(Totals::class);
        $totalsDefaults = $totalsDefaultBlock->getTotals();
        $totals24Hours = $this->callAjaxBlock();
        $this->assertEquals(100, $this->parseRevenue($totalsDefaults[0]['value']));
        $this->assertEquals(200, $this->parseRevenue($totals24Hours));
    }

    private function callAjaxBlock()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('block', 'totals');
        $this->getRequest()->setParam('period', Period::PERIOD_TODAY);
        $this->dispatch('backend/admin/dashboard/ajaxBlock/');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $actualHtml = $this->getResponse()->getBody();
        $dom = new DOMDocument();
        $dom->loadHTML($actualHtml);
        $xPath = '//*[@id="dashboard_diagram_totals"]/ul/li[1]/strong/span[1]';
        $totals24Hours = (new DOMXPath($dom))->query($xPath)->item(0)->textContent;
        return $totals24Hours;
    }

    private function parseRevenue($txt): float
    {
        return (float)preg_replace("/[^0-9.]/", '', strip_tags($txt));
    }

    private function updateOrderCreateDate($orderIncrementId, $modify)
    {
        $order2 = $this->_objectManager->get(Order::class);
        $order2->loadByIncrementId($orderIncrementId);
        $dateTime2 = new \DateTime('now');
        $order2->setCreatedAt($dateTime2->modify($modify)->format(DateTime::DATETIME_PHP_FORMAT));
        $order2->save();
    }
}
