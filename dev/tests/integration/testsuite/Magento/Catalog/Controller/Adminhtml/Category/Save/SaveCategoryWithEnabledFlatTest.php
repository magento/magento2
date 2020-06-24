<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Save;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Flat;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Catalog\Model\ResourceModel\Category\Flat as CategoryFlatResource;
use Magento\Catalog\Model\ResourceModel\Category\Flat\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResource;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Test cases related to save category with enabled category flat.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class SaveCategoryWithEnabledFlatTest extends AbstractSaveCategoryTest
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var UrlRewriteResource
     */
    private $urlRewriteResource;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Flat
     */
    private $categoryFlatIndexer;

    /**
     * @var CategoryFlatResource
     */
    private $categoryFlatResource;

    /**
     * @var CollectionFactory
     */
    private $categoryFlatCollectionFactory;

    /**
     * @var string
     */
    private $createdCategoryId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->indexerRegistry = $this->_objectManager->get(IndexerRegistry::class);
        $this->urlRewriteResource = $this->_objectManager->get(UrlRewriteResource::class);
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryFlatIndexer = $this->_objectManager->get(Flat::class);
        $this->categoryFlatResource = $this->_objectManager->get(CategoryFlatResource::class);
        $this->categoryFlatCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $categoryFlatIndexer = $this->indexerRegistry->get(State::INDEXER_ID);
        $categoryFlatIndexer->invalidate();
        $this->categoryFlatResource->getConnection()->dropTable($this->categoryFlatResource->getMainTable());
        $this->deleteAllCategoryUrlRewrites();
        try {
            $this->categoryRepository->deleteByIdentifier($this->createdCategoryId);
        } catch (NoSuchEntityException $e) {
            //Category already deleted.
        }
        $this->createdCategoryId = null;
    }

    /**
     * Assert that category flat table is created and flat table contain category with created child category.
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     *
     * @return void
     */
    public function testAddChildCategory(): void
    {
        $parentCategory = $this->categoryRepository->get(333);
        $postData = [
            'name' => 'Custom category name',
            'parent' => 333,
            'is_active' => 1,
            'include_in_menu' => 1,
            'display_mode' => 'PRODUCTS',
            'is_anchor' => true,
            'use_config' => [
                'available_sort_by' => 1,
                'default_sort_by' => 1,
                'filter_price_range' => 1,
            ],
        ];
        $responseData = $this->performSaveCategoryRequest($postData);
        $this->assertRequestIsSuccessfullyPerformed($responseData);
        $this->createdCategoryId = $responseData['category']['entity_id'];
        $this->categoryFlatIndexer->executeFull();
        $this->assertTrue(
            $this->categoryFlatResource->getConnection()->isTableExists($this->categoryFlatResource->getMainTable())
        );
        $this->assertEquals(1, $parentCategory->getChildrenCategories()->getSize());
        $categoryFlatCollection = $this->categoryFlatCollectionFactory->create();
        $categoryFlatCollection->addIdFilter([333, $this->createdCategoryId]);
        $this->assertCount(2, $categoryFlatCollection->getItems());
        /** @var Category $createdCategory */
        $createdCategory = $categoryFlatCollection->getItemByColumnValue('entity_id', $this->createdCategoryId);
        $this->assertEquals($parentCategory->getPath() . '/' . $this->createdCategoryId, $createdCategory->getPath());
        $this->assertEquals($parentCategory->getEntityId(), $createdCategory->getParentId());
        $this->assertEquals($parentCategory->getLevel() + 1, $createdCategory->getLevel());
    }

    /**
     * Assert that category flat table is created and flat table contains category with expected data.
     *
     * @dataProvider enableCategoryDataProvider
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testSaveCategoryWithData(array $postData, array $expectedData): void
    {
        $responseData = $this->performSaveCategoryRequest($postData);
        $this->assertRequestIsSuccessfullyPerformed($responseData);
        $this->createdCategoryId = $responseData['category']['entity_id'];
        $this->categoryFlatIndexer->executeFull();
        $this->assertTrue(
            $this->categoryFlatResource->getConnection()->isTableExists($this->categoryFlatResource->getMainTable())
        );
        $categoryFlatCollection = $this->categoryFlatCollectionFactory->create();
        $categoryFlatCollection->addAttributeToSelect(array_keys($expectedData));
        $categoryFlatCollection->addIdFilter($this->createdCategoryId);
        $this->assertCount(1, $categoryFlatCollection->getItems());
        /** @var Category $createdCategory */
        $createdCategory = $categoryFlatCollection->getFirstItem();
        foreach ($expectedData as $fieldName => $value) {
            $this->assertEquals($value, $createdCategory->getDataByKey($fieldName));
        }
    }

    /**
     * Data provider with create category POST data.
     *
     * @return array
     */
    public function enableCategoryDataProvider(): array
    {
        return [
            'category_is_enabled' => [
                [
                    'name' => 'Custom category name',
                    'parent' => 2,
                    'is_active' => 1,
                    'include_in_menu' => 1,
                    'display_mode' => 'PRODUCTS',
                    'is_anchor' => true,
                    'use_config' => [
                        'available_sort_by' => 1,
                        'default_sort_by' => 1,
                        'filter_price_range' => 1,
                    ],
                ],
                [
                    'is_active' => '1',
                ],
            ],
            'category_is_disabled' => [
                [
                    'name' => 'Custom category name',
                    'parent' => 2,
                    'is_active' => 0,
                    'include_in_menu' => 1,
                    'display_mode' => 'PRODUCTS',
                    'is_anchor' => true,
                    'use_config' => [
                        'available_sort_by' => 1,
                        'default_sort_by' => 1,
                        'filter_price_range' => 1,
                    ],
                ],
                [
                    'is_active' => '0'
                ]
            ],
            'include_in_menu_is_enabled' => [
                [
                    'name' => 'Custom category name',
                    'parent' => 2,
                    'is_active' => 1,
                    'include_in_menu' => 1,
                    'display_mode' => 'PRODUCTS',
                    'is_anchor' => true,
                    'use_config' => [
                        'available_sort_by' => 1,
                        'default_sort_by' => 1,
                        'filter_price_range' => 1,
                    ],
                ],
                [
                    'include_in_menu' => '1',
                ],
            ],
            'include_in_menu_is_disabled' => [
                [
                    'name' => 'Custom category name',
                    'parent' => 2,
                    'is_active' => 1,
                    'include_in_menu' => 0,
                    'display_mode' => 'PRODUCTS',
                    'is_anchor' => true,
                    'use_config' => [
                        'available_sort_by' => 1,
                        'default_sort_by' => 1,
                        'filter_price_range' => 1,
                    ],
                ],
                [
                    'include_in_menu' => '0',
                ],
            ],
        ];
    }

    /**
     * Delete all URL rewrite with entity type equal to "category".
     *
     * @return void
     */
    private function deleteAllCategoryUrlRewrites(): void
    {
        $deleteCondition = $this->urlRewriteResource->getConnection()
            ->quoteInto(UrlRewrite::ENTITY_TYPE . ' = ?', DataCategoryUrlRewriteDatabaseMap::ENTITY_TYPE);
        $this->urlRewriteResource->getConnection()->delete(
            $this->urlRewriteResource->getMainTable(),
            $deleteCondition
        );
    }
}
