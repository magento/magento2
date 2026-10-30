<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Ui\Component\Listing;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\Area;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as GridDataProvider;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for the admin Sales Order grid keyword search.
 *
 * Resolves the grid's DataProvider the same way the admin grid does — via the "SalesOrderGridDataProvider"
 * virtual type registered in Magento_Sales's adminhtml di.xml — rather than instantiating
 * Magento\Sales\Ui\Component\Listing\FulltextFilter directly, so this exercises the real
 * FilterPool -> Reporting -> DataProvider chain end to end.
 *
 * DB isolation is disabled below because MySQL/InnoDB only merges new rows into a FULLTEXT index's
 * auxiliary tables on COMMIT. Running this test inside the usual always-rolled-back test transaction would
 * mean MATCH...AGAINST never sees the orders created here, even though they're genuinely in the table (a
 * plain LIKE query would still find them, since it reads live table data).
 *
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextFilterTest extends TestCase
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

    /**
     * @param string $searchTemplate
     * @param string[] $expectedOrderKeys
     * @dataProvider searchDataProvider
     */
    #[
        AppArea(Area::AREA_ADMINHTML),
        DataFixture(ProductFixture::class, as: 'product'),

        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart1.id$', 'product_id' => '$product.id$']),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart1.id$', 'address' => ['firstname' => 'Will', 'lastname' => 'Johnson']]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart1.id$'], 'order1'),

        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart2.id$', 'product_id' => '$product.id$']),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart2.id$', 'address' => ['firstname' => 'Zq', 'lastname' => 'Roberts']]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart2.id$'], 'order2'),

        DataProvider('searchDataProvider'),
    ]
    public function testKeywordSearchOnOrderGrid(string $searchTemplate, array $expectedOrderKeys): void
    {
        /** @var OrderInterface $order1 */
        $order1 = $this->fixtures->get('order1');
        /** @var OrderInterface $order2 */
        $order2 = $this->fixtures->get('order2');
        $orders = ['order1' => $order1, 'order2' => $order2];

        $search = strtr(
            $searchTemplate,
            [
                '{order1_increment_id}' => $order1->getIncrementId(),
                '{order2_increment_id}' => $order2->getIncrementId(),
                '{order1_firstname}' => $order1->getBillingAddress()->getFirstname(),
                '{order2_firstname}' => $order2->getBillingAddress()->getFirstname(),
            ]
        );

        $objectManager = Bootstrap::getObjectManager();
        /** @var GridDataProvider $dataProvider */
        $dataProvider = $objectManager->create(
            'SalesOrderGridDataProvider',
            [
                'name' => 'sales_order_grid_data_source',
                'primaryFieldName' => 'main_table.entity_id',
                'requestFieldName' => 'id',
            ]
        );
        $filter = $objectManager->create(
            Filter::class,
            ['data' => ['field' => 'fulltext', 'condition_type' => 'fulltext', 'value' => $search]]
        );
        $dataProvider->addFilter($filter);

        $actualIncrementIds = array_map(
            fn ($item) => $item->getData('increment_id'),
            $dataProvider->getSearchResult()->getItems()
        );
        $expectedIncrementIds = array_map(
            fn (string $key) => $orders[$key]->getIncrementId(),
            $expectedOrderKeys
        );

        self::assertEqualsCanonicalizing($expectedIncrementIds, $actualIncrementIds);
    }

    public static function searchDataProvider(): array
    {
        return [
            'multiple increment IDs, space separated' => [
                '{order1_increment_id} {order2_increment_id}',
                ['order1', 'order2'],
            ],
            'multiple increment IDs, comma separated' => [
                '{order1_increment_id},{order2_increment_id}',
                ['order1', 'order2'],
            ],
            'stopword search only matches the order with that name' => [
                '{order1_firstname}',
                ['order1'],
            ],
            'short word search only matches the order with that name' => [
                '{order2_firstname}',
                ['order2'],
            ],
            'mixed stopword and increment ID search matches both orders' => [
                '{order1_firstname} {order2_increment_id}',
                ['order1', 'order2'],
            ],
        ];
    }
}
