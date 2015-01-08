<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config;

class CategoryRepositoryTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/categories';
    const SERVICE_NAME = 'catalogCategoryRepositoryV1';

    private $modelId = 333;

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_backend.php
     */
    public function testGet()
    {
        $expected = [
            'parent_id' => 2,
            'path' => '1/2/3',
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
        unset($result['created_at'], $result['updated_at'], $result['children']);
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
     * @param int $id
     * @return string
     */
    protected function getInfoCategory($id)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                'httpMethod' => Config::HTTP_METHOD_GET,
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
     * @return array
     */
    public function categoryCreationProvider()
    {
        return [
            [
                $this->getSimpleCategoryData(
                    [
                        'name' => 'Test Category Name',
                    ]
                ),
            ]
        ];
    }

    /**
     * Test for create category process
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Category/_files/service_category_create.php
     * @dataProvider categoryCreationProvider
     */
    public function testCreate($category)
    {
        $category = $this->createCategory($category);
        $this->assertGreaterThan(0, $category['id']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testDelete()
    {
        $this->assertTrue($this->deleteCategory($this->modelId));
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
        $categoryId = 333;
        $categoryData = [
            'name' => "Update Category Test",
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
        $model = Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Category');
        $category = $model->load($categoryId);
        $this->assertEquals("Update Category Test", $category->getName());
        $this->assertEquals("Update Category Description Test", $category->getDescription());
    }

    protected function getSimpleCategoryData($categoryData = [])
    {
        return [
            'path' => '2',
            'parent_id' => '2',
            'name' => isset($categoryData['name'])
                ? $categoryData['name'] : uniqid('Category-', true),
            'is_active' => '1',
            'custom_attributes' => [
                ['attribute_code' => 'url_key', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Custom description'],
                ['attribute_code' => 'meta_title', 'value' => ''],
                ['attribute_code' => 'meta_keywords', 'value' => ''],
                ['attribute_code' => 'meta_description', 'value' => ''],
                ['attribute_code' => 'include_in_menu', 'value' => '1'],
                ['attribute_code' => 'display_mode', 'value' => 'PRODUCTS'],
                ['attribute_code' => 'landing_page', 'value' => ''],
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
     * @param  $category
     * @return int
     */
    protected function createCategory($category)
    {
        $serviceInfo = [
            'rest' => ['resourcePath' => self::RESOURCE_PATH, 'httpMethod' => Config::HTTP_METHOD_POST],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
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
                    'httpMethod' => Config::HTTP_METHOD_DELETE,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => 'V1',
                    'operation' => self::SERVICE_NAME . 'DeleteByIdentifier',
                ],
            ];
        return $this->_webApiCall($serviceInfo, ['categoryId' => $id]);
    }

    protected function updateCategory($id, $data)
    {
        $serviceInfo =
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                    'httpMethod' => Config::HTTP_METHOD_PUT,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => 'V1',
                    'operation' => self::SERVICE_NAME . 'Save',
                ],
            ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $data['id'] = $id;
            return $this->_webApiCall($serviceInfo, ['id' => $id, 'category' => $data]);
        } else {
            return $this->_webApiCall($serviceInfo, ['category' => $data]);
        }
    }
}
