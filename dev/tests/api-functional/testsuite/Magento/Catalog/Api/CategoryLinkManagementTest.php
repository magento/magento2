<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Represents CategoryLinkManagementTest Class
 */
class CategoryLinkManagementTest extends WebapiAbstract
{
    const SERVICE_WRITE_NAME = 'catalogCategoryLinkManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH_SUFFIX = '/V1/categories';
    const RESOURCE_PATH_PREFIX = 'products';

    private $modelId = 333;

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testAssignedProducts()
    {
        $expected = [
            [
                'sku' => 'simple333',
                'position' => '0',
                'category_id' => '333',
            ],
        ];
        $result = $this->getAssignedProducts($this->modelId);

        $this->assertEquals($expected, $result);
    }

    public function testInfoNoSuchEntityException()
    {
        try {
            $this->getAssignedProducts(-1);
        } catch (\Exception $e) {
            $this->assertStringContainsString('No such entity with %fieldName = %fieldValue', $e->getMessage());
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testDuplicatedProductsInChildCategories()
    {
        $result = $this->getAssignedProducts(3, 'all');
        $this->assertCount(3, $result);
    }

    /**
     * @param int $id category id
     * @param string|null $storeCode
     * @return array|string
     */
    private function getAssignedProducts(int $id, ?string $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_SUFFIX . '/' . $id . '/' . self::RESOURCE_PATH_PREFIX,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_WRITE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_WRITE_NAME . 'GetAssignedProducts',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['categoryId' => $id], null, $storeCode);
    }
}
