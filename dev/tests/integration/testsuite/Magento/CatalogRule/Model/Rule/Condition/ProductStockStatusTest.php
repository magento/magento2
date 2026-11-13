<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[
    AppIsolation(true),
    DbIsolation(true)
]
class ProductStockStatusTest extends TestCase
{
    private const IN_STOCK = '1';
    private const OUT_OF_STOCK = '0';

    #[
        DataFixture(ProductFixture::class, as: 'product')
    ]
    public function testInStockProductWithoutStoredAttributeValueMatchesInStockCondition(): void
    {
        $productId = $this->deleteStoredStockStatus();

        $this->assertTrue($this->isMatchedBy($productId, '==', self::IN_STOCK));
        $this->assertFalse($this->isMatchedBy($productId, '==', self::OUT_OF_STOCK));
        $this->assertFalse($this->isMatchedBy($productId, '!=', self::IN_STOCK));
    }

    #[
        DataFixture(
            ProductFixture::class,
            ['extension_attributes' => ['stock_item' => ['is_in_stock' => false, 'qty' => 0]]],
            as: 'product'
        )
    ]
    public function testOutOfStockProductMatchesOutOfStockCondition(): void
    {
        $productId = (int)DataFixtureStorageManager::getStorage()->get('product')->getId();

        $this->assertTrue($this->isMatchedBy($productId, '==', self::OUT_OF_STOCK));
        $this->assertFalse($this->isMatchedBy($productId, '==', self::IN_STOCK));
    }

    private function deleteStoredStockStatus(): int
    {
        $productId = (int)DataFixtureStorageManager::getStorage()->get('product')->getId();
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $connection->delete(
            $connection->getTableName('catalog_product_entity_int'),
            [
                'entity_id = ?' => $productId,
                'attribute_id = ?' => $connection->fetchOne(
                    $connection->select()
                        ->from($connection->getTableName('eav_attribute'), 'attribute_id')
                        ->where('attribute_code = ?', 'quantity_and_stock_status')
                        ->where('entity_type_id = ?', 4)
                )
            ]
        );

        return $productId;
    }

    private function isMatchedBy(int $productId, string $operator, string $value): bool
    {
        $objectManager = Bootstrap::getObjectManager();
        $websiteId = (int)$objectManager->get(StoreManagerInterface::class)->getDefaultStoreView()->getWebsiteId();

        /** @var Rule $rule */
        $rule = $objectManager->get(RuleFactory::class)->create();
        $rule->loadPost([
            'name' => 'Stock status rule',
            'is_active' => 1,
            'website_ids' => [$websiteId],
            'customer_group_ids' => [0],
            'simple_action' => 'by_percent',
            'discount_amount' => 10,
            'conditions' => [
                '1' => ['type' => Combine::class, 'aggregator' => 'all', 'value' => 1],
                '1--1' => [
                    'type' => Product::class,
                    'attribute' => 'quantity_and_stock_status',
                    'operator' => $operator,
                    'value' => $value
                ]
            ]
        ]);
        $rule->setProductsFilter([$productId]);

        $matches = $rule->getMatchingProductIds();

        return !empty($matches[$productId][$websiteId]);
    }
}
