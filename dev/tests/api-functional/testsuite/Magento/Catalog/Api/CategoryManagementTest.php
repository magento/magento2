<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

class CategoryManagementTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/categories';

    const SERVICE_NAME = 'catalogCategoryManagementV1';

    /**
     * @dataProvider treeDataProvider
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testTree($rootCategoryId, $depth, $expectedLevel, $expectedId)
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

        for ($i = 0; $i < $expectedLevel; $i++) {
            if (!array_key_exists(0, $result['children_data'])) {
                $this->fail('Category "' . $result['name'] . '" doesn\'t have children but expected to have');
            }
            $result = $result['children_data'][0];
        }
        $this->assertEquals($expectedId, $result['id']);
        $this->assertEmpty($result['children_data']);
    }

    public function treeDataProvider()
    {
        return [
            [2, 100, 3, 402],
            [2, null, 3, 402],
            [400, 1, 1, 401],
            [401, 0, 0, 401],
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
