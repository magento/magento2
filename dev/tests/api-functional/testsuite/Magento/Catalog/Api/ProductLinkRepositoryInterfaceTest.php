<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class ProductLinkRepositoryInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductLinkRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related_multiple.php
     * @magentoAppIsolation enabled
     */
    public function testDelete()
    {
        $productSku = 'simple_with_cross';
        $linkedSku = 'simple';
        $linkType = 'related';
        $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH . $productSku . '/links/' . $linkType . '/' . $linkedSku,
                    'httpMethod' => RestConfig::HTTP_METHOD_DELETE,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => self::SERVICE_VERSION,
                    'operation' => self::SERVICE_NAME . 'DeleteById',
                ],
            ],
            [
                'productSku' => $productSku,
                'type' => $linkType,
                'linkedProductSku' => $linkedSku
            ]
        );
        /** @var \Magento\Catalog\Model\ProductLink\Management $linkManagement */
        $linkManagement = $this->objectManager->create('Magento\Catalog\Api\ProductLinkManagementInterface');
        $linkedProducts = $linkManagement->getLinkedItemsByType($productSku, $linkType);
        $this->assertCount(1, $linkedProducts);
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $product */
        $product = current($linkedProducts);
        $this->assertEquals($product->getLinkedProductSku(), 'simple_with_cross_two');
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related.php
     */
    public function testSave()
    {
        $productSku = 'simple_with_cross';
        $linkType = 'related';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/links/' . $linkType,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $this->_webApiCall(
            $serviceInfo,
            [
                'entity' => [
                    'product_sku' => 'simple_with_cross',
                    'link_type' => 'related',
                    'linked_product_sku' => 'simple',
                    'linked_product_type' => 'simple',
                    'position' => 1000,
                ]
            ]
        );

        /** @var \Magento\Catalog\Model\ProductLink\Management $linkManagement */
        $linkManagement = $this->objectManager->get('Magento\Catalog\Api\ProductLinkManagementInterface');
        $actual = $linkManagement->getLinkedItemsByType($productSku, $linkType);
        $this->assertCount(1, $actual, 'Invalid actual linked products count');
        $this->assertEquals(1000, $actual[0]->getPosition(), 'Product position is not updated');
    }
}
