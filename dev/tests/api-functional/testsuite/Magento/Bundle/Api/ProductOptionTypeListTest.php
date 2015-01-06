<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Bundle\Api;

use Magento\Webapi\Model\Rest\Config;

class ProductOptionTypeListTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_READ_NAME = 'bundleProductOptionTypeListV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/bundle-products/option/types';

    public function testGetTypes()
    {
        $expected = [
            ['label' => 'Drop-down', 'code' => 'select'],
            ['label' => 'Radio Buttons', 'code' => 'radio'],
            ['label' => 'Checkbox', 'code' => 'checkbox'],
            ['label' => 'Multiple Select', 'code' => 'multi'],
        ];
        $result = $this->getTypes();

        $this->assertEquals($expected, $result);
    }

    /**
     * @return string
     */
    protected function getTypes()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Config::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'getItems',
            ],
        ];
        return $this->_webApiCall($serviceInfo);
    }
}
