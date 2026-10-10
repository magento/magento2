<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Option;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[
    DbIsolation(true),
    DataFixture(
        ProductFixture::class,
        [
            'options' => [
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                    'title' => 'Field option',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
                [
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'title' => 'Select option',
                    'values' => [
                        ['title' => 'Value 1', 'price' => 5, 'price_type' => 'fixed', 'sku' => 'value-1'],
                    ],
                ],
            ],
        ],
        as: 'product'
    )
]
class StorePriceUseDefaultTest extends TestCase
{
    private const STORE_ID = 1;

    #[Config('catalog/price/scope', '1', 'store')]
    public function testOptionPriceFallsBackToDefault(): void
    {
        $option = $this->getOption(ProductCustomOptionInterface::OPTION_TYPE_FIELD);

        $this->saveOption($option, ['price' => 20, 'price_type' => 'fixed']);
        $this->assertSame([self::STORE_ID => 20.0], $this->getStorePrices('catalog_product_option_price', $option));

        $this->saveOption($option, ['price' => 10, 'price_type' => 'fixed', 'is_use_default_price' => 1]);
        $this->assertSame([], $this->getStorePrices('catalog_product_option_price', $option));
        $this->assertSame(
            10.0,
            $this->getDefaultPrice('catalog_product_option_price', 'option_id', (int)$option->getId())
        );
    }

    #[Config('catalog/price/scope', '1', 'store')]
    public function testOptionValuePriceFallsBackToDefault(): void
    {
        $option = $this->getOption(ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN);
        $value = current($option->getValues());

        $this->saveValue($value, ['price' => 8, 'price_type' => 'fixed']);
        $this->assertSame([self::STORE_ID => 8.0], $this->getStorePrices('catalog_product_option_type_price', $value));

        $this->saveValue($value, ['price' => 5, 'price_type' => 'fixed', 'is_use_default_price' => 1]);
        $this->assertSame([], $this->getStorePrices('catalog_product_option_type_price', $value));
        $this->assertSame(
            5.0,
            $this->getDefaultPrice('catalog_product_option_type_price', 'option_type_id', (int)$value->getId())
        );
    }

    private function getOption(string $type): ProductOption
    {
        $sku = DataFixtureStorageManager::getStorage()->get('product')->getSku();
        $product = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class)->get($sku, true, 0, true);
        foreach ($product->getOptions() as $option) {
            if ($option->getType() === $type) {
                return $option;
            }
        }
        $this->fail('Option of type ' . $type . ' was not created');
    }

    private function saveOption(ProductOption $option, array $data): void
    {
        $option->addData($data)->setStoreId(self::STORE_ID)->save();
    }

    private function saveValue(ProductOption\Value $value, array $data): void
    {
        $value->addData($data)->setStoreId(self::STORE_ID)->save();
    }

    private function getStorePrices(string $table, \Magento\Framework\DataObject $item): array
    {
        $idColumn = $table === 'catalog_product_option_price' ? 'option_id' : 'option_type_id';
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $rows = $connection->fetchPairs(
            $connection->select()
                ->from($connection->getTableName($table), ['store_id', 'price'])
                ->where($idColumn . ' = ?', (int)$item->getId())
                ->where('store_id <> ?', Store::DEFAULT_STORE_ID)
        );

        return array_map('floatval', $rows);
    }

    private function getDefaultPrice(string $table, string $idColumn, int $id): float
    {
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();

        return (float)$connection->fetchOne(
            $connection->select()
                ->from($connection->getTableName($table), 'price')
                ->where($idColumn . ' = ?', $id)
                ->where('store_id = ?', Store::DEFAULT_STORE_ID)
        );
    }
}
