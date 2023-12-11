<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Model\ProductLink\Link;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class checks product relations functionality
 *
 * @see \Magento\Catalog\Api\ProductLinkRepositoryInterface
 */
class ProductLinkRepositoryInterfaceTest extends WebapiAbstract
{
    /**
     * @var string
     */
    const SERVICE_NAME = 'catalogProductLinkRepositoryV1';

    /**
     * @var string
     */
    const SERVICE_VERSION = 'V1';

    /**
     * @var string
     */
    const RESOURCE_PATH = '/V1/products/';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductLinkManagementInterface
     */
    private $linkManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->linkManagement = $this->objectManager->get(ProductLinkManagementInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related_multiple.php
     *
     * @return void
     */
    public function testDelete(): void
    {
        $productSku = 'simple_with_cross';
        $linkType = 'related';
        $this->deleteApiCall($productSku, $linkType, 'simple');
        $linkedProducts = $this->linkManagement->getLinkedItemsByType($productSku, $linkType);
        $this->assertCount(1, $linkedProducts);
        $product = current($linkedProducts);
        $this->assertEquals('simple_with_cross_two', $product->getLinkedProductSku());
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     *
     * @return void
     */
    public function testDeleteNotExistedProductLink(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage((string)__("Product %1 doesn't have linked %2 as %3"));
        $this->deleteApiCall('simple', 'related', 'not_exists_product');
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related.php
     *
     * @return void
     */
    public function testSave(): void
    {
        $productSku = 'simple_with_cross';
        $linkType = 'related';
        $data = [
            'entity' => [
                Link::KEY_SKU => 'simple_with_cross',
                Link::KEY_LINK_TYPE => 'related',
                Link::KEY_LINKED_PRODUCT_SKU => 'simple',
                Link::KEY_LINKED_PRODUCT_TYPE => 'simple',
                Link::KEY_POSITION => 1000,
            ],
        ];
        $this->saveApiCall($productSku, $data);
        $actual = $this->linkManagement->getLinkedItemsByType($productSku, $linkType);
        $this->assertCount(1, $actual, 'Invalid actual linked products count');
        $this->assertEquals(1000, $actual[0]->getPosition(), 'Product position is not updated');
    }

    /**
     * Get service info for api call
     *
     * @param string $resourcePath
     * @param string $httpMethod
     * @param string $operation
     * @return array
     */
    private function getServiceInfo(string $resourcePath, string $httpMethod, string $operation): array
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $resourcePath,
                'httpMethod' => $httpMethod,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . $operation,
            ],
        ];
    }

    /**
     * Make api call to delete product link
     *
     * @param string $productSku
     * @param string $linkType
     * @param string $linkedSku
     * @return array|int|string|float|bool
     */
    private function deleteApiCall(string $productSku, string $linkType, string $linkedSku)
    {
        $serviceInfo = $this->getServiceInfo(
            $productSku . '/links/' . $linkType . '/' . $linkedSku,
            Request::HTTP_METHOD_DELETE,
            'DeleteById'
        );

        return $this->_webApiCall(
            $serviceInfo,
            [
                'sku' => $productSku,
                'type' => $linkType,
                'linkedProductSku' => $linkedSku,
            ]
        );
    }

    /**
     * Make api call to save product link
     *
     * @param string $productSku
     * @param array $data
     * @return array|bool|float|int|string
     */
    private function saveApiCall(string $productSku, array $data)
    {
        $serviceInfo = $this->getServiceInfo(
            $productSku . '/links',
            Request::HTTP_METHOD_PUT,
            'Save'
        );

        return $this->_webApiCall($serviceInfo, $data);
    }
}
