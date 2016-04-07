<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductMediaAttributeManagementTest extends WebapiAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/attribute_set_with_image_attribute.php
     */
    public function testGetList()
    {
        $attributeSetName = 'attribute_set_with_media_attribute';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/media/types/' . $attributeSetName,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogProductMediaAttributeManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductMediaAttributeManagementV1GetList',
            ],
        ];

        $requestData = [
            'attributeSetName' => $attributeSetName,
        ];

        $mediaAttributes = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotEmpty($mediaAttributes);
        $attribute = $this->getAttributeByCode($mediaAttributes, 'funny_image');
        $this->assertNotNull($attribute);
        $this->assertEquals('Funny image', $attribute['default_frontend_label']);
        $this->assertEquals(1, $attribute['is_user_defined']);
    }

    /**
     * Retrieve attribute based on given attribute code
     *
     * @param array $attributeList
     * @param string $attributeCode
     * @return array|null
     */
    protected function getAttributeByCode($attributeList, $attributeCode)
    {
        foreach ($attributeList as $attribute) {
            if ($attributeCode == $attribute['attribute_code']) {
                return $attribute;
            }
        }

        return null;
    }
}
