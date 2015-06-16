<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Api;

use Magento\TestFramework\Helper\Bootstrap;

class ProductLinkRepositoryTest extends \Magento\TestFramework\TestCase\WebapiAbstract
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
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testSave()
    {
        $productSku = 'grouped-product';
        $linkType = 'associated';
        $productData = [
            'sku' => $productSku,
            'link_type' => $linkType,
            'linked_product_type' => 'simple',
            'linked_product_sku' => 'simple',
            'position' => 3,
            'extension_attributes' => [
                'qty' =>  (float) 300.0000,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/links',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['entity' => $productData]);

        /** @var \Magento\Catalog\Api\ProductLinkManagementInterface $linkManagement */
        $linkManagement = $this->objectManager->get('Magento\Catalog\Api\ProductLinkManagementInterface');
        $actual = $linkManagement->getLinkedItemsByType($productSku, $linkType);
        array_walk($actual, function (&$item) {
            $item = $item->__toArray();
        });
        $this->assertEquals($productData, $actual[2]);
    }
}
