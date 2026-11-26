<?php
declare(strict_types=1);

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\DataObject;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for ACP2E-3658: Admin displays incorrect currency symbol when creating return.
 *
 * In a multi-website setup, the currency renderer must use the currency configured for the
 * row's store/website instead of always falling back to the system default currency.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencyTest extends TestCase
{
    /**
     * Store view code for the secondary website fixture.
     */
    private const SECOND_STORE_CODE = 'fixture_second_store';

    /**
     * End-to-end scenario covering ACP2E-3658.
     *
     * Runs first in this class: checkout PDF fixtures require a fresh StoreManager state. Later tests
     * that create and revert secondary websites via DataFixtureBeforeTransaction leave stale store
     * scope that breaks add-to-cart when this scenario runs after them.
     *
     * Creates a guest order on the secondary store with invoice and shipment via declarative
     * fixtures, then verifies the return product grid currency renderer uses the store EUR currency.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    #[Config('currency/options/default', 'EUR', 'store', self::SECOND_STORE_CODE)]
    #[Config('currency/options/allow', 'EUR', 'store', self::SECOND_STORE_CODE)]
    #[
        AppIsolation(true),
        DbIsolation(true),
        DataFixtureBeforeTransaction(
            WebsiteFixture::class,
            ['code' => 'test', 'name' => 'Test Website'],
            as: 'second_website'
        ),
        DataFixtureBeforeTransaction(
            StoreGroupFixture::class,
            [
                'code' => 'second_group',
                'name' => 'Second Store Group',
                'website_id' => '$second_website.id$',
            ],
            as: 'second_store_group'
        ),
        DataFixtureBeforeTransaction(
            StoreFixture::class,
            [
                'code' => self::SECOND_STORE_CODE,
                'name' => 'Fixture Second Store',
                'sort_order' => 10,
                'store_group_id' => '$second_store_group.id$',
            ],
            as: 'second_store'
        ),
        DataFixture(
            ProductFixture::class,
            ['website_ids' => ['$second_website.id$']],
            as: 'product'
        ),
        DataFixture(
            GuestCartFixture::class,
            ['reserved_order_id' => '200000001'],
            as: 'cart',
            scope: 'second_store'
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 2,
            ],
            scope: 'second_store'
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$'], scope: 'second_store'),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$'], scope: 'second_store'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$'], scope: 'second_store'),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$'], scope: 'second_store'),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$'], scope: 'second_store'),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order', 'second_store'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$'], 'shipment'),
    ]
    public function testReturnProductGridShowsEurForOrderOnSecondWebsite(): void
    {
        $order = DataFixtureStorageManager::getStorage()->get('order');

        $result = $this->renderPrice(
            ['index' => 'price', 'type' => 'currency'],
            ['price' => 100.00, 'store_id' => $order->getStoreId()]
        );

        $this->assertStringContainsString(
            '€',
            $result,
            'Return product grid must show EUR for an order placed on the EU website store'
        );
        $this->assertStringNotContainsString(
            '$',
            $result,
            'Return product grid must not show the system-default USD for an EU website order'
        );
    }

    /**
     * Main regression test for ACP2E-3658.
     *
     * When a grid row carries a store_id that belongs to a website configured with EUR,
     * the renderer must display prices in EUR, not in the system default (USD).
     *
     * Secondary website/store are created with DataFixtureBeforeTransaction so DB isolation can
     * remain enabled: Magento\Store\Test\Fixture\Store may perform DDL (sequences) outside the
     * test transaction, and revertible fixtures tear down website and store entities afterward.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    #[Config('currency/options/default', 'EUR', 'store', self::SECOND_STORE_CODE)]
    #[Config('currency/options/allow', 'EUR', 'store', self::SECOND_STORE_CODE)]
    #[
        AppIsolation(true),
        DbIsolation(true),
        DataFixtureBeforeTransaction(
            WebsiteFixture::class,
            ['code' => 'test', 'name' => 'Test Website'],
            as: 'second_website'
        ),
        DataFixtureBeforeTransaction(
            StoreGroupFixture::class,
            [
                'code' => 'second_group',
                'name' => 'Second Store Group',
                'website_id' => '$second_website.id$',
            ],
            as: 'second_store_group'
        ),
        DataFixtureBeforeTransaction(
            StoreFixture::class,
            [
                'code' => self::SECOND_STORE_CODE,
                'name' => 'Fixture Second Store',
                'sort_order' => 10,
                'store_group_id' => '$second_store_group.id$',
            ],
            as: 'second_store'
        ),
    ]
    public function testRenderUsesWebsiteCurrencyWhenStoreIdProvided(): void
    {
        $result = $this->renderPrice(
            ['index' => 'price', 'type' => 'currency'],
            ['price' => 100.00, 'store_id' => $this->getStoreIdByCode(self::SECOND_STORE_CODE)]
        );

        $this->assertStringContainsString('€', $result, 'Price should display in EUR for a store with EUR currency');
        $this->assertStringNotContainsString(
            '$',
            $result,
            'Price must not display in USD (the default system currency)'
        );
    }

    /**
     * An explicit currency_code on the column must take precedence over the store_id lookup.
     *
     * Verifies that the priority ordering in _getCurrencyCode() was not disturbed by the fix.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    #[
        AppIsolation(true),
        DbIsolation(true),
        DataFixtureBeforeTransaction(
            WebsiteFixture::class,
            ['code' => 'test', 'name' => 'Test Website'],
            as: 'second_website'
        ),
        DataFixtureBeforeTransaction(
            StoreGroupFixture::class,
            [
                'code' => 'second_group',
                'name' => 'Second Store Group',
                'website_id' => '$second_website.id$',
            ],
            as: 'second_store_group'
        ),
        DataFixtureBeforeTransaction(
            StoreFixture::class,
            [
                'code' => self::SECOND_STORE_CODE,
                'name' => 'Fixture Second Store',
                'sort_order' => 10,
                'store_group_id' => '$second_store_group.id$',
            ],
            as: 'second_store'
        ),
    ]
    public function testColumnCurrencyCodeTakesPrecedenceOverStoreId(): void
    {
        $result = $this->renderPrice(
            ['index' => 'price', 'type' => 'currency', 'currency_code' => 'GBP'],
            ['price' => 100.00, 'store_id' => $this->getStoreIdByCode(self::SECOND_STORE_CODE)]
        );

        $this->assertStringContainsString('£', $result, 'Column-level currency_code must override the store currency');
    }

    /**
     * When the row carries a currency field that maps to a column index, that value is used.
     *
     * Covers the second priority branch in _getCurrencyCode(): column->getCurrency() + row data.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    public function testRowCurrencyFieldIsUsedWhenColumnCurrencyIsSet(): void
    {
        $result = $this->renderPrice(
            ['index' => 'price', 'type' => 'currency', 'currency' => 'order_currency_code'],
            ['price' => 100.00, 'order_currency_code' => 'EUR']
        );

        $this->assertStringContainsString(
            '€',
            $result,
            'Currency from the row data field should be used when column->getCurrency() is set'
        );
    }

    /**
     * A non-existent store_id must not throw; the renderer falls back to the default currency.
     *
     * Covers the NoSuchEntityException catch block added in the fix.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    public function testRenderFallsBackToDefaultCurrencyForInvalidStoreId(): void
    {
        $result = $this->renderPrice(
            ['index' => 'price', 'type' => 'currency'],
            ['price' => 100.00, 'store_id' => 99999]
        );

        $this->assertStringContainsString(
            '$',
            $result,
            'An invalid store_id should not throw and should fall back to the default currency'
        );
    }

    /**
     * A row with a store_id from the default (USD) website must still render in USD.
     *
     * Verifies that the fix did not alter behaviour for stores that were already correct.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    public function testRenderUsesDefaultCurrencyForDefaultWebsiteStore(): void
    {
        $result = $this->renderPrice(
            ['index' => 'price', 'type' => 'currency'],
            ['price' => 100.00, 'store_id' => $this->getStoreIdByCode('default')]
        );

        $this->assertStringContainsString('$', $result, 'Default website store should still render prices in USD');
    }

    /**
     * When no store_id is set on the row, the renderer must fall back to the system default currency.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    public function testRenderFallsBackToDefaultCurrencyWithoutStoreId(): void
    {
        $result = $this->renderPrice(
            ['index' => 'price', 'type' => 'currency'],
            ['price' => 100.00]
        );

        $this->assertStringContainsString(
            '$',
            $result,
            'Price should display in USD (system default) when no store_id is set'
        );
    }

    /**
     * Render a grid currency column value for the given column and row data.
     *
     * @param array<string, mixed> $columnData
     * @param array<string, mixed> $rowData
     * @return string
     */
    private function renderPrice(array $columnData, array $rowData): string
    {
        $objectManager = Bootstrap::getObjectManager();
        $renderer = $objectManager->create(Currency::class);
        $column = $objectManager->create(Column::class, ['data' => $columnData]);
        $row = new DataObject($rowData);

        return $renderer->setColumn($column)->render($row);
    }

    /**
     * Resolve a store view ID by store code.
     *
     * @param string $storeCode
     * @return int
     */
    private function getStoreIdByCode(string $storeCode): int
    {
        return (int) Bootstrap::getObjectManager()
            ->get(StoreRepositoryInterface::class)
            ->get($storeCode)
            ->getId();
    }
}
