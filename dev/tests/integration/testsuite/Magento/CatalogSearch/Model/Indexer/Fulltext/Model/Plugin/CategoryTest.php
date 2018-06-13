<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Model\Plugin;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\StateInterface;

/**
 * Test for Magento\CatalogSearch\Model\Indexer\Fulltext\Model\Plugin\Category
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(Processor::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoAppArea adminhtml
     *
     * @return void
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
     * @param int $count
     * @return Category[]
     */
    private function getCategories(int $count): array
    {
        /** @var Category $category */
        $category = Bootstrap::getObjectManager()->create(Category::class);

        $result = $category->getCollection()->addAttributeToSelect('name')->getItems();
        $result = array_slice($result, 2);

        return array_slice($result, 0, $count);
    }
}
