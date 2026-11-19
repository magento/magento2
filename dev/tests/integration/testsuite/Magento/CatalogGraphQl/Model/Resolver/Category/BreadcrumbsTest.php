<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(Breadcrumbs::class),
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
        $topId = (int)DataFixtureStorageManager::getStorage()->get('top')->getId();
        $tree = 'categoryList(filters: {ids: {eq: "' . $topId . '"}}) '
            . '{ name children { name %1$s children { name %1$s } } }';
        $breadcrumbs = 'breadcrumbs { category_id category_name category_level }';

        [, $queriesWithoutBreadcrumbs] = $this->dispatch(sprintf($tree, ''));
        [$output, $queriesWithBreadcrumbs] = $this->dispatch(sprintf($tree, $breadcrumbs));

        $children = array_column($output['data']['categoryList'][0]['children'], null, 'name');
        $this->assertSame(
            [['category_id' => $topId, 'category_name' => 'Breadcrumbs Top', 'category_level' => 2]],
            $children['Breadcrumbs A1']['breadcrumbs']
        );
        $this->assertCount(2, $children['Breadcrumbs A2']['children']);
        foreach ($children['Breadcrumbs A2']['children'] as $child) {
            $this->assertSame(
                ['Breadcrumbs Top', 'Breadcrumbs A2'],
                array_column($child['breadcrumbs'], 'category_name')
            );
        }
        $this->assertLessThanOrEqual(
            2,
            $queriesWithBreadcrumbs - $queriesWithoutBreadcrumbs,
            'Breadcrumbs of 6 categories must be loaded with one collection (entity and attribute value selects)'
        );
    }

    /**
     * Dispatch a GraphQL query and count the category entity selects it ran
     *
     * @param string $query
     * @return array
     */
    private function dispatch(string $query): array
    {
        $request = $this->objectManager->create(Http::class);
        $request->setPathInfo('/graphql');
        $request->setMethod('POST');
        $request->setContent(json_encode(['query' => '{ ' . $query . ' }']));
        $request->getHeaders()->addHeaders(['Content-Type' => 'application/json']);

        $profiler = $this->objectManager->get(ResourceConnection::class)->getConnection()->getProfiler();
        $profiler->setEnabled(true);
        $profiler->clear();
        try {
            $response = $this->objectManager->get(GraphQl::class)->dispatch($request);
            $categorySelects = 0;
            foreach ($profiler->getQueryProfiles(\Zend_Db_Profiler::SELECT) ?: [] as $queryProfile) {
                if (str_contains($queryProfile->getQuery(), 'catalog_category_entity')) {
                    $categorySelects++;
                }
            }
        } finally {
            $profiler->setEnabled(false);
        }
        $output = $this->objectManager->get(SerializerInterface::class)->unserialize($response->getContent());
        $this->assertArrayNotHasKey('errors', $output);

        return [$output, $categorySelects];
    }
}
