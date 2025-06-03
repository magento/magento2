<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Routing;

class ServiceVersionV2Test extends \Magento\Webapi\Routing\BaseService
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $soapService;

    /**
     * @var string
     */
    private $restResourcePath;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->version = 'V2';
        $this->soapService = 'testModule1AllSoapAndRestV2';
        $this->restResourcePath = "/{$this->version}/testmodule1/";
    }

    /**
     *  Test to assert overriding of the existing 'Item' api in V2 version of the same service
     */
    public function testItem()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->restResourcePath . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => ['service' => $this->soapService, 'operation' => $this->soapService . 'Item'],
        ];
        $requestData = ['id' => $itemId];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        // Asserting for additional attribute returned by the V2 api
        $this->assertEquals(1, $item['price'], 'Item was retrieved unsuccessfully from V2');
    }

    /**
     * Test fetching all items
     */
    public function testItems()
    {
        $itemArr = [
            ['id' => 1, 'name' => 'testProduct1', 'price' => '1'],
            ['id' => 2, 'name' => 'testProduct2', 'price' => '2'],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->restResourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => ['service' => $this->soapService, 'operation' => $this->soapService . 'Items'],
        ];
        $item = $this->_webApiCall($serviceInfo);
        $this->assertEquals($itemArr, $item, 'Items were not retrieved');
    }

    /**
     * Test fetching items when filters are applied
     *
     * @param string[] $filters
     * @param array $expectedResult
     * @dataProvider itemsWithFiltersDataProvider
     */
    public function testItemsWithFilters($filters, $expectedResult)
    {
        $restFilter = '';
        foreach ($filters as $filterItemKey => $filterMetadata) {
            foreach ($filterMetadata as $filterMetaKey => $filterMetaValue) {
                $paramsDelimiter = empty($restFilter) ? '?' : '&';
                $restFilter .= "{$paramsDelimiter}filters[{$filterItemKey}][{$filterMetaKey}]={$filterMetaValue}";
            }
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->restResourcePath . $restFilter,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => $this->soapService,
                'operation' => $this->soapService . 'Items',
            ],
        ];
        $requestData = [];
        if (!empty($filters)) {
            $requestData['filters'] = $filters;
        }
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($expectedResult, $item, 'Filtration does not seem to work correctly.');
    }

    public static function itemsWithFiltersDataProvider()
    {
        $firstItem = ['id' => 1, 'name' => 'testProduct1', 'price' => 1];
        $secondItem = ['id' => 2, 'name' => 'testProduct2', 'price' => 2];
        return [
            'Both items filter' => [
                [
                    ['field' => 'id', 'conditionType' => 'eq','value' => 1],
                    ['field' => 'id', 'conditionType' => 'eq','value' => 2],
                ],
                [$firstItem, $secondItem],
            ],
            'First item filter' => [[['field' => 'id', 'conditionType' => 'eq','value' => 1]], [$firstItem]],
            'Second item filter' => [[['field' => 'id', 'conditionType' => 'eq','value' => 2]], [$secondItem]],
            'Empty filter' => [[], [$firstItem, $secondItem]],
        ];
    }

    /**
     *  Test update item
     */
    public function testUpdate()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->restResourcePath . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => ['service' => $this->soapService, 'operation' => $this->soapService . 'Update'],
        ];
        $requestData = ['entityItem' => ['id' => $itemId, 'name' => 'testName', 'price' => '4']];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals('Updated' . $requestData['entityItem']['name'], $item['name'], 'Item update failed');
    }

    /**
     *  Test to assert presence of new 'delete' api added in V2 version of the same service
     */
    public function testDelete()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->restResourcePath . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => ['service' => $this->soapService, 'operation' => $this->soapService . 'Delete'],
        ];
        $requestData = ['id' => $itemId, 'name' => 'testName'];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($itemId, $item['id'], "Item delete failed");
    }
}
