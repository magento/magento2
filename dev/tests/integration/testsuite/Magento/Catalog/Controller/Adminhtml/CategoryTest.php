<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml;

use Magento\Framework\Acl\Builder;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory as CategoryModelFactory;

/**
 * Test for admin category functionality.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends AbstractBackendController
{
    /**
     * @var ProductResource
     */
    protected $productResource;
    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var CategoryModelFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * @inheritDoc
     *
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var ProductResource $productResource */
        $this->productResource = Bootstrap::getObjectManager()->get(
            ProductResource::class
        );
        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
        $this->categoryFactory = Bootstrap::getObjectManager()->get(CategoryModelFactory::class);
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->storeRepository = $this->_objectManager->get(StoreRepositoryInterface::class);
        $this->json = $this->_objectManager->get(Json::class);
    }

    /**
     * Test save action.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @dataProvider saveActionDataProvider
     * @param array $inputData
     * @param array $defaultAttributes
     * @param array $attributesSaved
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveAction(array $inputData, array $defaultAttributes, array $attributesSaved = []): void
    {
        $store = $this->storeRepository->get('fixturestore');
        $storeId = $store->getId();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($inputData);
        $this->getRequest()->setParam('store', $storeId);
        $this->getRequest()->setParam('id', 2);
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            $this->equalTo(['You saved the category.']),
            MessageInterface::TYPE_SUCCESS
        );
        /** @var $category Category */
        $category = $this->categoryRepository->get(2, $storeId);
        $errors = [];
        foreach ($attributesSaved as $attribute => $value) {
            $actualValue = $category->getData($attribute);
            if ($value !== $actualValue) {
                $errors[] = "value for '{$attribute}' attribute must be '{$value}'"
                    . ", but '{$actualValue}' is found instead";
            }
        }

        foreach ($defaultAttributes as $attribute => $exists) {
            if ($exists !== $category->getExistsStoreValueFlag($attribute)) {
                if ($exists) {
                    $errors[] = "custom value for '{$attribute}' attribute is not found";
                } elseif (!$exists && $category->getCustomAttribute($attribute) !== null) {
                    $errors[] = "custom value for '{$attribute}' attribute is found, but default one must be used";
                }
            }
        }

        $this->assertEmpty($errors, "\n" . join("\n", $errors));
    }

    /**
     * Check default value for category url path
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories.php
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDefaultValueForCategoryUrlPath(): void
    {
        $categoryId = 3;
        $category = $this->categoryRepository->get($categoryId);
        $newUrlPath = 'test_url_path';
        $defaultUrlPath = $category->getData('url_path');

        // update url_path and check it
        $category->setStoreId(1);
        $category->setUrlKey($newUrlPath);
        $category->setUrlPath($newUrlPath);
        $this->categoryRepository->save($category);
        $this->assertEquals($newUrlPath, $category->getUrlPath());

        // set default url_path and check it
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $postData = $category->getData();
        $postData['use_default'] =
            [
                'available_sort_by' => 1,
                'default_sort_by' => 1,
                'url_key' => 1,
            ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the category.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $category = $this->categoryRepository->get($categoryId);
        $this->assertEquals($defaultUrlPath, $category->getData('url_key'));
    }

    /**
     * Test save action from product form page
     *
     * @param array $postData
     * @dataProvider categoryCreatedFromProductCreationPageDataProvider
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testSaveActionFromProductCreationPage(array $postData): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);

        $this->dispatch('backend/catalog/category/save');
        $body = $this->getResponse()->getBody();

        if (empty($postData['return_session_messages_only'])) {
            $this->assertRedirect(
                $this->stringContains('http://localhost/index.php/backend/catalog/category/edit/')
            );
        } else {
            $result = $this->json->unserialize($body);
            $this->assertArrayHasKey('messages', $result);
            $this->assertFalse($result['error']);
            $category = $result['category'];
            $this->assertEquals('Category Created From Product Creation Page', $category['name']);
            $this->assertEquals(1, $category['is_active']);
            $this->assertEquals(0, $category['include_in_menu']);
            $this->assertEquals(2, $category['parent_id']);
            $this->assertNull($category['available_sort_by']);
            $this->assertNull($category['default_sort_by']);
        }
    }

    /**
     * Get category post data
     *
     * @static
     * @return array
     */
    public static function categoryCreatedFromProductCreationPageDataProvider(): array
    {
        /* Keep in sync with new-category-dialog.js */
        $postData = [
            'name' => 'Category Created From Product Creation Page',
            'is_active' => 1,
            'include_in_menu' => 0,
            'use_config' => [
                'available_sort_by' => 1,
                'default_sort_by' => 1
            ],
            'parent' => 2,
        ];

        return [[$postData], [$postData + ['return_session_messages_only' => 1]]];
    }

    /**
     * Test SuggestCategories finds any categories.
     *
     * @return void
     */
    public function testSuggestCategoriesActionDefaultCategoryFound(): void
    {
        $this->getRequest()->setParam('label_part', 'Default');
        $this->dispatch('backend/catalog/category/suggestCategories');
        $this->assertEquals(
            '[{"id":"2","children":[],"is_active":"1","label":"Default Category"}]',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Test SuggestCategories properly processes search by label.
     *
     * @return void
     */
    public function testSuggestCategoriesActionNoSuggestions(): void
    {
        $this->getRequest()->setParam('label_part', strrev('Default'));
        $this->dispatch('backend/catalog/category/suggestCategories');
        $this->assertEquals('[]', $this->getResponse()->getBody());
    }

    /**
     * Save action data provider
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function saveActionDataProvider(): array
    {
        return [
            'default values' => [
                [
                    'id' => '2',
                    'entity_id' => '2',
                    'path' => '1/2',
                    'url_key' => 'default-category',
                    'is_anchor' => false,
                    'use_default' => [
                        'name' => 1,
                        'is_active' => 1,
                        'thumbnail' => 1,
                        'description' => 1,
                        'image' => 1,
                        'meta_title' => 1,
                        'meta_keywords' => 1,
                        'meta_description' => 1,
                        'include_in_menu' => 1,
                        'display_mode' => 1,
                        'landing_page' => 1,
                        'available_sort_by' => 1,
                        'default_sort_by' => 1,
                        'filter_price_range' => 1,
                        'custom_apply_to_products' => 1,
                        'custom_design' => 1,
                        'custom_design_from' => 1,
                        'custom_design_to' => 1,
                        'page_layout' => 1,
                        'custom_layout_update' => null,
                    ],
                ],
                [
                    'name' => false,
                    'default_sort_by' => false,
                    'display_mode' => false,
                    'meta_title' => false,
                    'custom_design' => false,
                    'page_layout' => false,
                    'is_active' => false,
                    'include_in_menu' => false,
                    'landing_page' => false,
                    'is_anchor' => false,
                    'custom_apply_to_products' => false,
                    'available_sort_by' => false,
                    'description' => false,
                    'meta_keywords' => false,
                    'meta_description' => false,
                    'custom_layout_update' => false,
                    'custom_design_from' => false,
                    'custom_design_to' => false,
                    'filter_price_range' => false
                ],
            ],
            'custom values' => [
                [
                    'id' => '2',
                    'entity_id' => '2',
                    'path' => '1/2',
                    'name' => 'Custom Name',
                    'is_active' => '0',
                    'description' => 'Custom Description',
                    'meta_title' => 'Custom Title',
                    'meta_keywords' => 'Custom keywords',
                    'meta_description' => 'Custom meta description',
                    'include_in_menu' => '0',
                    'url_key' => 'default-category',
                    'display_mode' => 'PRODUCTS',
                    'landing_page' => '1',
                    'is_anchor' => true,
                    'custom_apply_to_products' => '0',
                    'custom_design' => 'Magento/blank',
                    'custom_design_from' => '5/21/2015',
                    'custom_design_to' => '5/29/2015',
                    'page_layout' => '',
                    'use_config' => [
                        'available_sort_by' => 1,
                        'default_sort_by' => 1,
                        'filter_price_range' => 1,
                    ],
                ],
                [
                    'name' => true,
                    'default_sort_by' => false,
                    'display_mode' => true,
                    'meta_title' => true,
                    'custom_design' => true,
                    'page_layout' => true,
                    'is_active' => true,
                    'include_in_menu' => true,
                    'landing_page' => true,
                    'custom_apply_to_products' => true,
                    'available_sort_by' => false,
                    'description' => true,
                    'meta_keywords' => true,
                    'meta_description' => true,
                    'custom_design_from' => true,
                    'custom_design_to' => true,
                    'filter_price_range' => false
                ],
                [
                    'name' => 'Custom Name',
                    'default_sort_by' => null,
                    'display_mode' => 'PRODUCTS',
                    'meta_title' => 'Custom Title',
                    'custom_design' => 'Magento/blank',
                    'page_layout' => null,
                    'is_active' => '0',
                    'include_in_menu' => '0',
                    'landing_page' => '1',
                    'custom_apply_to_products' => '0',
                    'available_sort_by' => null,
                    'description' => 'Custom Description',
                    'meta_keywords' => 'Custom keywords',
                    'meta_description' => 'Custom meta description',
                    'custom_design_from' => '2015-05-21 00:00:00',
                    'custom_design_to' => '2015-05-29 00:00:00',
                    'filter_price_range' => null
                ],
            ],
        ];
    }

    /**
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testIncorrectDateFrom(): void
    {
        $data = [
            'name' => 'Test Category',
            'attribute_set_id' => '3',
            'parent_id' => 2,
            'path' => '1/2',
            'is_active' => true,
            'custom_design_from' => '5/29/2015',
            'custom_design_to' => '5/21/2015',
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($data);
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('Make sure the To Date is later than or the same as the From Date.')]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test validation.
     *
     * @return void
     */
    public function testSaveActionCategoryWithDangerRequest(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'general' => [
                    'path' => '1',
                    'name' => 'test',
                    'is_active' => '1',
                    'entity_id' => 1500,
                    'include_in_menu' => '1',
                    'available_sort_by' => 'name',
                    'default_sort_by' => 'name',
                ],
            ]
        );
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            $this->equalTo(['The "Name" attribute value is empty. Set the attribute and try again.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test move action.
     *
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @dataProvider moveActionDataProvider
     *
     * @param int $parentId
     * @param int $childId
     * @param string $childUrlKey
     * @param int $grandChildId
     * @param string $grandChildUrlKey
     * @param boolean $error
     * @return void
     */
    public function testMoveAction(
        int $parentId,
        int $childId,
        string $childUrlKey,
        int $grandChildId,
        string $grandChildUrlKey,
        bool $error
    ): void {
        $urlKeys = [
            $childId => $childUrlKey,
            $grandChildId => $grandChildUrlKey,
        ];
        foreach ($urlKeys as $categoryId => $urlKey) {
            /** @var $category Category */
            $category = $this->categoryFactory->create();
            if ($categoryId > 0) {
                $category->load($categoryId)
                    ->setUrlKey($urlKey)
                    ->save();
            }
        }
        $this->getRequest()
            ->setPostValue('id', $grandChildId)
            ->setPostValue('pid', $parentId)
            ->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/category/move');
        $jsonResponse = $this->json->unserialize($this->getResponse()->getBody());
        $this->assertNotNull($jsonResponse);
        $this->assertEquals($error, $jsonResponse['error']);
    }

    /**
     * Move action data provider
     *
     * @return array
     */
    public function moveActionDataProvider(): array
    {
        return [
            [400, 401, 'first_url_key', 402, 'second_url_key', false],
            [400, 401, 'duplicated_url_key', 402, 'duplicated_url_key', false],
            [0, 401, 'first_url_key', 402, 'second_url_key', true],
            [400, 401, 'first_url_key', 0, 'second_url_key', true],
        ];
    }

    /**
     * Test save category with product position.
     *
     * @magentoDataFixture Magento/Catalog/_files/products_in_different_stores.php
     * @magentoDbIsolation disabled
     * @dataProvider saveActionWithDifferentWebsitesDataProvider
     *
     * @param array $postData
     */
    public function testSaveCategoryWithProductPosition(array $postData): void
    {
        $store = $this->storeRepository->get('fixturestore');
        $storeId = $store->getId();
        $oldCategoryProductsCount = $this->getCategoryProductsCount();
        $this->getRequest()->setParam('store', $storeId);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('id', 96377);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the category.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $newCategoryProductsCount = $this->getCategoryProductsCount();
        $this->assertEquals(
            $oldCategoryProductsCount,
            $newCategoryProductsCount,
            'After changing product position number of records from catalog_category_product has changed'
        );
    }

    /**
     * Save action data provider
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function saveActionWithDifferentWebsitesDataProvider(): array
    {
        return [
            'default_values' => [
                [
                    'store_id' => '1',
                    'entity_id' => '96377',
                    'attribute_set_id' => '4',
                    'parent_id' => '2',
                    'created_at' => '2018-11-29 08:28:37',
                    'updated_at' => '2018-11-29 08:57:43',
                    'path' => '1/2/96377',
                    'level' => '2',
                    'children_count' => '0',
                    'name' => 'Category 1',
                    'display_mode' => 'PRODUCTS',
                    'url_key' => 'category-1',
                    'url_path' => 'category-1',
                    'automatic_sorting' => '0',
                    'is_active' => '1',
                    'is_anchor' => '1',
                    'include_in_menu' => '1',
                    'custom_use_parent_settings' => '0',
                    'custom_apply_to_products' => '0',
                    'path_ids' => [
                        0 => '1',
                        1 => '2',
                        2 => '96377'
                    ],
                    'use_config' => [
                        'available_sort_by' => 'true',
                        'default_sort_by' => 'true',
                        'filter_price_range' => 'true'
                    ],
                    'id' => '',
                    'parent' => '0',
                    'use_default' => [
                        'name' => '1',
                        'url_key' => '1',
                        'meta_title' => '1',
                        'is_active' => '1',
                        'include_in_menu' => '1',
                        'custom_use_parent_settings' => '1',
                        'custom_apply_to_products' => '1',
                        'description' => '1',
                        'landing_page' => '1',
                        'display_mode' => '1',
                        'custom_design' => '1',
                        'page_layout' => '1',
                        'meta_keywords' => '1',
                        'meta_description' => '1',
                        'custom_layout_update' => '1',
                        'image' => '1'
                    ],
                    'filter_price_range' => false,
                    'meta_title' => false,
                    'url_key_create_redirect' => 'category-1',
                    'description' => false,
                    'landing_page' => false,
                    'default_sort_by' => 'position',
                    'available_sort_by' => false,
                    'custom_design' => false,
                    'page_layout' => false,
                    'meta_keywords' => false,
                    'meta_description' => false,
                    'custom_layout_update' => false,
                    'position_cache_key' => '5c069248346ac',
                    'is_smart_category' => '0',
                    'smart_category_rules' => false,
                    'sort_order' => '0',
                    'vm_category_products' => '{"1":1,"3":0}'
                ]
            ]
        ];
    }

    /**
     * Get items count from catalog_category_product.
     *
     * @return int
     */
    private function getCategoryProductsCount(): int
    {
        $oldCategoryProducts = $this->productResource->getConnection()->select()->from(
            $this->productResource->getTable('catalog_category_product'),
            'product_id'
        );
        return count(
            $this->productResource->getConnection()->fetchAll($oldCategoryProducts)
        );
    }

    /**
     * Check whether additional authorization is required for the design fields.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @throws \Throwable
     * @return void
     */
    public function testSaveDesign(): void
    {
        /** @var $store \Magento\Store\Model\Store */
        $store = Bootstrap::getObjectManager()->create(Store::class);
        $store->load('fixturestore', 'code');
        $storeId = $store->getId();
        $requestData = [
            'id' => '2',
            'entity_id' => '2',
            'path' => '1/2',
            'name' => 'Custom Name',
            'is_active' => '0',
            'description' => 'Custom Description',
            'meta_title' => 'Custom Title',
            'meta_keywords' => 'Custom keywords',
            'meta_description' => 'Custom meta description',
            'include_in_menu' => '0',
            'url_key' => 'default-test-category',
            'display_mode' => 'PRODUCTS',
            'landing_page' => '1',
            'is_anchor' => true,
            'store_id' => $storeId,
            'use_config' => [
                'available_sort_by' => 1,
                'default_sort_by' => 1,
                'filter_price_range' => 1,
            ],
        ];
        $uri = 'backend/catalog/category/save';

        //Trying to update the category's design settings without proper permissions.
        //Expected list of sessions messages collected throughout the controller calls.
        $sessionMessages = ['Not allowed to edit the category\'s design attributes'];
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_category_design');
        $requestData['custom_layout_update_file'] = 'test-file';
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('store', $requestData['store_id']);
        $this->getRequest()->setParam('id', $requestData['id']);
        $this->dispatch($uri);
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );

        //Trying again with the permissions.
        $requestData['custom_layout_update_file'] = null;
        $requestData['page_layout'] = '2columns-left';
        $this->aclBuilder->getAcl()
            ->allow(null, ['Magento_Catalog::categories', 'Magento_Catalog::edit_category_design']);
        $this->getRequest()->setDispatched(false);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('store', $requestData['store_id']);
        $this->getRequest()->setParam('id', $requestData['id']);
        $this->dispatch($uri);
        /** @var CategoryModel $category */
        $category = $this->categoryFactory->create();
        $category->load(2);
        $this->assertEquals('2columns-left', $category->getData('page_layout'));
        //No new error messages
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );

        //Trying to save special value without the permissions.
        $requestData['custom_layout_update_file'] = CategoryModel\Attribute\Backend\LayoutUpdate::VALUE_USE_UPDATE_XML;
        $requestData['description'] = 'test';
        $this->aclBuilder->getAcl()->deny(null, ['Magento_Catalog::edit_category_design']);
        $this->getRequest()->setDispatched(false);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('store', $requestData['store_id']);
        $this->getRequest()->setParam('id', $requestData['id']);
        $this->dispatch($uri);
        /** @var CategoryModel $category */
        $category = $this->categoryFactory->create();
        $category->load(2);
        $this->assertEquals('2columns-left', $category->getData('page_layout'));
        $this->assertEmpty($category->getData('custom_layout_update_file'));
        $this->assertEquals('test', $category->getData('description'));
        //No new error messages
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Save design attributes with default values without design permissions.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @return void
     * @throws \Throwable
     */
    public function testSaveDesignWithDefaults(): void
    {
        /** @var $store \Magento\Store\Model\Store */
        $store = Bootstrap::getObjectManager()->create(Store::class);
        $store->load('fixturestore', 'code');
        $storeId = $store->getId();
        /** @var CategoryModel $category */
        $category = $this->categoryFactory->create();
        $category->load(2);
        $attributes = $category->getAttributes();
        $attributes['custom_design']->setDefaultValue('1');
        $attributes['custom_design']->save();
        $requestData = [
            'name' => 'Test name',
            'parent_id' => '2',
            'is_active' => '0',
            'description' => 'Custom Description',
            'meta_title' => 'Custom Title',
            'meta_keywords' => 'Custom keywords',
            'meta_description' => 'Custom meta description',
            'include_in_menu' => '0',
            'url_key' => 'default-test-category-test',
            'display_mode' => 'PRODUCTS',
            'landing_page' => '1',
            'is_anchor' => true,
            'store_id' => $storeId,
            'use_config' => [
                'available_sort_by' => 1,
                'default_sort_by' => 1,
                'filter_price_range' => 1,
            ],
            'custom_design' => '1',
            'custom_apply_to_products' => '0'
        ];
        $uri = 'backend/catalog/category/save';

        //Updating the category's design settings without proper permissions.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_category_design');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('store', $requestData['store_id']);
        $this->dispatch($uri);

        //Verifying that category was saved.
        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $id = $registry->registry('current_category')->getId();
        /** @var CategoryModel $category */
        $category = $this->categoryFactory->create();
        $category->load($id);
        $this->assertNotEmpty($category->getId());
        $this->assertEquals('1', $category->getData('custom_design'));
    }

    /**
     * Test custom update files functionality.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @throws \Throwable
     * @return void
     */
    public function testSaveCustomLayout(): void
    {
        $file = 'test_file';
        /** @var $store \Magento\Store\Model\Store */
        $store = Bootstrap::getObjectManager()->create(Store::class);
        /** @var CategoryLayoutUpdateManager $layoutManager */
        $layoutManager = Bootstrap::getObjectManager()->get(CategoryLayoutUpdateManager::class);
        $layoutManager->setCategoryFakeFiles(2, [$file]);
        $store->load('fixturestore', 'code');
        $storeId = $store->getId();
        $requestData = [
            'id' => '2',
            'entity_id' => '2',
            'path' => '1/2',
            'name' => 'Custom Name',
            'is_active' => '0',
            'description' => 'Custom Description',
            'meta_title' => 'Custom Title',
            'meta_keywords' => 'Custom keywords',
            'meta_description' => 'Custom meta description',
            'include_in_menu' => '0',
            'url_key' => 'default-test-category',
            'display_mode' => 'PRODUCTS',
            'landing_page' => '1',
            'is_anchor' => true,
            'store_id' => $storeId,
            'use_config' => [
                'available_sort_by' => 1,
                'default_sort_by' => 1,
                'filter_price_range' => 1,
            ],
        ];
        $uri = 'backend/catalog/category/save';

        //Saving a wrong file
        $requestData['custom_layout_update_file'] = $file . 'INVALID';
        $this->getRequest()->setDispatched(false);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('store', $requestData['store_id']);
        $this->getRequest()->setParam('id', $requestData['id']);
        $this->dispatch($uri);

        //Checking that the value is not saved
        /** @var CategoryModel $category */
        $category = $this->categoryFactory->create();
        $category->load($requestData['entity_id']);
        $this->assertEmpty($category->getData('custom_layout_update_file'));

        //Saving the correct file
        $requestData['custom_layout_update_file'] = $file;
        $this->getRequest()->setDispatched(false);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('store', $requestData['store_id']);
        $this->getRequest()->setParam('id', $requestData['id']);
        $this->dispatch($uri);

        //Checking that the value is saved
        /** @var CategoryModel $category */
        $category = $this->categoryFactory->create();
        $category->load($requestData['entity_id']);
        $this->assertEquals($file, $category->getData('custom_layout_update_file'));
    }

    /**
     * Verify that the category cannot be saved if the category url matches the admin url.
     *
     * @return void
     * @magentoConfigFixture admin/url/use_custom_path 1
     * @magentoConfigFixture admin/url/custom_path backend
     */
    public function testSaveWithCustomBackendNameAction(): void
    {
        /** @var FrontNameResolver $frontNameResolver */
        $frontNameResolver = Bootstrap::getObjectManager()->create(FrontNameResolver::class);
        $urlKey = $frontNameResolver->getFrontName();
        $inputData = [
            'id' => '2',
            'url_key' => $urlKey,
            'use_config' => [
                'available_sort_by' => 1,
                'default_sort_by' => 1
            ]
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($inputData);
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'URL key "backend" matches a reserved endpoint name '
                    . '(admin, soap, rest, graphql, standard, backend). Use another URL key.'
                ]
            ),
            MessageInterface::TYPE_ERROR
        );
    }
}
