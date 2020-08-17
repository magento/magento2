<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests CategoryManagement
 */
class CategoryManagementTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/categories';

    const SERVICE_NAME = 'catalogCategoryManagementV1';

    /**
     * Tests getTree operation
     *
     * @dataProvider treeDataProvider
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testTree($rootCategoryId, $depth, $expected)
    {
        $requestData = ['rootCategoryId' => $rootCategoryId, 'depth' => $depth];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'GetTree'
            ]
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $expected = array_replace_recursive($result, $expected);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function treeDataProvider(): array
    {
        return [
            [
                2,
                100,
                [
                    'id' => 2,
                    'name' => 'Default Category',
                    'children_data' => [
                        [
                            'id' => 400,
                            'name' => 'Category 1',
                            'children_data' => [
                                [
                                    'id' => 401,
                                    'name' => 'Category 1.1',
                                    'children_data' => [
                                        [
                                            'id' => 402,
                                            'name' => 'Category 1.1.1',
                                            'children_data' => [

                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                2,
                null,
                [
                    'id' => 2,
                    'name' => 'Default Category',
                    'children_data' => [
                        [
                            'id' => 400,
                            'name' => 'Category 1',
                            'children_data' => [
                                [
                                    'id' => 401,
                                    'name' => 'Category 1.1',
                                    'children_data' => [
                                        [
                                            'id' => 402,
                                            'name' => 'Category 1.1.1',
                                            'children_data' => [

                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                400,
                1,
                [
                    'id' => 400,
                    'name' => 'Category 1',
                    'children_data' => [
                        [
                            'id' => 401,
                            'name' => 'Category 1.1',
                            'children_data' => [

                            ]
                        ]
                    ]
                ]
            ],
            [
                400,
                0,
                [
                    'id' => 400,
                    'name' => 'Category 1',
                    'children_data' => [

                    ]
                ]
            ],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     * @dataProvider updateMoveDataProvider
     */
    public function testUpdateMove($categoryId, $parentId, $afterId, $expectedPosition)
    {
        $expectedPath = '1/2/400/' . $categoryId;
        $categoryData = ['categoryId' => $categoryId, 'parentId' => $parentId, 'afterId' => $afterId];
        $serviceInfo =
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH . '/' . $categoryId . '/move',
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => 'V1',
                    'operation' => self::SERVICE_NAME . 'Move'
                ]
            ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $categoryData));
        /** @var \Magento\Catalog\Model\Category $model */
        $readService = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $model = $readService->get($categoryId);
        $this->assertEquals($expectedPath, $model->getPath());
        $this->assertEquals($expectedPosition, $model->getPosition());
        $this->assertEquals($parentId, $model->getParentId());
    }

    public function updateMoveDataProvider()
    {
        return [
            [402, 400, null, 2],
            [402, 400, 401, 2],
            [402, 400, 999, 2],
            [402, 400, 0, 1]
        ];
    }
}
