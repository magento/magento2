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
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Test repository web API.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepositoryTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/categories';
    private const SERVICE_NAME = 'catalogCategoryRepositoryV1';
    private const FIXTURE_CATEGORY_ID = 333;
    private const FIXTURE_SECOND_STORE_CODE = 'fixture_second_store';
    private const STORE_CODE_GLOBAL = 'all';

    private $modelId = self::FIXTURE_CATEGORY_ID;

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
     * @inheritDoc
     */
    protected function setUp()
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
            'id' => self::FIXTURE_CATEGORY_ID,
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
            $this->assertContains('No such entity with %fieldName = %fieldValue', $e->getMessage());
        }
    }

    /**
     * Load category data.
     *
     * @param int $categoryId
     * @param string|null $storeCode
     * @return array
     */
    protected function getInfoCategory(int $categoryId, ?string $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $categoryId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['categoryId' => $categoryId], null, $storeCode);
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
        // delete category to clean up auto-generated url rewrites
        $this->deleteCategory($result['id']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testDelete()
    {
        /** @var \Magento\UrlRewrite\Model\Storage\DbStorage $storage */
        $storage = Bootstrap::getObjectManager()->get(\Magento\UrlRewrite\Model\Storage\DbStorage::class);
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

    /**
     * When update Category Attribute with `null` value - it should follow the default value (global one)
     *
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testCategoryUpdateWithStoreScopeNullValuesShouldFollowDefaultValue()
    {
        $newSecondStoreUrlKey = 'new-url-key';

        $this->updateCategoryCustomAttribute(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            $newSecondStoreUrlKey,
            self::FIXTURE_SECOND_STORE_CODE
        );

        $updatedSecondStoreCategory = $this->getInfoCategory(
            self::FIXTURE_CATEGORY_ID,
            self::FIXTURE_SECOND_STORE_CODE
        );

        // Verify `url_key` for Second Store was updated.
        $this->assertSame(
            $newSecondStoreUrlKey,
            $this->getCategoryAttributeValue($updatedSecondStoreCategory, 'url_key')
        );

        // Reset `url_key` for Second Store to `null` value (follow global value)
        $this->updateCategoryCustomAttribute(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            null,
            self::FIXTURE_SECOND_STORE_CODE
        );

        $newGlobalUrlKey = 'new-global-key';
        $this->updateCategoryCustomAttribute(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            $newGlobalUrlKey,
            self::STORE_CODE_GLOBAL
        );

        $revertedSecondStoreCategory = $this->getInfoCategory(
            self::FIXTURE_CATEGORY_ID,
            self::FIXTURE_SECOND_STORE_CODE
        );

        // Verify `url_key` for Second Store follows Global value.
        $this->assertSame(
            $newGlobalUrlKey,
            $this->getCategoryAttributeValue($revertedSecondStoreCategory, 'url_key')
        );
    }

    /**
     * Change of Category Name should not touch it's `url_key` attribute
     *
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testCategoryNameUpdateForStoreShouldNotStopUrlKeyFromFollowingGlobalValue()
    {
        $this->markTestSkipped('https://github.com/magento/magento2/issues/27065');

        $this->updateCategory(
            self::FIXTURE_CATEGORY_ID,
            ['name' => 'New Category Name'],
            null,
            self::FIXTURE_SECOND_STORE_CODE
        );

        $this->updateCategoryCustomAttribute(
            self::FIXTURE_CATEGORY_ID,
            'url_key',
            'new-url-key',
            self::STORE_CODE_GLOBAL
        );

        $categoryUpdatedInfo = $this->getInfoCategory(
            self::FIXTURE_CATEGORY_ID,
            self::FIXTURE_SECOND_STORE_CODE
        );

        // Expect that Store-level value was updated
        $this->assertSame(
            'new-url-key',
            $this->getCategoryAttributeValue($categoryUpdatedInfo, 'url_key')
        );
    }

    /**
     * Returns custom attribute value based on attribute key
     *
     * @param array $categoryData
     * @param string $attributeCode
     * @return string|null
     */
    private function getCategoryAttributeValue(array $categoryData, string $attributeCode): ?string
    {
        $attributes = $categoryData['custom_attributes'];

        if (isset($attributes['attribute_code'])) {
            return $attributes['value'];
        }

        foreach ($attributes as $attribute) {
            if ($attribute['attribute_code'] === $attributeCode) {
                return $attribute['value'];
            }
        }

        return null;
    }

    /**
     * Performs URL Key update
     *
     * @param int $categoryId
     * @param string $attributeCode
     * @param string|null $attributeValue
     * @param string|null $storeCode
     */
    private function updateCategoryCustomAttribute(
        int $categoryId,
        string $attributeCode,
        ?string $attributeValue,
        ?string $storeCode = 'all'
    ): void {
        $updateRequest = [
            'custom_attributes' => [
                [
                    'attribute_code' => $attributeCode,
                    'value' => $attributeValue
                ]
            ]
        ];

        $this->updateCategory($categoryId, $updateRequest, null, $storeCode);
    }

    public function testDeleteNoSuchEntityException()
    {
        try {
            $this->deleteCategory(-1);
        } catch (\Exception $e) {
            $this->assertContains('No such entity with %fieldName = %fieldValue', $e->getMessage());
        }
    }

    /**
     * @dataProvider deleteSystemOrRootDataProvider
     * @expectedException \Exception
     */
    public function testDeleteSystemOrRoot()
    {
        $this->deleteCategory($this->modelId);
    }

    public function deleteSystemOrRootDataProvider()
    {
        return [
            [\Magento\Catalog\Model\Category::TREE_ROOT_ID],
            [2] //Default root category
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testUpdate()
    {
        $categoryId = self::FIXTURE_CATEGORY_ID;
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
        /** @var \Magento\Catalog\Model\Category $model */
        $model = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\Category::class);
        $category = $model->load($categoryId);
        $this->assertFalse((bool)$category->getIsActive(), 'Category "is_active" must equal to false');
        $this->assertEquals("Update Category Test", $category->getName());
        $this->assertEquals("Update Category Description Test", $category->getDescription());
        // delete category to clean up auto-generated url rewrites
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
     * @param string|null $storeCode
     * @return array
     */
    protected function updateCategory($id, $data, ?string $token = null, ?string $storeCode = null)
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

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $data['id'] = $id;
            return $this->_webApiCall($serviceInfo, ['id' => $id, 'category' => $data], null, $storeCode);
        } else {
            $data['id'] = $id;
            return $this->_webApiCall($serviceInfo, ['id' => $id, 'category' => $data], null, $storeCode);
        }
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
     * @return void
     * @throws \Throwable
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
    }
}
