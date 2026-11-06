<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * A shipping refund entered including tax is refunded exactly, without a one-cent rounding difference.
 *
 * @see https://github.com/magento/magento2/issues/39644
 */
class RefundShippingInclTaxRoundingTest extends WebapiAbstract
{
    private const SERVICE_REFUND_ORDER_NAME = 'salesRefundOrderV1';

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @param float $shippingRefund shipping amount to refund, including tax
     * @return void
     */
    #[
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/sales_display/shipping', '2', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        Config('carriers/flatrate/price', '149', 'store', 'default'),
        DataFixture(TaxRateFixture::class, ['rate' => 19, 'tax_postcode' => '*'], 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            ['tax_rate_ids' => ['$rate.id$'], 'product_tax_class_ids' => [2], 'customer_tax_class_ids' => [3]]
        ),
        DataFixture(ProductFixture::class, ['price' => 10], 'product'),
    ]
    public function testShippingRefundInclTaxIsExact(): void
    {
        $order = $this->placeOrder((int)$this->fixtures->get('product')->getId());
        $this->assertEqualsWithDelta(177.31, (float)$order->getShippingInclTax(), 0.001, 'Precondition: 149 + 19%.');

        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = ['order_item_id' => $item->getItemId(), 'qty' => 0];
        }
        $creditmemoId = (int)$this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => '/V1/order/' . $order->getEntityId() . '/refund',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
                'soap' => [
                    'service' => self::SERVICE_REFUND_ORDER_NAME,
                    'serviceVersion' => 'V1',
                    'operation' => self::SERVICE_REFUND_ORDER_NAME . 'execute',
                ],
            ],
            [
                'orderId' => $order->getEntityId(),
                'items' => $items,
                'arguments' => ['shipping_amount' => 75, 'adjustment_positive' => 0, 'adjustment_negative' => 0],
            ]
        );

        $creditmemo = Bootstrap::getObjectManager()->create(CreditmemoRepositoryInterface::class)->get($creditmemoId);
        $this->assertEqualsWithDelta(75.00, (float)$creditmemo->getGrandTotal(), 0.001, 'Grand total.');
        $this->assertEqualsWithDelta(
            75.00,
            (float)$creditmemo->getShippingAmount() + (float)$creditmemo->getShippingTaxAmount(),
            0.001,
            'Shipping plus shipping tax.'
        );
    }

    /**
     * Places a guest order with flat-rate shipping and invoices it.
     *
     * Built here, not with data fixture attributes: those are applied before the config fixtures in API-functional
     * tests, so the cart would be quoted with the default flat rate and no shipping tax.
     *
     * @param int $productId
     * @return OrderInterface
     */
    private function placeOrder(int $productId): OrderInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $cartId = (int)$objectManager->get(GuestCartFixture::class)->apply([])->getId();
        $objectManager->get(SetGuestEmailFixture::class)->apply(['cart_id' => $cartId]);
        $objectManager->get(AddProductToCartFixture::class)->apply(['cart_id' => $cartId, 'product_id' => $productId]);
        $objectManager->get(SetBillingAddressFixture::class)->apply(['cart_id' => $cartId]);
        $objectManager->get(SetShippingAddressFixture::class)->apply(['cart_id' => $cartId]);
        $objectManager->get(SetDeliveryMethodFixture::class)->apply(
            ['cart_id' => $cartId, 'carrier_code' => 'flatrate', 'method_code' => 'flatrate']
        );
        $objectManager->get(SetPaymentMethodFixture::class)->apply(['cart_id' => $cartId, 'method' => 'checkmo']);
        $order = $objectManager->get(PlaceOrderFixture::class)->apply(['cart_id' => $cartId]);
        $objectManager->get(InvoiceFixture::class)->apply(['order_id' => $order->getId()]);

        return $objectManager->create(OrderRepositoryInterface::class)->get((int)$order->getId());
    }
}
