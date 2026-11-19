<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use GraphQL\Executor\Promise\Adapter\SyncPromise;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider\Breadcrumbs as BreadcrumbsDataProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(Breadcrumbs::class),
    CoversClass(BreadcrumbsDataProvider::class),
    AppArea(Area::AREA_GRAPHQL),
]
class BreadcrumbsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    #[
        DataFixture(CategoryFixture::class, ['name' => 'Breadcrumbs Top'], 'top'),
        DataFixture(CategoryFixture::class, ['name' => 'Breadcrumbs A1', 'parent_id' => '$top.id$'], 'a1'),
        DataFixture(CategoryFixture::class, ['name' => 'Breadcrumbs A2', 'parent_id' => '$top.id$'], 'a2'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$a1.id$'], 'b1'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$a1.id$'], 'b2'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$a2.id$'], 'b3'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$a2.id$'], 'b4'),
    ]
    public function testBreadcrumbsOfAllCategoriesAreLoadedWithOneCollection(): void
    {
        $storage = DataFixtureStorageManager::getStorage();
        $categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $paths = [];
        foreach (['a1', 'a2', 'b1', 'b2', 'b3', 'b4'] as $key) {
            $paths[$key] = $categoryRepository->get((int)$storage->get($key)->getId())->getPath();
        }
        $resolver = $this->objectManager->create(
            Breadcrumbs::class,
            ['breadcrumbsDataProvider' => $this->objectManager->create(BreadcrumbsDataProvider::class)]
        );
        $field = $this->createStub(Field::class);
        $info = $this->createStub(ResolveInfo::class);

        $profiler = $this->objectManager->get(ResourceConnection::class)->getConnection()->getProfiler();
        $profiler->setEnabled(true);
        $profiler->clear();
        try {
            $values = [];
            foreach ($paths as $key => $path) {
                $values[$key] = $resolver->resolve($field, null, $info, ['path' => $path]);
            }
            $breadcrumbs = array_map(fn ($value) => $this->getResolvedValue($value), $values);
            $categorySelects = 0;
            foreach ($profiler->getQueryProfiles(\Zend_Db_Profiler::SELECT) ?: [] as $queryProfile) {
                if (str_contains($queryProfile->getQuery(), 'catalog_category_entity')) {
                    $categorySelects++;
                }
            }
        } finally {
            $profiler->setEnabled(false);
        }

        $topId = (int)$storage->get('top')->getId();
        $this->assertSame([$topId], array_map('intval', array_column($breadcrumbs['a1'], 'category_id')));
        $this->assertSame(['Breadcrumbs Top'], array_column($breadcrumbs['a2'], 'category_name'));
        $this->assertSame(['Breadcrumbs Top', 'Breadcrumbs A1'], array_column($breadcrumbs['b2'], 'category_name'));
        $this->assertSame(['Breadcrumbs Top', 'Breadcrumbs A2'], array_column($breadcrumbs['b3'], 'category_name'));
        $this->assertLessThanOrEqual(
            2,
            $categorySelects,
            'Breadcrumbs of 6 categories must be loaded with one collection (entity and attribute value selects)'
        );
    }

    /**
     * Get the value a resolver returned, running deferred values the way the GraphQL executor does
     *
     * @param mixed $value
     * @return mixed
     */
    private function getResolvedValue(mixed $value): mixed
    {
        if (!$value instanceof SyncPromise) {
            return $value;
        }
        $result = null;
        $value->then(
            function ($resolved) use (&$result) {
                $result = $resolved;
            }
        );
        SyncPromise::runQueue();

        return $result;
    }
}
