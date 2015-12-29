<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Helper;

use Magento\Integration\Model\Integration;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Integration\Helper\Data */
    protected $dataHelper;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataHelper = $helper->getObject('Magento\Integration\Helper\Data');
    }

    public function testMapResources()
    {
        $testData = require __DIR__ . '/_files/acl.php';
        $expectedData = require __DIR__ . '/_files/acl-map.php';
        $this->assertEquals($expectedData, $this->dataHelper->mapResources($testData));
    }

    public function testHashResources()
    {
        $testData = require __DIR__ . '/_files/acl.php';
        $expectedData = require __DIR__ . '/_files/acl-hash.php';
        $this->assertEquals($expectedData, $this->dataHelper->hashResources($testData));
    }

    /**
     * @dataProvider hashDataProvider
     */
    public function testAddParents($hashData, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->dataHelper->addParents($hashData, 'Magento_Sales::actions'));
    }

    /**
     * @dataProvider integrationDataProvider
     */
    public function testIsConfigType($integrationsData, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->dataHelper->isConfigType($integrationsData));
    }

    public function integrationDataProvider()
    {
        return [
            [
                [
                    'id' => 1,
                    Integration::NAME => 'TestIntegration1',
                    Integration::EMAIL => 'test-integration1@magento.com',
                    Integration::ENDPOINT => 'http://endpoint.com',
                    Integration::SETUP_TYPE => 1,
                ],
                true,
            ],
            [
                [
                    'id' => 1,
                    Integration::NAME => 'TestIntegration1',
                    Integration::EMAIL => 'test-integration1@magento.com',
                    Integration::ENDPOINT => 'http://endpoint.com',
                    Integration::SETUP_TYPE => 0,
                ],
                false,
            ]
        ];
    }

    public function hashDataProvider()
    {
        return [
            [
                [
                    'Magento_Sales::sales' =>
                        [
                            'Magento_Sales::sales_operation' =>
                                [
                                    'Magento_Sales::sales_order' =>
                                        [
                                            'Magento_Sales::actions' =>
                                                [
                                                    'Magento_Sales::create' =>
                                                        [],
                                                    'Magento_Sales::actions_view' =>
                                                        [],
                                                    'Magento_Sales::email' =>
                                                        [],
                                                    'Magento_Sales::reorder' =>
                                                        [],
                                                    'Magento_Sales::actions_edit' =>
                                                        []
                                                ]
                                        ],
                                    'Magento_Sales::sales_invoice' =>
                                        [],
                                    'Magento_Sales::shipment' =>
                                        [],
                                ],
                        ],
                    'Magento_Catalog::catalog' =>
                        [
                            'Magento_Catalog::catalog_inventory' =>
                                [
                                    'Magento_Catalog::products' =>
                                        [],
                                    'Magento_Catalog::categories' =>
                                        [],
                                ],
                        ],
                    'Magento_Customer::customer' =>
                        [
                            'Magento_Customer::manage' =>
                                [],
                            'Magento_Customer::online' =>
                                [],
                        ]
                ],
                [
                    'Magento_Sales::sales',
                    'Magento_Sales::sales_operation',
                    'Magento_Sales::sales_order',
                    'Magento_Sales::actions'
                ]
            ]
        ];
    }
}
