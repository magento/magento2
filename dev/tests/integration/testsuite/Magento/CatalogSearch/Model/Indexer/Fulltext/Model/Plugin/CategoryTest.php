<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Model\Plugin;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Framework\Indexer\StateInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for category repository plugin
 */
class CategoryTest extends TestCase
{
    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(Processor::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->categoryCollectionFactory = Bootstrap::getObjectManager()->create(CategoryCollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoAppArea adminhtml
     */
    public function testIndexerInvalidatedAfterCategoryDelete()
    {
        $this->indexerProcessor->reindexAll();
        $isIndexerValid = (bool)$this->indexerProcessor->getIndexer()->isValid();

        $category = $this->getCategories(1);
        $this->categoryRepository->delete(array_pop($category));

        $state = $this->indexerProcessor->getIndexer()->getState();
        $state->loadByIndexer($this->indexerProcessor->getIndexerId());
        $status = $state->getStatus();

        $this->assertTrue($isIndexerValid);
        $this->assertEquals(StateInterface::STATUS_INVALID, $status);
    }

    /**
     * Returns categories
     *
     * @param int $count
     * @return Category[]
     */
    private function getCategories(int $count): array
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->getItems();
        $result = array_slice($collection, 2);

        return array_slice($result, 0, $count);
    }
}
