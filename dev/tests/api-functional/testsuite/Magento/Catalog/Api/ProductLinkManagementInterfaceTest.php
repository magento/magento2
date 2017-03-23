<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @magentoAppIsolation enabled
 */
class ProductLinkManagementInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductLinkManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_crosssell.php
     */
    public function testGetLinkedProductsCrossSell()
    {
        $productSku = 'simple_with_cross';
        $linkType = 'crosssell';

        $this->assertLinkedProducts($productSku, $linkType);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related.php
     */
    public function testGetLinkedProductsRelated()
    {
        $productSku = 'simple_with_cross';
        $linkType = 'related';

        $this->assertLinkedProducts($productSku, $linkType);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_upsell.php
     */
    public function testGetLinkedProductsUpSell()
    {
        $productSku = 'simple_with_upsell';
        $linkType = 'upsell';

        $this->assertLinkedProducts($productSku, $linkType);
    }

    /**
     * @param string $productSku
     * @param int $linkType
     */
    protected function assertLinkedProducts($productSku, $linkType)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/links/' . $linkType,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetLinkedItemsByType',
            ],
        ];

        $actual = $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'type' => $linkType]);

        $this->assertEquals('simple', $actual[0]['linked_product_type']);
        $this->assertEquals('simple', $actual[0]['linked_product_sku']);
        $this->assertEquals(1, $actual[0]['position']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual_in_stock.php
     */
    public function testAssign()
    {
        $linkType = 'related';
        $productSku = 'simple';
        $linkData = [
            'linked_product_type' => 'virtual',
            'linked_product_sku' => 'virtual-product',
            'position' => 100,
            'sku' => 'simple',
            'link_type' => 'related',
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/links',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'SetProductLinks',
            ],
        ];

        $arguments = [
            'sku' => $productSku,
            'items' => [$linkData],
            'type' => $linkType,
        ];

        $this->_webApiCall($serviceInfo, $arguments);
        $actual = $this->getLinkedProducts($productSku, 'related');
        array_walk($actual, function (&$item) {
            $item = $item->__toArray();
        });
        $this->assertEquals([$linkData], $actual);
    }

    /**
     * Get list of linked products
     *
     * @param string $sku
     * @param string $linkType
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     */
    protected function getLinkedProducts($sku, $linkType)
    {
        /** @var \Magento\Catalog\Model\ProductLink\Management $linkManagement */
        $linkManagement = $this->objectManager->get(\Magento\Catalog\Api\ProductLinkManagementInterface::class);
        $linkedProducts = $linkManagement->getLinkedItemsByType($sku, $linkType);

        return $linkedProducts;
    }
}
