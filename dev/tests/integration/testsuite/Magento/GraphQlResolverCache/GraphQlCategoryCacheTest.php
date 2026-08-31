<?php
declare(strict_types=1);

namespace Magento\GraphQlResolverCache;

use Magento\GraphQl\Service\GraphQlRequest;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies category resolver caching through a real GraphQL request.
 *
 * @magentoAppArea graphql
 * @magentoAppIsolation enabled
 */
class GraphQlCategoryCacheTest extends TestCase
{
    public function testCategoryQueryCanBeServedRepeatedlyFromResolverCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $cache = $objectManager->get(Type::class);
        $cache->clean();
        $query = '{ categories { items { uid name } } }';

        try {
            $first = $request->send($query);
            $this->assertSame(200, $first->getHttpResponseCode());
            $this->assertStringNotContainsString('errors', strtolower((string)$first->getBody()));

            $second = $request->send($query);
            $this->assertSame(200, $second->getHttpResponseCode());
            $this->assertSame((string)$first->getBody(), (string)$second->getBody());
        } finally {
            $cache->clean();
        }
    }

    /**
     * @magentoDataFixture Magento/GraphQlResolverCache/_files/category.php
     */
    public function testCategoryUpdateInvalidatesResolverCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $cache = $objectManager->get(Type::class);
        $repository = $objectManager->get(CategoryRepositoryInterface::class);
        $cache->clean();
        $query = '{ categories(filters: { name: { match: "GraphQL Cache Category" } }) { items { name } } }';

        try {
            $first = $request->send($query);
            $this->assertStringContainsString('GraphQL Cache Category', (string) $first->getBody());
            $collection = $objectManager->get(CollectionFactory::class)->create();
            $category = $collection->addAttributeToFilter('name', 'GraphQL Cache Category')->getFirstItem();
            $this->assertNotEmpty($category->getId());
            $originalName = $category->getName();
            $category->setName($originalName . ' Updated');
            $repository->save($category);
            $updated = $request->send($query);
            $this->assertStringContainsString($originalName . ' Updated', (string) $updated->getBody());
        } finally {
            $cache->clean();
        }
    }
}
