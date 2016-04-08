<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\DataObjectSerialization;

class ServiceSerializationTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var string
     */
    protected $_version;

    /**
     * @var string
     */
    protected $_restResourcePath;

    protected function setUp()
    {
        $this->_markTestAsRestOnly();
        $this->_version = 'V1';
        $this->_restResourcePath = "/{$this->_version}/testmodule4/";
    }

    /**
     *  Test simple request data
     */
    public function testGetServiceCall()
    {
        $itemId = 1;
        $name = 'Test';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];
        $item = $this->_webApiCall($serviceInfo, []);
        $this->assertEquals($itemId, $item['entity_id'], 'id field returned incorrectly');
        $this->assertEquals($name, $item['name'], 'name field returned incorrectly');
    }

    /**
     *  Test multiple params with Data Object
     */
    public function testUpdateServiceCall()
    {
        $itemId = 1;
        $name = 'Test';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $item = $this->_webApiCall($serviceInfo, ['request' => ['name' => $name]]);
        $this->assertEquals($itemId, $item['entity_id'], 'id field returned incorrectly');
        $this->assertEquals($name, $item['name'], 'name field returned incorrectly');
    }

    /**
     *  Test nested Data Object
     */
    public function testNestedDataObjectCall()
    {
        $itemId = 1;
        $name = 'Test';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId . '/nested',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $item = $this->_webApiCall($serviceInfo, ['request' => ['details' => ['name' => $name]]]);
        $this->assertEquals($itemId, $item['entity_id'], 'id field returned incorrectly');
        $this->assertEquals($name, $item['name'], 'name field returned incorrectly');
    }

    public function testScalarResponse()
    {
        $id = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "{$this->_restResourcePath}scalar/{$id}",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];
        $this->assertEquals($id, $this->_webApiCall($serviceInfo), 'Scalar service output is serialized incorrectly.');
    }

    public function testExtensibleCall()
    {
        $id = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "{$this->_restResourcePath}extensibleDataObject/{$id}",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];

        $name = 'Magento';
        $requestData = [
          'name' => $name,
        ];
        $item = $this->_webApiCall($serviceInfo, ['request' => $requestData]);
        $this->assertEquals($id, $item['entity_id'], 'id field returned incorrectly');
        $this->assertEquals($name, $item['name'], 'name field returned incorrectly');
    }
}
