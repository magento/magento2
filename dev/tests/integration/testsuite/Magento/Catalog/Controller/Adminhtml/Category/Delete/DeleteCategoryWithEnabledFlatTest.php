<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Delete;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Catalog\Model\ResourceModel\Category\Flat as CategoryFlatResource;
use Magento\Catalog\Model\ResourceModel\Category\Flat\CollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test cases related to delete category with enabled category flat.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class DeleteCategoryWithEnabledFlatTest extends AbstractBackendController
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryFlatResource
     */
    private $categoryFlatResource;

    /**
     * @var CollectionFactory
     */
    private $categoryFlatCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->indexerRegistry = $this->_objectManager->get(IndexerRegistry::class);
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryFlatResource = $this->_objectManager->get(CategoryFlatResource::class);
        $this->categoryFlatCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        $categoryFlatIndexer = $this->indexerRegistry->get(State::INDEXER_ID);
        $categoryFlatIndexer->invalidate();
        $this->categoryFlatResource->getConnection()->dropTable($this->categoryFlatResource->getMainTable());
    }

    /**
     * Check that product is deleted from flat table.
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Catalog/_files/reindex_catalog_category_flat.php
     *
     * @return void
     */
    public function testDeleteCategory(): void
    {
        $this->assertEquals(1, $this->getFlatCategoryCollectionSizeByCategoryId(333));
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['id' => 333]);
        $this->dispatch('backend/catalog/category/delete');
        $this->assertSessionMessages($this->equalTo([(string)__('You deleted the category.')]));
        $this->assertEquals(0, $this->getFlatCategoryCollectionSizeByCategoryId(333));
        $this->checkCategoryIsDeleted(333);
    }

    /**
     * Return collection size from category flat collection by category ID.
     *
     * @param int $categoryId
     * @return int
     */
    private function getFlatCategoryCollectionSizeByCategoryId(int $categoryId): int
    {
        $categoryFlatCollection = $this->categoryFlatCollectionFactory->create();
        $categoryFlatCollection->addIdFilter($categoryId);

        return $categoryFlatCollection->getSize();
    }

    /**
     * Assert that category is deleted.
     *
     * @param int $categoryId
     */
    private function checkCategoryIsDeleted(int $categoryId): void
    {
        $this->expectExceptionObject(new NoSuchEntityException(__("No such entity with id = {$categoryId}")));
        $this->categoryRepository->get($categoryId);
    }
}
