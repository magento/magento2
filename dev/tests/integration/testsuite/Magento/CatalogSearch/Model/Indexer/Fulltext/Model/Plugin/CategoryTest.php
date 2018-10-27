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
<<<<<<< HEAD

=======
use Magento\Framework\Indexer\StateInterface;

/**
 * Test for Magento\CatalogSearch\Model\Indexer\Fulltext\Model\Plugin\Category
 */
>>>>>>> upstream/2.2-develop
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

<<<<<<< HEAD
=======
    /**
     * @inheritdoc
     */
>>>>>>> upstream/2.2-develop
    protected function setUp()
    {
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(Processor::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoAppArea adminhtml
<<<<<<< HEAD
=======
     *
     * @return void
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
        $this->assertEquals(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID, $status);
=======
        $this->assertEquals(StateInterface::STATUS_INVALID, $status);
>>>>>>> upstream/2.2-develop
    }

    /**
     * @param int $count
     * @return Category[]
     */
<<<<<<< HEAD
    private function getCategories($count)
    {
        /** @var Category $category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
=======
    private function getCategories(int $count): array
    {
        /** @var Category $category */
        $category = Bootstrap::getObjectManager()->create(Category::class);
>>>>>>> upstream/2.2-develop

        $result = $category->getCollection()->addAttributeToSelect('name')->getItems();
        $result = array_slice($result, 2);

        return array_slice($result, 0, $count);
    }
}
