<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config;

class CategoryLinkRepositoryTest extends WebapiAbstract
{
    const SERVICE_WRITE_NAME = 'catalogCategoryLinkRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH_SUFFIX = '/V1/categories';
    const RESOURCE_PATH_PREFIX = 'products';

    private $categoryId = 333;

    /**
     * @dataProvider saveDataProvider
     * @magentoApiDataFixture Magento/Catalog/_files/products_in_category.php
     * @param int $productId
     * @param string[] $productLink
     * @param int $productPosition
     */
    public function testSave($productLink, $productId, $productPosition = 0)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_SUFFIX
                    . '/' . $this->categoryId . '/' . self::RESOURCE_PATH_PREFIX,
                'httpMethod' => Config::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_WRITE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_WRITE_NAME . 'Save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['productLink' => $productLink]);
        $this->assertTrue($result);
        $this->assertTrue($this->isProductInCategory($this->categoryId, $productId, $productPosition));
    }

    public function saveDataProvider()
    {
        return [
            [
                ['sku' => 'simple_with_cross', 'position' => 7, 'category_id' => $this->categoryId],
                334,
                7,
            ],
            [
                ['sku' => 'simple_with_cross', 'category_id' => $this->categoryId],
                334,
                0
            ],
        ];
    }

    /**
     * @dataProvider updateProductProvider
     * @magentoApiDataFixture Magento/Catalog/_files/products_in_category.php
     * @param int $productId
     * @param string[] $productLink
     * @param int $productPosition
     */
    public function testUpdateProduct($productLink, $productId, $productPosition = 0)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_SUFFIX
                    . '/' . $this->categoryId . '/' . self::RESOURCE_PATH_PREFIX,
                'httpMethod' => Config::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_WRITE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_WRITE_NAME . 'Save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['productLink' => $productLink]);
        $this->assertTrue($result);
        $this->assertFalse($this->isProductInCategory($this->categoryId, $productId, $productPosition));
    }

    public function updateProductProvider()
    {
        return [
            [
                ['sku' => 'simple_with_cross', 'position' => 7, 'categoryId' => $this->categoryId],
                333,
                4,
            ],
            [
                ['sku' => 'simple_with_cross', 'categoryId' => $this->categoryId],
                333,
                0
            ],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_in_category.php
     */
    public function testDelete()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_SUFFIX . '/' . $this->categoryId .
                    '/' . self::RESOURCE_PATH_PREFIX . '/simple',
                'httpMethod' => Config::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_WRITE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_WRITE_NAME . 'DeleteByIds',
            ],
        ];
        $result = $this->_webApiCall(
            $serviceInfo,
            ['productSku' => 'simple', 'categoryId' => $this->categoryId]
        );
        $this->assertTrue($result);
        $this->assertFalse($this->isProductInCategory($this->categoryId, 333, 10));
    }

    /**
     * @param int $categoryId
     * @param int $productId
     * @param int $productPosition
     * @return bool
     */
    private function isProductInCategory($categoryId, $productId, $productPosition)
    {
        /** @var \Magento\Catalog\Api\CategoryRepositoryInterface $categoryLoader */
        $categoryLoader = Bootstrap::getObjectManager()->create('Magento\Catalog\Api\CategoryRepositoryInterface');
        $category = $categoryLoader->get($categoryId);
        $productsPosition = $category->getProductsPosition();

        if (isset($productsPosition[$productId]) && $productsPosition[$productId] == $productPosition) {
            return true;
        } else {
            return false;
        }
    }
}
