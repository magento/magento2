<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderRepositoryTaxDetailsTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(TaxRateFixture::class, ['tax_country_id' => 'US', 'rate' => 10], 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            ['customer_tax_class_ids' => [3], 'product_tax_class_ids' => [2], 'tax_rate_ids' => ['$rate.id$']]
        ),
        DataFixture(ProductFixture::class, ['price' => 20], 'product'),

        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart1.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart1.id$'], 'order1'),

        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart2.id$', 'product_id' => '$product.id$', 'qty' => 3]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart2.id$'], 'order2'),
    ]
    public function testGetListReturnsTaxDetailsOfEachOrder(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $orderIds = [
            (int)$this->fixtures->get('order1')->getEntityId(),
            (int)$this->fixtures->get('order2')->getEntityId(),
        ];
        $searchCriteria = $objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter(OrderInterface::ENTITY_ID, $orderIds, 'in')
            ->create();

        $orders = $objectManager->create(OrderRepositoryInterface::class)->getList($searchCriteria)->getItems();

        $this->assertCount(2, $orders);
        $taxManagement = $objectManager->get(OrderTaxManagementInterface::class);
        foreach ($orders as $order) {
            $expected = $taxManagement->getOrderTaxDetails($order->getEntityId());
            $extensionAttributes = $order->getExtensionAttributes();
            $this->assertNotEmpty($extensionAttributes->getAppliedTaxes());
            $this->assertEquals(
                $this->appliedTaxesToArray($expected->getAppliedTaxes()),
                $this->appliedTaxesToArray($extensionAttributes->getAppliedTaxes())
            );
            $this->assertEquals(
                $this->itemsToArray($expected->getItems()),
                $this->itemsToArray($extensionAttributes->getItemAppliedTaxes())
            );
        }
    }

    /**
     * @param OrderTaxDetailsAppliedTaxInterface[] $appliedTaxes
     * @return array
     */
    private function appliedTaxesToArray(array $appliedTaxes): array
    {
        return array_map(
            static fn (OrderTaxDetailsAppliedTaxInterface $tax) => [
                $tax->getCode(),
                $tax->getPercent(),
                (float)$tax->getAmount(),
                (float)$tax->getBaseAmount(),
            ],
            array_values($appliedTaxes)
        );
    }

    /**
     * @param OrderTaxDetailsItemInterface[] $items
     * @return array
     */
    private function itemsToArray(array $items): array
    {
        return array_map(
            fn (OrderTaxDetailsItemInterface $item) => [
                $item->getType(),
                $item->getItemId(),
                $item->getAssociatedItemId(),
                $this->appliedTaxesToArray($item->getAppliedTaxes()),
            ],
            $items
        );
    }
}
