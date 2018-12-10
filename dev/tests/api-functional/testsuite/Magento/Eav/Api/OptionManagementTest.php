<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class OptionManagementTest extends WebapiAbstract
{
    /**
     * Test creating Attribute Option.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiselect_attribute.php
     */
    public function testAttributeOptionAdding()
    {
        $option = [
            'label' => 'test_option_' . md5(random_int(0, PHP_INT_MAX)),
        ];
        $attributeCode = 'multiselect_attribute';

        // Integer value should be returned - this indicates Option was created.
        $this->assertInternalType(
            'numeric',
            $this->addAttributeOption($attributeCode, $option)
        );
        // False should be returned - this indicates Option already exists.
        $this->assertFalse(
            $this->addAttributeOption($attributeCode, $option)
        );
    }

    /**
     * @param string $attributeCode
     * @param array $option
     * @return array|bool|float|int|string
     */
    private function addAttributeOption(string $attributeCode, array $option)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/attributes/' . $attributeCode . '/options',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeOptionManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeOptionManagementV1getItems',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['option' => $option]);
    }
}
