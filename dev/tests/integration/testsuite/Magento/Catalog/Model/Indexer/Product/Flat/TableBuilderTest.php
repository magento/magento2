<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Helper\Product\Flat\Indexer as FlatIndexerHelper;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class TableBuilderTest extends TestCase
{
    /**
     * Store-scope attribute value explicitly overridden with NULL must not fall back
     * to the default-scope value in the flat table built by full reindex.
     *
     * @return void
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        Config('catalog/frontend/flat_catalog_product', 1),
        Config('catalog/frontend/flat_catalog_product', 1, ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(StoreFixture::class, as: 'store2'),
        DataFixture(
            AttributeFixture::class,
            [
                ProductAttributeInterface::SCOPE => 'store',
                ProductAttributeInterface::USED_IN_PRODUCT_LISTING => true,
            ],
            as: 'attribute'
        ),
        DataFixture(ProductFixture::class, as: 'product_with_override'),
        DataFixture(ProductFixture::class, as: 'product_without_override'),
    ]
    public function testStoreScopeNullOverrideIsNotReplacedByDefaultValue(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $storeId = (int)$fixtures->get('store2')->getId();
        $attribute = $fixtures->get('attribute');
        $attributeCode = $attribute->getAttributeCode();
        $productWithOverrideId = (int)$fixtures->get('product_with_override')->getId();
        $productWithoutOverrideId = (int)$fixtures->get('product_without_override')->getId();

        /** @var ProductAction $productAction */
        $productAction = $objectManager->get(ProductAction::class);
        $productAction->updateAttributes(
            [$productWithOverrideId, $productWithoutOverrideId],
            [$attributeCode => 'default scope value'],
            Store::DEFAULT_STORE_ID
        );
        $productAction->updateAttributes([$productWithOverrideId], [$attributeCode => null], $storeId);

        $this->assertStoreValueRowExists($productWithOverrideId, (int)$attribute->getAttributeId(), $storeId);

        /** @var Processor $processor */
        $processor = $objectManager->get(Processor::class);
        $processor->reindexAll();

        $flatValues = $this->fetchFlatValues($attributeCode, $storeId);
        $this->assertArrayHasKey($productWithOverrideId, $flatValues);
        $this->assertNull(
            $flatValues[$productWithOverrideId],
            'Flat table must contain the store-scope NULL override, not the default-scope value'
        );
        $this->assertArrayHasKey($productWithoutOverrideId, $flatValues);
        $this->assertSame(
            'default scope value',
            $flatValues[$productWithoutOverrideId],
            'Flat table must fall back to the default-scope value when there is no store-scope row'
        );
    }

    /**
     * Assert precondition: a store-scope row with NULL value exists for the attribute.
     *
     * @param int $productId
     * @param int $attributeId
     * @param int $storeId
     * @return void
     */
    private function assertStoreValueRowExists(int $productId, int $attributeId, int $storeId): void
    {
        /** @var ProductResource $productResource */
        $productResource = Bootstrap::getObjectManager()->get(ProductResource::class);
        $connection = $productResource->getConnection();
        $select = $connection->select()
            ->from($productResource->getTable('catalog_product_entity_varchar'), ['value_id', 'value'])
            ->where('attribute_id = ?', $attributeId)
            ->where('store_id = ?', $storeId)
            ->where($productResource->getLinkField() . ' = ?', $productId);
        $row = $connection->fetchRow($select);

        $this->assertNotFalse($row, 'Store-scope attribute value row must exist');
        $this->assertNull($row['value'], 'Store-scope attribute value row must hold NULL value');
    }

    /**
     * Fetch attribute column values from the store flat table indexed by entity id.
     *
     * @param string $attributeCode
     * @param int $storeId
     * @return array
     */
    private function fetchFlatValues(string $attributeCode, int $storeId): array
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var FlatIndexerHelper $flatHelper */
        $flatHelper = $objectManager->get(FlatIndexerHelper::class);
        /** @var ProductResource $productResource */
        $productResource = $objectManager->get(ProductResource::class);
        $connection = $productResource->getConnection();
        $select = $connection->select()
            ->from($productResource->getTable($flatHelper->getFlatTableName($storeId)), ['entity_id', $attributeCode]);

        return $connection->fetchPairs($select);
    }
}
