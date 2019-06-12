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
use Magento\Framework\Indexer\StateInterface;

/**
 * Test for Magento\CatalogSearch\Model\Indexer\Fulltext\Model\Plugin\Category
 */
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
    /**
     * @inheritdoc
     */
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    protected function setUp()
    {
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(Processor::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoAppArea adminhtml
<<<<<<< HEAD
     *
     * @return void
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
        $this->assertEquals(StateInterface::STATUS_INVALID, $status);
=======
        $this->assertEquals(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID, $status);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @param int $count
     * @return Category[]
     */
<<<<<<< HEAD
    private function getCategories(int $count): array
    {
        /** @var Category $category */
        $category = Bootstrap::getObjectManager()->create(Category::class);
=======
    private function getCategories($count)
    {
        /** @var Category $category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $result = $category->getCollection()->addAttributeToSelect('name')->getItems();
        $result = array_slice($result, 2);

        return array_slice($result, 0, $count);
    }
}
