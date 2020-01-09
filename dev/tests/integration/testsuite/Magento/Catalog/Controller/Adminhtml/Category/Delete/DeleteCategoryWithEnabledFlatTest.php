<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Delete;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Flat\CollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

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
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryFlatCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
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
        $this->assertNotNull($this->getCategoryByIdFromCategoryFlat(333));
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['id' => 333]);
        $this->dispatch('backend/catalog/category/delete');
        $this->assertSessionMessages($this->equalTo([(string)__('You deleted the category.')]));
        $this->assertNull($this->getCategoryByIdFromCategoryFlat(333));
        $this->expectExceptionObject(new NoSuchEntityException(__('No such entity with id = 333')));
        $this->categoryRepository->get(333);
    }

    /**
     * Return category from category flat collection by category ID.
     *
     * @param int $categoryId
     * @return Category|null
     */
    private function getCategoryByIdFromCategoryFlat(int $categoryId): ?Category
    {
        $categoryFlatCollection = $this->categoryFlatCollectionFactory->create();
        $categoryFlatCollection->addIdFilter($categoryId);

        return $categoryFlatCollection->getItemByColumnValue('entity_id', $categoryId);
    }
}
