<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

use Magento\Webapi\Model\Rest\Config;

class OptionTypesListTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_READ_NAME = 'configurableProductOptionTypesListV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/configurable-products/:productSku/options/';

    public function testGetTypes()
    {
        $expectedTypes = ['multiselect', 'select'];
        $result = $this->getTypes();
        $this->assertEquals($expectedTypes, $result);
    }

    /**
     * @return array
     */
    protected function getTypes()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(':productSku/', '', self::RESOURCE_PATH) . 'types',
                'httpMethod'   => Config::HTTP_METHOD_GET
            ],
            'soap' => [
                'service'        => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation'      => self::SERVICE_READ_NAME . 'GetItems'
            ]
        ];
        return $this->_webApiCall($serviceInfo);
    }
}
