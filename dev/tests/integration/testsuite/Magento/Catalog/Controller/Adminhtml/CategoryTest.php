<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class CategoryTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @dataProvider saveActionDataProvider
     * @param array $inputData
     * @param array $defaultAttributes
     * @param array $attributesSaved
     * @param bool $isSuccess
     */
    public function testSaveAction($inputData, $defaultAttributes, $attributesSaved = [], $isSuccess = true)
    {
        /** @var $store \Magento\Store\Model\Store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');
        $storeId = $store->getId();

        $this->getRequest()->setPostValue($inputData);
        $this->getRequest()->setParam('store', $storeId);
        $this->getRequest()->setParam('id', 2);
        $this->dispatch('backend/catalog/category/save');

        if ($isSuccess) {
            $this->assertSessionMessages(
                $this->equalTo(['You saved the category.']),
                \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
            );
        }

        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $category->setStoreId($storeId);
        $category->load(2);

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
     * @param array $postData
     * @dataProvider categoryCreatedFromProductCreationPageDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSaveActionFromProductCreationPage($postData)
    {
        $this->getRequest()->setPostValue($postData);

        $this->dispatch('backend/catalog/category/save');
        $body = $this->getResponse()->getBody();

        if (empty($postData['return_session_messages_only'])) {
            $this->assertRedirect(
                $this->stringContains('http://localhost/index.php/backend/catalog/category/edit/')
            );
        } else {
            $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\Json\Helper\Data::class
            )->jsonDecode(
                $body
            );
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
     * @static
     * @return array
     */
    public static function categoryCreatedFromProductCreationPageDataProvider()
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

    public function testSuggestCategoriesActionDefaultCategoryFound()
    {
        $this->getRequest()->setParam('label_part', 'Default');
        $this->dispatch('backend/catalog/category/suggestCategories');
        $this->assertEquals(
            '[{"id":"2","children":[],"is_active":"1","label":"Default Category"}]',
            $this->getResponse()->getBody()
        );
    }

    public function testSuggestCategoriesActionNoSuggestions()
    {
        $this->getRequest()->setParam('label_part', strrev('Default'));
        $this->dispatch('backend/catalog/category/suggestCategories');
        $this->assertEquals('[]', $this->getResponse()->getBody());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function saveActionDataProvider()
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
                        'custom_layout_update' => 1,
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
                    'custom_layout_update' => '',
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
                    'custom_layout_update' => true,
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
                    'custom_layout_update' => null,
                    'custom_design_from' => '2015-05-21 00:00:00',
                    'custom_design_to' => '2015-05-29 00:00:00',
                    'filter_price_range' => null
                ],
            ],
            'incorrect datefrom' => [
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
                    'custom_design_from' => '5/29/2015',
                    'custom_design_to' => '5/21/2015',
                    'page_layout' => '',
                    'custom_layout_update' => '',
                    'use_config' => [
                        'available_sort_by' => 1,
                        'default_sort_by' => 1,
                        'filter_price_range' => 1,
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
                [],
                false
            ]
        ];
    }

    public function testSaveActionCategoryWithDangerRequest()
    {
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
            $this->equalTo(['The value of attribute "name" must be set']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @dataProvider moveActionDataProvider
     *
     * @param int $parentId
     * @param int $childId
     * @param string $childUrlKey
     * @param int $grandChildId
     * @param string $grandChildUrlKey
     * @param boolean $error
     */
    public function testMoveAction($parentId, $childId, $childUrlKey, $grandChildId, $grandChildUrlKey, $error)
    {
        $urlKeys = [
            $childId => $childUrlKey,
            $grandChildId => $grandChildUrlKey,
        ];
        foreach ($urlKeys as $categoryId => $urlKey) {
            /** @var $category \Magento\Catalog\Model\Category */
            $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Category::class
            );
            if ($categoryId > 0) {
                $category->load($categoryId)
                    ->setUrlKey($urlKey)
                    ->save();
            }
        }
        $this->getRequest()
            ->setPostValue('id', $grandChildId)
            ->setPostValue('pid', $parentId);
        $this->dispatch('backend/catalog/category/move');
        $jsonResponse = json_decode($this->getResponse()->getBody());
        $this->assertNotNull($jsonResponse);
        $this->assertEquals($error, $jsonResponse->error);
    }

    /**
     * @return array
     */
    public function moveActionDataProvider()
    {
        return [
            [400, 401, 'first_url_key', 402, 'second_url_key', false],
            [400, 401, 'duplicated_url_key', 402, 'duplicated_url_key', true],
            [0, 401, 'first_url_key', 402, 'second_url_key', true],
            [400, 401, 'first_url_key', 0, 'second_url_key', true],
        ];
    }
}
