<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Category\DataProvider;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider\Breadcrumbs;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class BreadcrumbsTest extends TestCase
{
    /**
     * @var CollectionFactory|Stub
     */
    private $collectionFactory;

    /**
     * @var int
     */
    private int $storeId = 1;

    /**
     * @var array<int, array<int, int>>
     */
    private array $loadedIds = [];

    /**
     * @var Breadcrumbs
     */
    private Breadcrumbs $breadcrumbs;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createStub(CollectionFactory::class);
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturnCallback(fn () => $this->storeId);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);
        $this->breadcrumbs = new Breadcrumbs($this->collectionFactory, new Uid(), $storeManager);
    }

    public function testPrefetchLoadsParentCategoriesOfAllPathsWithOneCollection(): void
    {
        $this->expectCollections([10 => true, 11 => true, 12 => true, 20 => true]);

        $this->breadcrumbs->prefetch(['1/2/10/11/12/13', '1/2/10/11/14', '1/2/20/21', '1/2/30']);

        $this->assertSame([10, 11, 12], array_column($this->breadcrumbs->getData('1/2/10/11/12/13'), 'category_id'));
        $this->assertSame([10, 11], array_column($this->breadcrumbs->getData('1/2/10/11/14'), 'category_id'));
        $this->assertSame([20], array_column($this->breadcrumbs->getData('1/2/20/21'), 'category_id'));
        $this->assertSame([], $this->breadcrumbs->getData('1/2/30'));
        $this->assertSame([[10, 11, 12, 20]], $this->loadedIds);
        $this->assertSame(
            [
                'category_id' => 11,
                'category_uid' => base64_encode('11'),
                'category_name' => 'Category 11',
                'category_level' => 3,
                'category_url_key' => 'category-11',
                'category_url_path' => 'parent/category-11',
            ],
            $this->breadcrumbs->getData('1/2/10/11/14')[1]
        );
    }

    public function testOrderDoesNotDependOnWhichBatchLoadedTheParents(): void
    {
        $this->expectCollections([10 => true, 20 => true, 30 => true]);

        $this->breadcrumbs->prefetch(['1/2/30/31']);
        $this->assertSame([20], array_column($this->breadcrumbs->getData('1/2/20/21'), 'category_id'));
        $this->breadcrumbs->prefetch(['1/2/30/20/10/40']);

        $this->assertSame([[30], [20], [10]], $this->loadedIds);
        $this->assertSame(
            [10, 20, 30],
            array_column($this->breadcrumbs->getData('1/2/30/20/10/40'), 'category_id')
        );
        $this->assertSame(
            [10, 20],
            array_column($this->breadcrumbs->getData('1/2/10/20/22'), 'category_id')
        );
    }

    public function testLaterPathsLoadOnlyCategoriesNotLoadedYet(): void
    {
        $this->expectCollections([10 => true, 11 => false, 12 => true]);

        $this->breadcrumbs->prefetch(['1/2/10/11/13']);
        $data = $this->breadcrumbs->getData('1/2/10/11/12/14');
        $this->assertSame([], $this->breadcrumbs->getData('1/2/11/15'));

        $this->assertSame([[10, 11], [12]], $this->loadedIds);
        $this->assertSame([10, 12], array_column($data, 'category_id'));
    }

    public function testPathsWithoutParentsRunNoQuery(): void
    {
        $this->expectCollections([]);

        $this->breadcrumbs->prefetch(['1/2/3', '1/2']);

        $this->assertSame([], $this->breadcrumbs->getData('1/2/3'));
        $this->assertSame([], $this->loadedIds);
    }

    public function testCategoriesAreLoadedPerStore(): void
    {
        $this->expectCollections([10 => true]);

        $this->breadcrumbs->getData('1/2/10/11');
        $this->storeId = 2;
        $this->breadcrumbs->getData('1/2/10/12');

        $this->assertSame([[10], [10]], $this->loadedIds);
    }

    public function testResetStateDropsLoadedCategories(): void
    {
        $this->expectCollections([10 => true]);

        $this->breadcrumbs->getData('1/2/10/11');
        $this->breadcrumbs->_resetState();
        $this->breadcrumbs->getData('1/2/10/11');

        $this->assertSame([[10], [10]], $this->loadedIds);
    }

    /**
     * Make every created collection return the active categories among the ids it is filtered by
     *
     * @param array<int, bool> $categories category id => is active
     * @return void
     */
    private function expectCollections(array $categories): void
    {
        $this->collectionFactory->method('create')->willReturnCallback(
            function () use ($categories) {
                $ids = [];
                $collection = $this->createStub(Collection::class);
                $collection->method('addAttributeToSelect')->willReturnSelf();
                $collection->method('addAttributeToFilter')->willReturnCallback(
                    function ($attribute, $condition) use (&$ids, $collection) {
                        if ($attribute === 'entity_id') {
                            $ids = array_map('intval', $condition);
                            $this->loadedIds[] = $ids;
                        }
                        return $collection;
                    }
                );
                $collection->method('getIterator')->willReturnCallback(
                    function () use (&$ids, $categories) {
                        $items = [];
                        foreach ($ids as $id) {
                            if (!empty($categories[$id])) {
                                $items[] = new DataObject(
                                    [
                                        'id' => $id,
                                        'name' => 'Category ' . $id,
                                        'level' => 3,
                                        'url_key' => 'category-' . $id,
                                        'url_path' => 'parent/category-' . $id,
                                    ]
                                );
                            }
                        }
                        return new \ArrayIterator(array_reverse($items));
                    }
                );
                return $collection;
            }
        );
    }
}
