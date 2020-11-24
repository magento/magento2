<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Save;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test related to update category.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class UpdateCategoryTest extends AbstractSaveCategoryTest
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @dataProvider categoryDataProvider
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     *
     * @param array $postData
     * @return void
     */
    public function testUpdateCategoryForDefaultStoreView(array $postData): void
    {
        $storeId = (int)$this->storeManager->getStore('default')->getId();
        $postData = array_merge($postData, ['store_id' => $storeId]);
        $responseData = $this->performSaveCategoryRequest($postData);
        $this->assertRequestIsSuccessfullyPerformed($responseData);
        $category = $this->categoryRepository->get($postData['entity_id'], $postData['store_id']);
        unset($postData['use_default']);
        unset($postData['use_config']);
        foreach ($postData as $key => $value) {
            $this->assertEquals($value, $category->getData($key));
        }
    }

    /**
     * @return array
     */
    public function categoryDataProvider(): array
    {
        return [
            [
                'post_data' => [
                    'entity_id' => 333,
                    CategoryInterface::KEY_IS_ACTIVE => '0',
                    CategoryInterface::KEY_INCLUDE_IN_MENU => '0',
                    CategoryInterface::KEY_NAME => 'Category default store',
                    'description' => 'Description for default store',
                    'landing_page' => '',
                    'display_mode' => Category::DM_MIXED,
                    CategoryInterface::KEY_AVAILABLE_SORT_BY => ['name', 'price'],
                    'default_sort_by' => 'price',
                    'filter_price_range' => 5,
                    'url_key' => 'default-store-category',
                    'meta_title' => 'meta_title default store',
                    'meta_keywords' => 'meta_keywords default store',
                    'meta_description' => 'meta_description default store',
                    'custom_use_parent_settings' => '0',
                    'custom_design' => '2',
                    'page_layout' => '2columns-right',
                    'custom_apply_to_products' => '1',
                    'use_default' => [
                        CategoryInterface::KEY_NAME => '0',
                        CategoryInterface::KEY_IS_ACTIVE => '0',
                        CategoryInterface::KEY_INCLUDE_IN_MENU => '0',
                        'url_key' => '0',
                        'meta_title' => '0',
                        'custom_use_parent_settings' => '0',
                        'custom_apply_to_products' => '0',
                        'description' => '0',
                        'landing_page' => '0',
                        'display_mode' => '0',
                        'custom_design' => '0',
                        'page_layout' => '0',
                        'meta_keywords' => '0',
                        'meta_description' => '0',
                        'custom_layout_update' => '0',
                    ],
                    'use_config' => [
                        CategoryInterface::KEY_AVAILABLE_SORT_BY => false,
                        'default_sort_by' => false,
                        'filter_price_range' => false,
                    ],
                ],
            ],
        ];
    }
}
