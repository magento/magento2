<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verify single-attribute saves respect website boundaries.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('adminhtml')]
#[DbIsolation(true)]
#[DataFixtureBeforeTransaction(WebsiteFixture::class, as: 'selected_website')]
#[DataFixtureBeforeTransaction(GroupFixture::class, ['website_id' => '$selected_website.id$'], 'selected_group')]
#[DataFixtureBeforeTransaction(StoreFixture::class, ['store_group_id' => '$selected_group.id$'], 'selected_store')]
#[DataFixtureBeforeTransaction(StoreFixture::class, ['store_group_id' => '$selected_group.id$'], 'sibling_store')]
#[DataFixtureBeforeTransaction(StoreFixture::class, as: 'other_store')]
#[DataFixture(
    AttributeFixture::class,
    ['scope' => 'website', 'backend_type' => 'int', 'frontend_input' => 'boolean', 'source_model' => Boolean::class],
    'attribute'
)]
#[DataFixture(ProductFixture::class, ['website_ids' => ['$selected_website.id$']], 'product')]
class AttributeUpdateTest extends TestCase
{
    /**
     * Updating or inserting a website value must affect siblings but no other website or admin row.
     *
     * @param bool $existingRow
     * @param bool $existingSiblingRow
     * @return void
     */
    #[DataProvider('existingRowProvider')]
    public function testWebsiteAttributeUpdate(bool $existingRow, bool $existingSiblingRow): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $selectedStoreId = (int) $fixtures->get('selected_store')->getId();
        $siblingStoreId = (int) $fixtures->get('sibling_store')->getId();
        $otherStoreId = (int) $fixtures->get('other_store')->getId();
        $resource = $objectManager->get(Product::class);
        $attributeCode = $fixtures->get('attribute')->getAttributeCode();
        $attribute = $resource->getAttribute($attributeCode);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->get(ProductRepositoryInterface::class)->get(
            $fixtures->get('product')->getSku(),
            false,
            $selectedStoreId,
            true
        );
        $connection = $resource->getConnection();
        $table = $attribute->getBackend()->getTable();
        $linkField = $resource->getLinkField();
        $storeIds = [0, $otherStoreId];
        if ($existingRow) {
            $storeIds[] = $selectedStoreId;
        }
        if ($existingSiblingRow) {
            $storeIds[] = $siblingStoreId;
        }
        foreach ($storeIds as $storeId) {
            $connection->insertOnDuplicate(
                $table,
                [
                    $linkField => $product->getData($linkField),
                    'attribute_id' => $attribute->getId(),
                    'store_id' => $storeId,
                    'value' => 0,
                ],
                ['value']
            );
        }
        $select = $connection->select()->from($table, ['store_id', 'value'])
            ->where($linkField . ' = ?', $product->getData($linkField))
            ->where('attribute_id = ?', $attribute->getId())
            ->where('store_id IN (?)', [0, $selectedStoreId, $siblingStoreId, $otherStoreId]);
        $before = $connection->fetchPairs($select);
        self::assertSame($existingRow, array_key_exists($selectedStoreId, $before));
        self::assertSame($existingSiblingRow, array_key_exists($siblingStoreId, $before));
        $statusBefore = $connection->showTableStatus($table);

        $product->addAttributeUpdate($attributeCode, 1, $selectedStoreId);

        self::assertEquals(
            [0 => 0, $selectedStoreId => 1, $siblingStoreId => 1, $otherStoreId => 0],
            $connection->fetchPairs($select)
        );
        $statusAfter = $connection->showTableStatus($table);
        if ($existingRow && $existingSiblingRow) {
            self::assertSame((int) $statusBefore['Auto_increment'], (int) $statusAfter['Auto_increment']);
        }

        $product->addAttributeUpdate($attributeCode, 1, $siblingStoreId);

        $statusAfterRepeatedUpdate = $connection->showTableStatus($table);
        self::assertSame((int) $statusAfter['Auto_increment'], (int) $statusAfterRepeatedUpdate['Auto_increment']);
    }

    /**
     * Exercise both the update optimization and the insertion path.
     *
     * @return array
     */
    public static function existingRowProvider(): array
    {
        return [
            'all rows exist' => [true, true],
            'missing sibling row' => [true, false],
            'missing selected row' => [false, true],
        ];
    }
}
