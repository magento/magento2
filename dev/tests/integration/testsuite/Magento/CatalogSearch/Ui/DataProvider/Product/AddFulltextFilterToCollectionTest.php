<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for the admin product grid keyword filter
 */
#[
    DataFixture(
        ProductFixture::class,
        [
            'sku' => 'gridsearch-alpha',
            'name' => 'Alpha Grid Product',
            'description' => 'Alpha description text',
        ]
    ),
    DataFixture(
        ProductFixture::class,
        [
            'sku' => 'gridsearch-beta',
            'name' => 'Beta Grid Product',
            'description' => 'Beta unicorndesc text',
        ]
    ),
    DataFixture(
        ProductFixture::class,
        [
            'sku' => 'unrelated-gamma',
            'name' => 'Gamma Product',
            'description' => 'Gamma description text',
        ]
    ),
]
class AddFulltextFilterToCollectionTest extends TestCase
{
    /**
     * @var AddFulltextFilterToCollection
     */
    private $model;

    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(AddFulltextFilterToCollection::class);
        $this->collectionFactory = $objectManager->get(ProductCollectionFactory::class);
    }

    /**
     * @param string $keyword
     * @param array $expectedSkus
     */
    #[DataProvider('keywordDataProvider')]
    public function testKeywordMatchesTheSameProductsAsBefore(string $keyword, array $expectedSkus): void
    {
        $collection = $this->createFilteredCollection($keyword);
        $skus = $collection->getColumnValues('sku');
        sort($skus);

        $this->assertSame($expectedSkus, $skus);
        $this->assertSame(count($expectedSkus), $collection->getSize());
    }

    /**
     * @return array
     */
    public static function keywordDataProvider(): array
    {
        return [
            'sku fragment' => ['gridsearch-alpha', ['gridsearch-alpha']],
            'name fragment' => ['Beta Grid', ['gridsearch-beta']],
            'description fragment' => ['unicorndesc', ['gridsearch-beta']],
            'matches several products' => ['gridsearch-', ['gridsearch-alpha', 'gridsearch-beta']],
            'no match' => ['zzz-nothing-matches-zzz', []],
        ];
    }

    /**
     * A keyword hit by several searchable attributes of one product must not duplicate the grid row
     */
    public function testProductIsReturnedOnceWhenSeveralAttributesMatch(): void
    {
        $collection = $this->createFilteredCollection('Alpha');

        $this->assertSame(['gridsearch-alpha'], $collection->getColumnValues('sku'));
        $this->assertSame(1, $collection->getSize());
    }

    /**
     * The total count must stay correct while the collection itself is limited to one page
     */
    public function testPagingDoesNotAffectTheTotalCount(): void
    {
        $collection = $this->collectionFactory->create();
        $this->model->addFilter($collection, 'fulltext', ['fulltext' => 'gridsearch-']);
        $collection->setPageSize(1);
        $collection->setCurPage(1);

        $this->assertCount(1, $collection->getItems());
        $this->assertSame(2, $collection->getSize());
    }

    /**
     * @param string $keyword
     * @return ProductCollection
     */
    private function createFilteredCollection(string $keyword): ProductCollection
    {
        $collection = $this->collectionFactory->create();
        $this->model->addFilter($collection, 'fulltext', ['fulltext' => $keyword]);
        $collection->load();

        return $collection;
    }
}
