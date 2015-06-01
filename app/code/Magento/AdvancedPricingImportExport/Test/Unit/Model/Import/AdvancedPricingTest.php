<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import;

class AdvancedPricingTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Catalog\Helper\Data |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_catalogData;

    /**
     * @var \Magento\Catalog\Model\Product |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productModel;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeResolver;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importProduct;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validator;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteValidator;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\GroupPrice |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupPriceValidator;

    /**
     * @var \Magento\ImportExport\Model\Resource\Helper |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connection;

    /**
     * @var \Magento\ImportExport\Model\Resource\Import\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataSourceModel;

    /**
     * @var array
     */
    protected $_cachedSkuToDelete;

    /**
     * @var array
     */
    protected $_oldSkus;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $advancedPricing;

    public function setUp()
    {
        $this->jsonHelper = $this->getMock('\Magento\Framework\Json\Helper\Data', [], [], '', false);
        $this->_importExportData = $this->getMock('\Magento\ImportExport\Helper\Data', [], [], '', false);
        $this->_resourceHelper = $this->getMock('\Magento\ImportExport\Model\Resource\Helper', [], [], '', false);
        $this->_resource = $this->getMock(
            '\Magento\Framework\App\Resource',
            ['getConnection'],
            [],
            '',
            false
        );
        $this->_connection = $this->getMockForAbstractClass('\Magento\Framework\DB\Adapter\AdapterInterface', [], '', false);
        $this->_resource->expects($this->any())->method('getConnection')->willReturn($this->_connection);
        $this->_dataSourceModel = $this->getMock('\Magento\ImportExport\Model\Resource\Import\Data', [], [], '', false);
        $this->_resourceFactory = $this->getMock('\Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory', [], [], '', false);
        $this->_productModel = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->_catalogData = $this->getMock('\Magento\Catalog\Helper\Data', [], [], '', false);
        $this->_storeResolver = $this->getMock('\Magento\CatalogImportExport\Model\Import\Product\StoreResolver', [], [], '', false);
        $this->_importProduct = $this->getMock('\Magento\CatalogImportExport\Model\Import\Product', [], [], '', false);
        $this->_validator = $this->getMock(
            '\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator',
            ['isValid', 'getMessages'],
            [],
            '',
            false
        );
        $this->websiteValidator = $this->getMock('\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website', [], [], '', false);
        $this->groupPriceValidator = $this->getMock('\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\GroupPrice', [], [], '', false);

        $this->advancedPricing = $this->getAdvancedPricingMock([
            'retrieveOldSkus',
            'validateRow',
            'addRowError',
            'saveProductPrices',
            'getCustomerGroupId',
            'getWebSiteId',
            'deleteProductTierAndGroupPrices',
        ]);

        $this->advancedPricing->expects($this->any())->method('retrieveOldSkus')->willReturn([]);
    }

    public function testGetEntityTypeCode()
    {
        $result = $this->advancedPricing->getEntityTypeCode();
        $expectedResult = 'advanced_pricing';

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider validateRowResultDataProvider
     */
    public function testValidateRowResult($rowData, $validatedRows, $invalidRows, $oldSkus, $expectedResult)
    {
        $rowNum = 0;
        $advancedPricingMock = $this->getAdvancedPricingMock([
            'retrieveOldSkus',
            'addRowError',
            'saveProductPrices',
            'getCustomerGroupId',
            'getWebSiteId',
        ]);
        $this->setPropertyValue($advancedPricingMock, '_validatedRows', $validatedRows);
        $this->setPropertyValue($advancedPricingMock, '_invalidRows', $invalidRows);
        $this->setPropertyValue($advancedPricingMock, '_oldSkus', $oldSkus);
        $this->_validator->expects($this->any())->method('isValid')->willReturn(true);

        $result = $advancedPricingMock->validateRow($rowData, $rowNum);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider validateRowAddRowErrorCallDataProvider
     */
    public function testValidateRowAddRowErrorCall($rowData, $validatedRows, $invalidRows, $oldSkus, $error)
    {
        $rowNum = 0;
        $advancedPricingMock = $this->getAdvancedPricingMock([
            'retrieveOldSkus',
            'addRowError',
            'saveProductPrices',
            'getCustomerGroupId',
            'getWebSiteId',
        ]);
        $this->setPropertyValue($advancedPricingMock, '_validatedRows', $validatedRows);
        $this->setPropertyValue($advancedPricingMock, '_invalidRows', $invalidRows);
        $this->setPropertyValue($advancedPricingMock, '_oldSkus', $oldSkus);
        $this->_validator->expects($this->any())->method('isValid')->willReturn(true);
        $advancedPricingMock->expects($this->once())->method('addRowError')->with($error, $rowNum);

        $advancedPricingMock->validateRow($rowData, $rowNum);
    }

    public function testValidateRowValidatorCall()
    {
        $rowNum = 0;
        $rowData = [
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
        ];
        $advancedPricingMock = $this->getAdvancedPricingMock([
            'retrieveOldSkus',
            'addRowError',
            'saveProductPrices',
            'getCustomerGroupId',
            'getWebSiteId',
        ]);
        $oldSkus = [
            'sku value' => 'value',
        ];
        $this->setPropertyValue($advancedPricingMock, '_validatedRows', []);
        $this->setPropertyValue($advancedPricingMock, '_oldSkus', $oldSkus);
        $this->_validator->expects($this->once())->method('isValid')->willReturn(false);
        $messages = ['value'];
        $this->_validator->expects($this->once())->method('getMessages')->willReturn($messages);
        $advancedPricingMock->expects($this->once())->method('addRowError')->with('value', $rowNum);

        $advancedPricingMock->validateRow($rowData, $rowNum);
    }

    public function testSaveAdvancedPricingAddRowErrorCall()
    {
        $rowNum = 0;
        $testBunch = [
            $rowNum => [
                'bunch',
            ]
        ];
        $this->_dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($testBunch);
        $this->advancedPricing->expects($this->once())->method('validateRow')->willReturn(false);
        $this->advancedPricing->expects($this->any())->method('saveProductPrices')->will($this->returnSelf());

        $this->advancedPricing
            ->expects($this->once())
            ->method('addRowError')
            ->with(\Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);

        $this->advancedPricing->saveAdvancedPricing();
    }

    /**
     * @dataProvider saveAdvancedPricingDataProvider
     */
    public function testSaveAdvancedPricing($data, $tierCustomerGroupId, $groupCustomerGroupId, $tierWebsiteId, $groupWebsiteId, $expectedTierPrices, $expectedGroupPrices)
    {
        $this->_dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricing->expects($this->once())->method('validateRow')->willReturn(true);

        $this->advancedPricing->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturnMap([
            [$data[0][\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP], $tierCustomerGroupId],
            [$data[0][\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP], $groupCustomerGroupId]
        ]);

        $this->advancedPricing->expects($this->atLeastOnce())->method('getWebSiteId')->willReturnMap([
            [$data[0][\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_WEBSITE], $tierWebsiteId],
            [$data[0][\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_WEBSITE], $groupWebsiteId]
        ]);

        $this->advancedPricing->expects($this->exactly(2))->method('saveProductPrices')->withConsecutive(
            [$expectedTierPrices, \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::TABLE_TIER_PRICE],
            [$expectedGroupPrices, \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::TABLE_GROUPED_PRICE]
        )->will($this->returnSelf());

        $this->advancedPricing->saveAdvancedPricing();
    }

    public function testDeleteAdvancedPricingAddRowCall()
    {
        $rowNum = 0;
        $data = [
            $rowNum => [
                \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value'
            ]
        ];

        $this->_dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricing->expects($this->once())->method('validateRow')->willReturn(false);
        $this->advancedPricing->expects($this->once())->method('addRowError')->with(
            \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_SKU_IS_EMPTY,
            $rowNum
        )->willReturn(false);

        $this->advancedPricing->deleteAdvancedPricing();
    }

    public function testDeleteAdvancedPricingFormListSkuToDelete()
    {
        $sku_1 = 'sku value';
        $sku_2 = 'sku value';
        $data = [
            0 => [
                \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => $sku_1
            ],
            1 => [
                \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => $sku_2
            ],
        ];

        $this->_dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricing->expects($this->any())->method('validateRow')->willReturn(true);
        $expectedSkuList = ['sku value'];
        $this->advancedPricing->expects($this->exactly(2))->method('deleteProductTierAndGroupPrices')->withConsecutive(
            [$expectedSkuList, \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::TABLE_GROUPED_PRICE],
            [$expectedSkuList, \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::TABLE_TIER_PRICE]
        )->will($this->returnSelf());

        $this->advancedPricing->deleteAdvancedPricing();
    }

    public function testDeleteAdvancedPricingReset_cachedSkuToDelete()
    {
        $this->setPropertyValue($this->advancedPricing, '_cachedSkuToDelete', 'some value');
        $this->_dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn([]);

        $this->advancedPricing->deleteAdvancedPricing();

        $_cachedSkuToDelete = $this->getPropertyValue($this->advancedPricing, '_cachedSkuToDelete');
        $this->assertNull($_cachedSkuToDelete);
    }

    public function testReplaceAdvancedPricing()
    {
        $this->markTestSkipped('The method replaceAdvancedPricing is empty');
    }

    public function saveAdvancedPricingDataProvider()
    {
        return [
            [
                '$data' => [
                    0 => [
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier price website value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups ',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                        //group
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group price website value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'group price customer group value - not all groups ',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE => 'group price value',
                    ],
                ],
                '$tierCustomerGroupId' => 'tier customer group id value',
                '$groupCustomerGroupId' => 'group customer group id value',
                '$tierWebsiteId' => 'tier website id value',
                '$groupWebsiteId' => 'group website id value',
                '$expectedTierPrices' => [
                    'sku value' => [
                        [
                            'all_groups' => false,//$rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS
                            'customer_group_id' => 'tier customer group id value',//$tierCustomerGroupId
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                        ],
                    ],
                ],
                '$expectedGroupPrices' => [
                    'sku value' => [
                        [
                            'all_groups' => \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE,
                            'customer_group_id' => 'group customer group id value',//$groupCustomerGroupId
                            'value' => 'group price value',
                            'website_id' => 'group website id value',
                        ],
                    ],
                ],
            ],
            [// tier customer group is equal to all group
                 '$data' => [
                     0 => [
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                         //tier
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier price website value',
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::VALUE_ALL_GROUPS,
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                         //group
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group price website value',
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'group price customer group value',
                         \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE => 'group price value',
                     ],
                 ],
                 '$tierCustomerGroupId' => 'tier customer group id value',
                 '$groupCustomerGroupId' => 'group customer group id value',
                 '$tierWebsiteId' => 'tier website id value',
                 '$groupWebsiteId' => 'group website id value',
                 '$expectedTierPrices' => [
                     'sku value' => [
                         [
                             'all_groups' => true,//$rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS
                             'customer_group_id' => 'tier customer group id value',//$tierCustomerGroupId
                             'qty' => 'tier price qty value',
                             'value' => 'tier price value',
                             'website_id' => 'tier website id value',
                         ],
                     ],
                 ],
                 '$expectedGroupPrices' => [
                     'sku value' => [
                         [
                             'all_groups' => \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE,
                             'customer_group_id' => 'group customer group id value',//$groupCustomerGroupId
                             'value' => 'group price value',
                             'website_id' => 'group website id value',
                         ],
                     ],
                 ],
            ],
            [
                '$data' => [
                    0 => [
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_WEBSITE => null,
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                        //group
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group price website value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'group price customer group value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE => 'group price value',
                    ],
                ],
                '$tierCustomerGroupId' => 'tier customer group id value',
                '$groupCustomerGroupId' => 'group customer group id value',
                '$tierWebsiteId' => 'tier website id value',
                '$groupWebsiteId' => 'group website id value',
                '$expectedTierPrices' => [],
                '$expectedGroupPrices' => [
                    'sku value' => [
                        [
                            'all_groups' => \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE,
                            'customer_group_id' => 'group customer group id value',//$groupCustomerGroupId
                            'value' => 'group price value',
                            'website_id' => 'group website id value',
                        ],
                    ],
                ],
            ],
            [
                '$data' => [
                    0 => [
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier price website value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                        //group
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_WEBSITE => null,
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'group price customer group value',
                        \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_GROUP_PRICE => 'group price value',
                    ],
                ],
                '$tierCustomerGroupId' => 'tier customer group id value',
                '$groupCustomerGroupId' => 'group customer group id value',
                '$tierWebsiteId' => 'tier website id value',
                '$groupWebsiteId' => 'group website id value',
                '$expectedTierPrices' => [
                    'sku value' => [
                        [
                            'all_groups' => false,//$rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS
                            'customer_group_id' => 'tier customer group id value',//$tierCustomerGroupId
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                        ],
                    ]
                ],
                '$expectedGroupPrices' => [],
            ],
        ];
    }

    public function validateRowResultDataProvider()
    {
        return [
            [
                '$rowData' => [
                    \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$validatedRows' => [
                    0 => ['value']
                ],
                '$invalidRows' => [
                    0 => ['value']
                ],
                '$oldSkus' => ['sku value' => 'value'],
                '$expectedResult' => false,
            ],
            [
                '$rowData' => [
                    \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$validatedRows' => [],
                '$invalidRows' => [
                    0 => ['value']
                ],
                '$oldSkus' => ['sku value' => null],
                '$expectedResult' => false,
            ],
            [
                '$rowData' => [
                    \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$validatedRows' => [],
                '$invalidRows' => [
                    0 => ['value']
                ],
                '$oldSkus' => ['sku value' => 'value'],
                '$expectedResult' => false,
            ],
            [
                '$rowData' => [
                    \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$validatedRows' => [],
                '$invalidRows' => [],
                '$oldSkus' => ['sku value' => 'value'],
                '$expectedResult' => true,
            ],
        ];
    }

    public function validateRowAddRowErrorCallDataProvider()
    {
        return [
            [
                '$rowData' => [
                    \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$validatedRows' => [],
                '$invalidRows' => [
                    0 => ['value']
                ],
                '$oldSkus' => ['sku value' => null],
                '$error' => \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE,
            ],
            [
                '$rowData' => [
                    \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => false,
                ],
                '$validatedRows' => [],
                '$invalidRows' => [
                    0 => ['value']
                ],
                '$oldSkus' => [0 => 'value'],
                '$error' => \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_ROW_IS_ORPHAN,
            ],
        ];
    }

    /**
     * Get any object property value.
     *
     * @param $object
     * @param $property
     */
    protected function getPropertyValue($object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Set object property value.
     *
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }

    /**
     * Get AdvancedPricing Mock object with predefined methods.
     *
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdvancedPricingMock($methods = array())
    {
        return $this->getMock(
            '\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing',
            $methods,
            [
                $this->jsonHelper,
                $this->_importExportData,
                $this->_resourceHelper,
                $this->_dataSourceModel,
                $this->_resource,
                $this->_resourceFactory,
                $this->_productModel,
                $this->_catalogData,
                $this->_storeResolver,
                $this->_importProduct,
                $this->_validator,
                $this->websiteValidator,
                $this->groupPriceValidator,
            ],
            ''
        );
    }
}
