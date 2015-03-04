<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to test routing based on a Service that exposes subset of operations
 */
namespace Magento\Webapi\Routing;

class SubsetTest extends \Magento\Webapi\Routing\BaseService
{
    /**
     * @var string
     */
    protected $_version;

    /**
     * @var string
     */
    protected $_restResourcePath;

    /**
     * @var string
     */
    protected $_soapService;

    /**
     * @Override
     */
    protected function setUp()
    {
        $this->_version = 'V1';
        $this->_restResourcePath = "/{$this->_version}/testModule2SubsetRest/";
        $this->_soapService = 'testModule2SubsetRestV1';
    }

    /**
     * @Override
     * Test get item
     */
    public function testItem()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'Item'],
        ];
        $requestData = ['id' => $itemId];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($itemId, $item['id'], 'Item was retrieved unsuccessfully');
    }

    /**
     * @Override
     * Test fetching all items
     */
    public function testItems()
    {
        $itemArr = [['id' => 1, 'name' => 'testItem1'], ['id' => 2, 'name' => 'testItem2']];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'Items'],
        ];

        $item = $this->_webApiCall($serviceInfo);
        $this->assertEquals($itemArr, $item, 'Items were not retrieved');
    }
}
