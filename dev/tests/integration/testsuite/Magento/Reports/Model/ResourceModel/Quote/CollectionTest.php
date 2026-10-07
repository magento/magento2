<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Model\ResourceModel\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * Loaded items must not be registered in the entity snapshot.
     *
     * The plain quote collection is used as a positive control: it registers a snapshot for the same quote.
     *
     * @return void
     */
    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]),
    ]
    public function testLoadedItemsAreNotRegisteredInSnapshot(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $quoteId = (int) $objectManager->get(DataFixtureStorageManager::class)
            ->getStorage()
            ->get('quote')
            ->getId();

        // Positive control: the plain quote collection registers a snapshot for the loaded quote.
        $controlSnapshot = $objectManager->create(Snapshot::class);
        $controlCollection = $objectManager->create(
            QuoteCollection::class,
            ['entitySnapshot' => $controlSnapshot]
        );
        $controlCollection->addFieldToFilter('main_table.entity_id', $quoteId);
        $controlItem = $controlCollection->getFirstItem();

        $this->assertSame($quoteId, (int) $controlItem->getId(), 'Fixture quote was not loaded.');
        $this->assertNotEmpty(
            $controlSnapshot->getSnapshotData($controlItem),
            'The plain quote collection is expected to register a snapshot for the loaded quote.'
        );

        // Reports collection must skip snapshot registration for the same quote.
        $reportsSnapshot = $objectManager->create(Snapshot::class);
        $reportsCollection = $objectManager->create(
            Collection::class,
            ['entitySnapshot' => $reportsSnapshot]
        );
        $reportsCollection->addFieldToFilter('main_table.entity_id', $quoteId);
        $reportsItem = $reportsCollection->getFirstItem();

        $this->assertSame($quoteId, (int) $reportsItem->getId(), 'Fixture quote was not loaded.');
        $this->assertEmpty(
            $reportsSnapshot->getSnapshotData($reportsItem),
            'The abandoned cart report collection must not register a snapshot for loaded items.'
        );
    }
}
