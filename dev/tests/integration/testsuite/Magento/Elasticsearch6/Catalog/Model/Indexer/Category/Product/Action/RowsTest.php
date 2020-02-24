<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Rows;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
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

        $searchResponse = $this->searchByName('Simple');
        $categoryIds = $this->getCategoryIdsFromResponse($searchResponse);

        $this->assertNotEmpty($categoryIds);

        $this->assertContains($categoryA->getId(), $categoryIds);
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
     * Search docs in Elasticsearch by name.
     *
     * @param string $text
     * @return SearchResultInterface
     */
    private function searchByName(string $text): SearchResultInterface
    {
        /** @var \Magento\Framework\Search\Request\Config\Converter $converter */
        $converter = $this->objectManager->create(\Magento\Framework\Search\Request\Config\Converter::class);
        $document = new \DOMDocument();
        $document->load($this->getRequestConfigPath());
        $requestConfig = $converter->convert($document);
        /** @var \Magento\Framework\Search\Request\Config $config */
        $config = $this->objectManager->create(\Magento\Framework\Search\Request\Config::class);
        $config->merge($requestConfig);

        $requestBuilder = $this->objectManager->create(
            \Magento\Framework\Search\Request\Builder::class,
            ['config' => $config]
        );
        $requestBuilder->bind('fulltext_search_query', $text);
        $requestBuilder->setRequestName('one_match_with_aggregations');
        $queryRequest = $requestBuilder->create();

        $searchEngine = $this->objectManager->create(SearchEngineInterface::class);
        $queryResult = $searchEngine->search($queryRequest);

        $searchResponseBuilder = $this->objectManager->create(SearchResponseBuilder::class);

        return $searchResponseBuilder->build($queryResult);
    }

    /**
     * Extract category ids from search result.
     *
     * @param SearchResultInterface $searchResponse
     * @return array
     */
    private function getCategoryIdsFromResponse(SearchResultInterface $searchResponse): array
    {
        $categoryIds = [];
        foreach ($searchResponse->getAggregations()->getBucket('category_bucket')->getValues() as $value) {
            $categoryIds[] = $value->getValue();
        }

        return $categoryIds;
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
