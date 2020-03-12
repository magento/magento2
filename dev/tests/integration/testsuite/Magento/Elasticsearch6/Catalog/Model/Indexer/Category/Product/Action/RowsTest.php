<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Rows;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Search\Request\Config\Converter;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Test for Magento\Catalog\Model\Indexer\Category\Product\Action\Rows class.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Rows
     */
    private $rowsIndexer;

    /**
     * @var DefaultCategory
     */
    private $defaultCategoryHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->rowsIndexer = $this->objectManager->get(Rows::class);
        $this->defaultCategoryHelper = $this->objectManager->get(DefaultCategory::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_tree_with_products.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest
     * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
     * @return void
     */
    public function testCategoryMoveWithElasticsearch(): void
    {
        $categoryA = $this->getCategory('Category A');
        $categoryB = $this->getCategory('Category B');
        $categoryC = $this->getCategory('Category C');

        /** Move $categoryB to $categoryA */
        $categoryB->move($categoryA->getId(), null);
        $this->rowsIndexer->execute(
            [
                $this->defaultCategoryHelper->getId(),
                $categoryA->getId(),
                $categoryB->getId(),
                $categoryC->getId(),
            ],
            true
        );

        $collection = $this->searchByCategoryId((int) $categoryA->getId());

        $this->assertProductsArePresentInCollection($collection->getAllIds());
    }

    /**
     * Assert that expected products are present in collection.
     *
     * @param array $productIds
     *
     * @return void
     */
    private function assertProductsArePresentInCollection(array $productIds): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $firstProductId = $productRepository->get('simpleB')->getId();
        $secondProductId = $productRepository->get('simpleC')->getId();

        $this->assertCount(2, $productIds);
        $this->assertContains($secondProductId, $productIds);
        $this->assertContains($firstProductId, $productIds);
    }

    /**
     * Gets category by name.
     *
     * @param string $name
     * @return CategoryInterface
     */
    private function getCategory(string $name): CategoryInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
            ->create();
        /** @var CategoryListInterface $repository */
        $repository = $this->objectManager->get(CategoryListInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Search docs in Elasticsearch by category id.
     *
     * @param int $categoryId
     * @return Collection
     */
    private function searchByCategoryId(int $categoryId): Collection
    {
        /** @var Converter $converter */
        $converter = $this->objectManager->get(Converter::class);
        $document = new \DOMDocument();
        $document->load($this->getRequestConfigPath());
        $requestConfig = $converter->convert($document);
        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        $config->merge($requestConfig);

        $requestBuilder = $this->objectManager->get(
            Builder::class,
            ['config' => $config]
        );
        $requestBuilder->bind('category_ids', $categoryId);
        $requestBuilder->bind('visibility', [2,4]);
        $requestBuilder->setRequestName('catalog_view_container');
        $queryRequest = $requestBuilder->create();

        $searchEngine = $this->objectManager->get(SearchEngineInterface::class);
        $queryResult = $searchEngine->search($queryRequest);

        $searchResponseBuilder = $this->objectManager->get(SearchResponseBuilder::class);
        $searchResponse = $searchResponseBuilder->build($queryResult);

        $searchResultApplierFactory = $this->objectManager->get(SearchResultApplierFactory::class);
        $collection = $this->objectManager->get(Collection::class);
        $searchResultApplierFactory->create(
            [
                'collection' => $collection,
                'searchResult' => $searchResponse,
                'orders' => [],
                'size' => 12,
                'currentPage' => 1,
            ]
        )->apply();

        return $collection;
    }

    /**
     * Get request config path.
     *
     * @return string
     */
    private function getRequestConfigPath()
    {
        return __DIR__ . '/../../../../../../../Elasticsearch/_files/requests.xml';
    }
}
