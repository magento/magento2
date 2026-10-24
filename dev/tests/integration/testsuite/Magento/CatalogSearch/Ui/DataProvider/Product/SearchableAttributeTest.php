<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[DbIsolation(true)]
class SearchableAttributeTest extends TestCase
{
    #[DataFixture(AttributeFixture::class, ['is_searchable' => true], 'attribute')]
    #[DataFixture(
        ProductFixture::class,
        [
            'name' => 'Erika Running Short',
            'custom_attributes' => [['attribute_code' => '$attribute.attribute_code$', 'value' => 'Saffron38587']],
        ],
        'product'
    )]
    public function testWordsAcrossSearchableAttributes(): void
    {
        $this->assertProductFound('Erika Saffron38587');
    }

    #[DbIsolation(false)]
    #[DataFixture(StoreFixture::class, as: 'store')]
    #[DataFixture(ProductFixture::class, ['name' => 'Erika Running Short'], 'product')]
    #[DataFixture(
        ProductFixture::class,
        ['sku' => '$product.sku$', 'name' => 'Storeword38587 Running Short', '_update' => true],
        scope: 'store'
    )]
    public function testWordsAcrossStoreValues(): void
    {
        $this->assertProductFound('Erika Storeword38587');
    }

    private function assertProductFound(string $query): void
    {
        $productId = (int)DataFixtureStorageManager::getStorage()->get('product')->getId();
        $collection = Bootstrap::getObjectManager()->create(ProductCollection::class);
        $collection->setStoreId(0)->addIdFilter([$productId]);
        Bootstrap::getObjectManager()->create(
            AddFulltextFilterToCollection::class,
            ['searchCollection' => Bootstrap::getObjectManager()->create(SearchCollection::class)]
        )
            ->addFilter($collection, 'fulltext', ['fulltext' => $query]);

        self::assertSame([$productId], array_map('intval', $collection->getAllIds()));
    }
}
