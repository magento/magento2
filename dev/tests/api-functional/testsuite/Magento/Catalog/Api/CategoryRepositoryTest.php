<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\UrlRewrite\Model\Storage\DbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Test repository web API.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepositoryTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/categories';
    const SERVICE_NAME = 'catalogCategoryRepositoryV1';

    private $modelId = 333;

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @var AdminTokenServiceInterface
     */
    private $adminTokens;

    /**
     * @var string[]
     */
    private $createdCategories;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->roleFactory = Bootstrap::getObjectManager()->get(RoleFactory::class);
        $this->rulesFactory = Bootstrap::getObjectManager()->get(RulesFactory::class);
        $this->adminTokens = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_backend.php
     */
    public function testGet()
    {
        $expected = [
            'parent_id' => 2,
            'path' => '1/2/333',
            'position' => 1,
            'level' => 2,
            'available_sort_by' => ['position', 'name'],
            'include_in_menu' => true,
            'name' => 'Category 1',
            'id' => 333,
            'is_active' => true,
        ];

        $result = $this->getInfoCategory($this->modelId);

        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('children', $result);
        $this->assertArrayHasKey('custom_attributes', $result);
        unset($result['created_at'], $result['updated_at'], $result['children'], $result['custom_attributes']);
        ksort($expected);
        ksort($result);
        $this->assertEquals($expected, $result);
    }

    public function testInfoNoSuchEntityException()
    {
        try {
            $this->getInfoCategory(-1);
        } catch (\Exception $e) {
            $this->assertStringContainsString('No such entity with %fieldName = %fieldValue', $e->getMessage());
        }
    }

    /**
     * Load category data.
     *
     * @param int $id
     * @return array
     */
    protected function getInfoCategory($id)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['categoryId' => $id]);
    }

    /**
     * Test for create category process
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Category/_files/service_category_create.php
     */
    public function testCreate()
    {
        $categoryData = $this->getSimpleCategoryData(['name' => 'Test Category Name']);
        $result = $this->createCategory($categoryData);
        $this->assertGreaterThan(0, $result['id']);
        foreach (['name', 'parent_id', 'available_sort_by'] as $fieldName) {
            $this->assertEquals(
                $categoryData[$fieldName],
                $result[$fieldName],
                sprintf('"%s" field value is invalid', $fieldName)
            );
        }
        $this->createdCategories = [$result['id']];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testDelete()
    {
        /** @var DbStorage $storage */
        $storage = Bootstrap::getObjectManager()->get(DbStorage::class);
        $categoryId = $this->modelId;
        $data = [
            UrlRewrite::ENTITY_ID => $categoryId,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE
        ];
        /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $urlRewrite */
        $urlRewrite = $storage->findOneByData($data);

        // Assert that a url rewrite is auto-generated for the category created from the data fixture
        $this->assertEquals(1, $urlRewrite->getIsAutogenerated());
        $this->assertEquals($categoryId, $urlRewrite->getEntityId());
        $this->assertEquals(CategoryUrlRewriteGenerator::ENTITY_TYPE, $urlRewrite->getEntityType());
        $this->assertEquals('category-1.html', $urlRewrite->getRequestPath());

        // Assert deleting category is successful
        $this->assertTrue($this->deleteCategory($this->modelId));
        // After the category is deleted, assert that the associated url rewrite is also auto-deleted
        $this->assertNull($storage->findOneByData($data));
    }

    public function testDeleteNoSuchEntityException()
    {
        try {
            $this->deleteCategory(-1);
        } catch (\Exception $e) {
            $this->assertStringContainsString('No such entity with %fieldName = %fieldValue', $e->getMessage());
        }
    }

    /**
     * @dataProvider deleteSystemOrRootDataProvider
     *
     * @param int $categoryId
     * @param string $exceptionMsg
     * @return void
     */
    public function testDeleteSystemOrRoot(int $categoryId, string $exceptionMsg): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($exceptionMsg);

        $this->deleteCategory($categoryId);
    }

    /**
     * @return array
     */
    public function deleteSystemOrRootDataProvider(): array
    {
        return [
            'system_category' => [
                'category_id' => Category::TREE_ROOT_ID,
                'exception_message' => $this->buildExceptionMessage(Category::TREE_ROOT_ID),
            ],
            'root_category' => [
                'category_id' => 2,
                'exception_message' => $this->buildExceptionMessage(2),
            ],
        ];
    }

    /**
     * Build response error message
     *
     * @param int $categoryId
     * @return string
     */
    private function buildExceptionMessage(int $categoryId): string
    {
        $translatedMsg = (string)__('Cannot delete category with id %1');

        return TESTS_WEB_API_ADAPTER === self::ADAPTER_REST
            ? sprintf('{"message":"%s","parameters":["%u"]}', $translatedMsg, $categoryId)
            : $translatedMsg;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testUpdate()
    {
        $categoryId = 333;
        $categoryData = [
            'name' => 'Update Category Test',
            'is_active' => false,
            'custom_attributes' => [
                [
                    'attribute_code' => 'description',
                    'value' => "Update Category Description Test",
                ],
            ],
        ];
        $result = $this->updateCategory($categoryId, $categoryData);
        $this->assertEquals($categoryId, $result['id']);
        /** @var Category $model */
        $model = Bootstrap::getObjectManager()->get(Category::class);
        $category = $model->load($categoryId);
        $this->assertFalse((bool)$category->getIsActive(), 'Category "is_active" must equal to false');
        $this->assertEquals("Update Category Test", $category->getName());
        $this->assertEquals("Update Category Description Test", $category->getDescription());
        $this->createdCategories = [$categoryId];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testUpdateWithDefaultSortByAttribute()
    {
        $categoryId = 333;
        $categoryData = [
            'name' => 'Update Category Test With default_sort_by Attribute',
            'is_active' => true,
            "available_sort_by" => [],
            'custom_attributes' => [
                [
                    'attribute_code' => 'default_sort_by',
                    'value' => ["name"],
                ],
            ],
        ];
        $result = $this->updateCategory($categoryId, $categoryData);
        $this->assertEquals($categoryId, $result['id']);
        /** @var Category $model */
        $model = Bootstrap::getObjectManager()->get(Category::class);
        $category = $model->load($categoryId);
        $this->assertTrue((bool)$category->getIsActive(), 'Category "is_active" must equal to true');
        $this->assertEquals("Update Category Test With default_sort_by Attribute", $category->getName());
        $this->assertEquals("name", $category->getDefaultSortBy());
        $this->createdCategories = [$categoryId];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testUpdateUrlKey()
    {
        $this->_markTestAsRestOnly('Functionality available in REST mode only.');

        $categoryId = 333;
        $categoryData = [
            'name' => 'Update Category Test Old Name',
            'custom_attributes' => [
                [
                    'attribute_code' => 'url_key',
                    'value' => "Update Category Test Old Name",
                ],
            ],
        ];
        $result = $this->updateCategory($categoryId, $categoryData);
        $this->assertEquals($categoryId, $result['id']);

        $categoryData = [
            'name' => 'Update Category Test New Name',
            'custom_attributes' => [
                [
                    'attribute_code' => 'url_key',
                    'value' => "Update Category Test New Name",
                ],
                [
                    'attribute_code' => 'save_rewrites_history',
                    'value' => 1,
                ],
            ],
        ];
        $result = $this->updateCategory($categoryId, $categoryData);
        $this->assertEquals($categoryId, $result['id']);
        /** @var Category $model */
        $model = Bootstrap::getObjectManager()->get(Category::class);
        $category = $model->load($categoryId);
        $this->assertEquals("Update Category Test New Name", $category->getName());

        // check for the url rewrite for the new name
        $storage = Bootstrap::getObjectManager()->get(DbStorage::class);
        $data = [
            UrlRewrite::ENTITY_ID => $categoryId,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::REDIRECT_TYPE => 0,
        ];

        $urlRewrite = $storage->findOneByData($data);

        // Assert that a url rewrite is auto-generated for the category created from the data fixture
        $this->assertNotNull($urlRewrite);
        $this->assertEquals(1, $urlRewrite->getIsAutogenerated());
        $this->assertEquals($categoryId, $urlRewrite->getEntityId());
        $this->assertEquals(CategoryUrlRewriteGenerator::ENTITY_TYPE, $urlRewrite->getEntityType());
        $this->assertEquals('update-category-test-new-name.html', $urlRewrite->getRequestPath());

        // check for the forward from the old name to the new name
        $storage = Bootstrap::getObjectManager()->get(DbStorage::class);
        $data = [
            UrlRewrite::ENTITY_ID => $categoryId,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::REDIRECT_TYPE => 301,
        ];

        $urlRewrite = $storage->findOneByData($data);

        $this->assertNotNull($urlRewrite);
        $this->assertEquals(0, $urlRewrite->getIsAutogenerated());
        $this->assertEquals($categoryId, $urlRewrite->getEntityId());
        $this->assertEquals(CategoryUrlRewriteGenerator::ENTITY_TYPE, $urlRewrite->getEntityType());
        $this->assertEquals('update-category-test-old-name.html', $urlRewrite->getRequestPath());

        $this->deleteCategory($categoryId);
    }

    protected function getSimpleCategoryData($categoryData = [])
    {
        return [
            'parent_id' => '2',
            'name' => isset($categoryData['name'])
                ? $categoryData['name'] : uniqid('Category-', true),
            'is_active' => '1',
            'include_in_menu' => '1',
            'available_sort_by' => ['position', 'name'],
            'custom_attributes' => [
                ['attribute_code' => 'url_key', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Custom description'],
                ['attribute_code' => 'meta_title', 'value' => ''],
                ['attribute_code' => 'meta_keywords', 'value' => ''],
                ['attribute_code' => 'meta_description', 'value' => ''],
                ['attribute_code' => 'display_mode', 'value' => 'PRODUCTS'],
                ['attribute_code' => 'landing_page', 'value' => '0'],
                ['attribute_code' => 'is_anchor', 'value' => '0'],
                ['attribute_code' => 'custom_use_parent_settings', 'value' => '0'],
                ['attribute_code' => 'custom_apply_to_products', 'value' => '0'],
                ['attribute_code' => 'custom_design', 'value' => ''],
                ['attribute_code' => 'custom_design_from', 'value' => ''],
                ['attribute_code' => 'custom_design_to', 'value' => ''],
                ['attribute_code' => 'page_layout', 'value' => ''],
            ]
        ];
    }

    /**
     * Create category process
     *
     * @param array $category
     * @param string|null $token
     * @return array
     */
    protected function createCategory(array $category, ?string $token = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if ($token) {
            $serviceInfo['rest']['token'] = $serviceInfo['soap']['token'] = $token;
        }
        $requestData = ['category' => $category];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    protected function deleteCategory($id)
    {
        $serviceInfo =
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => 'V1',
                    'operation' => self::SERVICE_NAME . 'DeleteByIdentifier',
                ],
            ];
        return $this->_webApiCall($serviceInfo, ['categoryId' => $id]);
    }

    /**
     * Update given category via web API.
     *
     * @param int $id
     * @param array $data
     * @param string|null $token
     * @return array
     */
    protected function updateCategory($id, $data, ?string $token = null)
    {
        $serviceInfo =
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => 'V1',
                    'operation' => self::SERVICE_NAME . 'Save',
                ],
            ];
        if ($token) {
            $serviceInfo['rest']['token'] = $serviceInfo['soap']['token'] = $token;
        }
        $data['id'] = $id;

        return $this->_webApiCall($serviceInfo, ['id' => $id, 'category' => $data]);
    }

    /**
     * Update admin role resources list.
     *
     * @param string $roleName
     * @param string[] $resources
     * @return void
     */
    private function updateRoleResources(string $roleName, array $resources): void
    {
        /** @var Role $role */
        $role = $this->roleFactory->create();
        $role->load($roleName, 'role_name');
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources($resources);
        $rules->saveRel();
    }

    /**
     * Extract error returned by the server.
     *
     * @param \Throwable $exception
     * @return string
     */
    private function extractCallExceptionMessage(\Throwable $exception): string
    {
        if ($restResponse = json_decode($exception->getMessage(), true)) {
            //REST
            return $restResponse['message'];
        } else {
            //SOAP
            return $exception->getMessage();
        }
    }

    /**
     * Test design settings authorization
     *
     * @magentoApiDataFixture Magento/User/_files/user_with_custom_role.php
     * @throws \Throwable
     * @return void
     */
    public function testSaveDesign(): void
    {
        //Updating our admin user's role to allow saving categories but not their design settings.
        $roleName = 'test_custom_role';
        $this->updateRoleResources($roleName, ['Magento_Catalog::categories']);
        //Using the admin user with custom role.
        $token = $this->adminTokens->createAdminAccessToken(
            'customRoleUser',
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $categoryData = $this->getSimpleCategoryData();
        $categoryData['custom_attributes'][] = ['attribute_code' => 'custom_layout_update_file', 'value' => 'test'];

        //Creating new category with design settings.
        $exceptionMessage = null;
        try {
            $this->createCategory($categoryData, $token);
        } catch (\Throwable $exception) {
            $exceptionMessage = $this->extractCallExceptionMessage($exception);
        }
        //We don't have the permissions.
        $this->assertEquals('Not allowed to edit the category\'s design attributes', $exceptionMessage);

        //Updating the user role to allow access to design properties.
        $this->updateRoleResources($roleName, ['Magento_Catalog::categories', 'Magento_Catalog::edit_category_design']);
        //Making the same request with design settings.
        $categoryData = $this->getSimpleCategoryData();
        foreach ($categoryData['custom_attributes'] as &$attribute) {
            if ($attribute['attribute_code'] === 'custom_design') {
                $attribute['value'] = 'test';
                break;
            }
        }
        $result = $this->createCategory($categoryData, $token);
        $this->assertArrayHasKey('id', $result);
        //Category must be saved.
        $categorySaved = $this->getInfoCategory($result['id']);
        $savedCustomDesign = null;
        foreach ($categorySaved['custom_attributes'] as $customAttribute) {
            if ($customAttribute['attribute_code'] === 'custom_design') {
                $savedCustomDesign = $customAttribute['value'];
                break;
            }
        }
        $this->assertEquals('test', $savedCustomDesign);
        $categoryData = $categorySaved;

        //Updating our role to remove design properties access.
        $this->updateRoleResources($roleName, ['Magento_Catalog::categories']);
        //Updating the category but with the same design properties values.
        //Omitting existing design attribute and keeping it's existing value
        $attributes = $categoryData['custom_attributes'];
        foreach ($attributes as $index => $attrData) {
            if ($attrData['attribute_code'] === 'custom_design') {
                unset($categoryData['custom_attributes'][$index]);
                break;
            }
        }
        unset($attributes, $index, $attrData);
        $result = $this->updateCategory($categoryData['id'], $categoryData, $token);
        //We haven't changed the design so operation is successful.
        $this->assertArrayHasKey('id', $result);

        //Changing a design property.
        $categoryData['custom_attributes'][] = ['attribute_code' => 'custom_design', 'value' => 'test2'];
        $exceptionMessage = null;
        try {
            $this->updateCategory($categoryData['id'], $categoryData, $token);
        } catch (\Throwable $exception) {
            $exceptionMessage = $this->extractCallExceptionMessage($exception);
        }
        //We don't have permissions to do that.
        $this->assertEquals('Not allowed to edit the category\'s design attributes', $exceptionMessage);
        $this->createdCategories = [$result['id']];
    }

    /**
     * Check if repository does not override default values for attributes out of request
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testUpdateScopeAttribute()
    {
        $categoryId = 333;
        $categoryData = [
            'name' => 'Scope Specific Value',
        ];
        $result = $this->updateCategoryForSpecificStore($categoryId, $categoryData);
        $this->assertEquals($categoryId, $result['id']);

        /** @var Category $model */
        $model = Bootstrap::getObjectManager()->get(Category::class);
        $category = $model->load($categoryId);

        /** @var ScopeOverriddenValue $scopeOverriddenValue */
        $scopeOverriddenValue = Bootstrap::getObjectManager()->get(ScopeOverriddenValue::class);
        self::assertTrue($scopeOverriddenValue->containsValue(
            CategoryInterface::class,
            $category,
            'name',
            Store::DISTRO_STORE_ID
        ), 'Name is not saved for specific store');
        self::assertFalse($scopeOverriddenValue->containsValue(
            CategoryInterface::class,
            $category,
            'is_active',
            Store::DISTRO_STORE_ID
        ), 'is_active is overridden for default store');
        self::assertFalse($scopeOverriddenValue->containsValue(
            CategoryInterface::class,
            $category,
            'url_key',
            Store::DISTRO_STORE_ID
        ), 'url_key is overridden for default store');

        $this->deleteCategory($categoryId);
    }

    /**
     * Update given category via web API for specific store code.
     *
     * @param int $id
     * @param array $data
     * @param string|null $token
     * @param string $storeCode
     * @return array
     */
    protected function updateCategoryForSpecificStore(
        int $id,
        array $data,
        ?string $token = null,
        string $storeCode = 'default'
    ) {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if ($token) {
            $serviceInfo['rest']['token'] = $serviceInfo['soap']['token'] = $token;
        }
        $data['id'] = $id;

        return $this->_webApiCall($serviceInfo, ['id' => $id, 'category' => $data], null, $storeCode);
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function tearDown(): void
    {
        if (!empty($this->createdCategories)) {
            // delete category to clean up auto-generated url rewrites
            foreach ($this->createdCategories as $categoryId) {
                $this->deleteCategory($categoryId);
            }
        }

        parent::tearDown();
    }
}
