<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[DbIsolation(true)]
class AddFulltextFilterToCollectionTest extends TestCase
{
    #[DataFixture(
        ProductFixture::class,
        ['name' => 'Erika Running Short 50%_', 'sku' => 'erika-prefix-38587', 'status' => 2, 'visibility' => 1],
        'product'
    )]
    #[DataFixture(ProductFixture::class, ['name' => 'Wildcard Control 50XY'], 'control')]
    #[DataProvider('queryProvider')]
    public function testKeywordSearch(string $query, bool $matches): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $productId = (int)$fixtures->get('product')->getId();
        $collection = Bootstrap::getObjectManager()->create(ProductCollection::class);
        $collection->addIdFilter([$productId, (int)$fixtures->get('control')->getId()]);
        Bootstrap::getObjectManager()->create(
            AddFulltextFilterToCollection::class,
            ['searchCollection' => Bootstrap::getObjectManager()->create(SearchCollection::class)]
        )
            ->addFilter($collection, 'fulltext', ['fulltext' => $query]);

        self::assertSame($matches ? [$productId] : [], array_map('intval', $collection->getAllIds()));
    }

    public static function queryProvider(): array
    {
        return [
            'separated words' => ['Erika Short', true],
            'reverse order' => ['Short Erika', true],
            'contiguous phrase' => ['Erika Running', true],
            'case insensitive' => ['erika', true],
            'sku prefix' => ['erika-prefix', true],
            'name and sku' => ['Short erika-prefix', true],
            'whitespace runs' => [" \tErika\n Short  ", true],
            'duplicate words' => ['Erika Erika Short', true],
            'missing word' => ['Erika Absent38587', false],
            'missing first word' => ['Absent38587 Erika', false],
            'zero matches' => ['Absent38587', false],
            'literal percent' => ['%', true],
            'literal underscore' => ['_', true],
        ];
    }

    #[DataFixture(ProductFixture::class, ['name' => 'Erika Running Short', 'sku' => 'z-erika-38587'], 'last')]
    #[DataFixture(ProductFixture::class, ['name' => 'Erika Walking Short', 'sku' => 'a-erika-38587'], 'first')]
    public function testGridSortingAndPagination(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $collection = Bootstrap::getObjectManager()->create(ProductCollection::class);
        $collection->addIdFilter([$fixtures->get('first')->getId(), $fixtures->get('last')->getId()]);
        $collection->setOrder('sku', 'ASC')->setPageSize(1)->setCurPage(2);
        Bootstrap::getObjectManager()->create(
            AddFulltextFilterToCollection::class,
            ['searchCollection' => Bootstrap::getObjectManager()->create(SearchCollection::class)]
        )
            ->addFilter($collection, 'fulltext', ['fulltext' => 'Erika Short']);

        self::assertSame(2, (int)$collection->getSize());
        self::assertSame(
            [(int)$fixtures->get('last')->getId()],
            array_map('intval', array_keys($collection->getItems()))
        );
    }
}
