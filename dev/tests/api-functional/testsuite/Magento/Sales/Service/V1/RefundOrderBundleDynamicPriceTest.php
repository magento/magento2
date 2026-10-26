<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Partial refunds of a dynamic-price bundle record how many bundles were actually refunded.
 *
 * @see https://github.com/magento/magento2/issues/39060
 */
class RefundOrderBundleDynamicPriceTest extends WebapiAbstract
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

    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 20, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product1'
        ),
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product2'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['price_type' => Price::PRICE_TYPE_DYNAMIC, '_options' => ['$opt1$', '$opt2$']],
            'bundle'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 2
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testPartialRefundOfBundleItemsRefundsOnlyCompleteBundles(): void
    {
        $order = $this->fixtures->get('order');
        [$bundleItemId, $product1ItemId, $product2ItemId] = $this->getItemIds($order);

        $creditmemoId = $this->refund($order, [$product1ItemId => 1]);
        $items = $this->getOrderItems($order);
        $this->assertEquals(0, $items[$bundleItemId]->getQtyRefunded(), 'One item of a bundle is not a bundle.');
        $this->assertEquals(1, $items[$product1ItemId]->getQtyRefunded());
        $this->assertEquals(0, $items[$product2ItemId]->getQtyRefunded());
        $this->assertContains(
            $product1ItemId,
            $this->getCreditmemoOrderItemIds($creditmemoId),
            'The credit memo lists the refunded bundle item.'
        );

        $this->refund($order, [$product2ItemId => 1]);
        $items = $this->getOrderItems($order);
        $this->assertEquals(1, $items[$bundleItemId]->getQtyRefunded(), 'Both items of one bundle make one bundle.');
        $this->assertEquals(1, $items[$product1ItemId]->getQtyRefunded());
        $this->assertEquals(1, $items[$product2ItemId]->getQtyRefunded());
    }

    /**
     * Refund the given order item quantities and return the credit memo id
     *
     * @param OrderInterface $order
     * @param array $qtys order item id => quantity
     * @return int
     */
    private function refund(OrderInterface $order, array $qtys): int
    {
        $items = [];
        foreach ($qtys as $orderItemId => $qty) {
            $items[] = ['order_item_id' => $orderItemId, 'qty' => $qty];
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $order->getEntityId() . '/refund',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_REFUND_ORDER_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_REFUND_ORDER_NAME . 'execute',
            ],
        ];

        return (int)$this->_webApiCall($serviceInfo, ['orderId' => $order->getEntityId(), 'items' => $items]);
    }

    /**
     * Get the bundle item id and its two children's item ids, in selection order
     *
     * @param OrderInterface $order
     * @return int[]
     */
    private function getItemIds(OrderInterface $order): array
    {
        $bundleItemId = null;
        $childIds = [];
        foreach ($this->getOrderItems($order) as $item) {
            if ($item->getParentItemId()) {
                $childIds[$item->getSku()] = (int)$item->getItemId();
            } else {
                $bundleItemId = (int)$item->getItemId();
            }
        }

        return [
            $bundleItemId,
            $childIds[$this->fixtures->get('product1')->getSku()],
            $childIds[$this->fixtures->get('product2')->getSku()],
        ];
    }

    /**
     * Get the order's items keyed by item id, freshly loaded
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getOrderItems(OrderInterface $order): array
    {
        $repository = Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class);
        $items = [];
        foreach ($repository->get($order->getEntityId())->getItems() as $item) {
            $items[(int)$item->getItemId()] = $item;
        }

        return $items;
    }

    /**
     * Get the order item ids listed on a credit memo
     *
     * @param int $creditmemoId
     * @return int[]
     */
    private function getCreditmemoOrderItemIds(int $creditmemoId): array
    {
        $creditmemo = Bootstrap::getObjectManager()->create(CreditmemoRepositoryInterface::class)->get($creditmemoId);
        $ids = [];
        foreach ($creditmemo->getItems() as $item) {
            $ids[] = (int)$item->getOrderItemId();
        }

        return $ids;
    }
}
