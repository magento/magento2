<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Compare\Item;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection
     */
    protected $collection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->collection = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection::class
        );
    }

    /**
     * Checks if join set compare list id to null if visitor id is empty/null.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testJoinTable()
    {
        $this->collection->setVisitorId(0);
        $fromParts = $this->collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);

        self::assertArrayHasKey('t_compare', $fromParts);
        $joinCondition = $fromParts['t_compare']['joinCondition'];

        self::assertStringContainsString('t_compare.list_id IS NULL', $joinCondition);
        self::assertStringContainsString('t_compare.customer_id IS NULL', $joinCondition);
        self::assertStringContainsString("t_compare.visitor_id = '0'", $joinCondition);
    }

    /**
     * Verifies that getComparableAttributes() applies the correct store-specific label even when the shared
     * EAV config object already has the attribute cached with a label from a different store. This simulates
     * the scenario where minicart rendering triggers the EAV preload cache before the compare page runs, causing
     * the preload (keyed on websiteId=0) to carry a poisoned store_label into subsequent store-view requests.
     */
    #[
        AppArea('frontend'),
        DbIsolation(true),
        DataFixtureBeforeTransaction(
            StoreFixture::class,
            ['code' => 'test_fr_store', 'name' => 'French Store'],
            'fr_store'
        ),
        DataFixture(ProductFixture::class, ['sku' => 'simple_compare_label_test'], 'product'),
    ]
    public function testGetComparableAttributesUsesCorrectStoreLabelWhenEavConfigPoisoned(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $storage = DataFixtureStorageManager::getStorage();

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $frStore = $storeManager->getStore($storage->get('fr_store')->getCode());
        $product = $storage->get('product');

        $connection = $objectManager->get(ResourceConnection::class)->getConnection();

        $attributeId = (int)$connection->fetchOne(
            $connection->select()
                ->from($connection->getTableName('eav_attribute'), 'attribute_id')
                ->where('attribute_code = ?', 'short_description')
        );
        $this->assertGreaterThan(0, $attributeId);

        // Register store-specific labels for short_description.
        // Default store keeps "Short Description"; the French store gets its own translation.
        $defaultStoreLabel = 'Short Description (Default)';
        $frStoreLabel = 'Description courte (FR)';
        $connection->insert(
            $connection->getTableName('eav_attribute_label'),
            ['attribute_id' => $attributeId, 'store_id' => 1, 'value' => $defaultStoreLabel]
        );
        $connection->insert(
            $connection->getTableName('eav_attribute_label'),
            ['attribute_id' => $attributeId, 'store_id' => (int)$frStore->getId(), 'value' => $frStoreLabel]
        );

        // Add the product to a visitor compare list so _getAttributeSetIds() returns a non-empty result.
        $visitorId = 99991;
        $connection->insert(
            $connection->getTableName('catalog_compare_item'),
            ['visitor_id' => $visitorId, 'product_id' => (int)$product->getId(), 'store_id' => (int)$frStore->getId()]
        );

        // Simulate the poisoned EAV preload cache state.
        // The preload (triggered by minicart when cart is non-empty) caches short_description with the default
        // store's label. On the next request for the French store's compare page, getAttribute() returns the
        // cached attribute which carries the wrong store_label.
        /** @var EavConfig $eavConfig */
        $eavConfig = $objectManager->get(EavConfig::class);
        $storeManager->setCurrentStore($frStore->getId());
        $eavConfig->getAttribute(Product::ENTITY, 'short_description')
            ->setData('store_label', $defaultStoreLabel);

        /** @var Collection $collection */
        $collection = $objectManager->create(Collection::class);
        $collection->setStoreId((int)$frStore->getId());
        $collection->setVisitorId($visitorId);

        $comparableAttributes = $collection->getComparableAttributes();

        $this->assertArrayHasKey('short_description', $comparableAttributes);
        $this->assertSame(
            $frStoreLabel,
            $comparableAttributes['short_description']->getStoreLabel(),
            'getComparableAttributes() must return the store-specific label from DB, not the wrong cached label'
        );
    }
}
