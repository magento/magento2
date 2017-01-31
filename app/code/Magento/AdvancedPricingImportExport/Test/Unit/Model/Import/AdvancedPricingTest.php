<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import;

use \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;
use \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory as ResourceFactory;
use \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as RowValidatorInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class AdvancedPricingTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    const TABLE_NAME = 'tableName';
    const LINK_FIELD = 'linkField';

    /**
     * @var ResourceFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceFactory;

    /**
     * @var \Magento\Catalog\Helper\Data |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogData;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeResolver;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importProduct;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productModel;

    /**
     * @var AdvancedPricing\Validator |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var AdvancedPricing\Validator\Website |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteValidator;

    /**
     * @var AdvancedPricing\Validator\TearPrice |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tierPriceValidator;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataSourceModel;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelper;

    /**
     * @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importExportData;

    /**
     * @var array
     */
    protected $cachedSkuToDelete;

    /**
     * @var array
     */
    protected $oldSkus;

    /**
     * @var AdvancedPricing |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $advancedPricing;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $stringObject;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    protected function setUp()
    {
        parent::setUp();

        $this->jsonHelper = $this->getMock(
            \Magento\Framework\Json\Helper\Data::class,
            [],
            [],
            '',
            false
        );
        $this->importExportData = $this->getMock(
            \Magento\ImportExport\Helper\Data::class,
            [],
            [],
            '',
            false
        );
        $this->resourceHelper = $this->getMock(
            \Magento\ImportExport\Model\ResourceModel\Helper::class,
            [],
            [],
            '',
            false
        );
        $this->resource = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            ['getConnection'],
            [],
            '',
            false
        );
        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false
        );
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->dataSourceModel = $this->getMock(
            \Magento\ImportExport\Model\ResourceModel\Import\Data::class,
            [],
            [],
            '',
            false
        );
        $this->eavConfig = $this->getMock(
            \Magento\Eav\Model\Config::class,
            [],
            [],
            '',
            false
        );
        $entityType = $this->getMock(
            \Magento\Eav\Model\Entity\Type::class,
            [],
            [],
            '',
            false
        );
        $entityType->method('getEntityTypeId')->willReturn('');
        $this->eavConfig->method('getEntityType')->willReturn($entityType);
        $this->resourceFactory = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory::class,
            ['create', 'getTable'],
            [],
            '',
            false
        );
        $this->resourceFactory->expects($this->any())->method('create')->willReturnSelf();
        $this->resourceFactory->expects($this->any())->method('getTable')->willReturn(self::TABLE_NAME);
        $this->catalogData = $this->getMock(
            \Magento\Catalog\Helper\Data::class,
            [],
            [],
            '',
            false
        );
        $this->storeResolver = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product\StoreResolver::class,
            [],
            [],
            '',
            false
        );
        $this->importProduct = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            [],
            [],
            '',
            false
        );
        $this->productModel = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [],
            [],
            '',
            false
        );
        $this->validator = $this->getMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator::class,
            ['isValid', 'getMessages'],
            [],
            '',
            false
        );
        $this->websiteValidator = $this->getMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website::class,
            [],
            [],
            '',
            false
        );
        $this->tierPriceValidator = $this->getMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPrice::class,
            [],
            [],
            '',
            false
        );
        $this->stringObject = $this->getMock(
            \Magento\Framework\Stdlib\StringUtils::class,
            [],
            [],
            '',
            false
        );
        $this->errorAggregator = $this->getErrorAggregatorObject();
        $this->dateTime = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\DateTime::class,
            ['date', 'format'],
            [],
            '',
            false
        );
        $this->dateTime->expects($this->any())->method('date')->willReturnSelf();

        $this->advancedPricing = $this->getAdvancedPricingMock(
            [
                'retrieveOldSkus',
                'validateRow',
                'addRowError',
                'saveProductPrices',
                'getCustomerGroupId',
                'getWebSiteId',
                'deleteProductTierPrices',
                'getBehavior',
                'saveAndReplaceAdvancedPrices',
                'processCountExistingPrices',
                'processCountNewPrices'
            ]
        );

        $this->advancedPricing->expects($this->any())->method('retrieveOldSkus')->willReturn([]);
    }

    /**
     * Test getter for entity type code.
     */
    public function testGetEntityTypeCode()
    {
        $result = $this->advancedPricing->getEntityTypeCode();
        $expectedResult = 'advanced_pricing';

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test method validateRow against its result.
     *
     * @dataProvider validateRowResultDataProvider
     */
    public function testValidateRowResult($rowData, $behavior, $expectedResult)
    {
        $rowNum = 0;
        $advancedPricingMock = $this->getAdvancedPricingMock(
            [
                'retrieveOldSkus',
                'addRowError',
                'saveProductPrices',
                'getCustomerGroupId',
                'getWebSiteId',
                'getBehavior',
            ]
        );
        $this->validator->expects($this->any())->method('isValid')->willReturn(true);
        $advancedPricingMock->expects($this->any())->method('getBehavior')->willReturn($behavior);

        $result = $advancedPricingMock->validateRow($rowData, $rowNum);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test method validateRow whether AddRowError is called.
     *
     * @dataProvider validateRowAddRowErrorCallDataProvider
     */
    public function testValidateRowAddRowErrorCall($rowData, $behavior, $error)
    {
        $rowNum = 0;
        $advancedPricingMock = $this->getAdvancedPricingMock(
            [
                'retrieveOldSkus',
                'addRowError',
                'saveProductPrices',
                'getCustomerGroupId',
                'getWebSiteId',
                'getBehavior',
            ]
        );
        $this->validator->expects($this->any())->method('isValid')->willReturn(true);
        $advancedPricingMock->expects($this->any())->method('getBehavior')->willReturn($behavior);
        $advancedPricingMock->expects($this->once())->method('addRowError')->with($error, $rowNum);

        $advancedPricingMock->validateRow($rowData, $rowNum);
    }

    /**
     * Test method validateRow whether internal validator is called.
     */
    public function testValidateRowValidatorCall()
    {
        $rowNum = 0;
        $rowData = [
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::COL_SKU => 'sku value',
        ];
        $advancedPricingMock = $this->getAdvancedPricingMock(
            [
                'retrieveOldSkus',
                'addRowError',
                'saveProductPrices',
                'getCustomerGroupId',
                'getWebSiteId',
            ]
        );
        $this->setPropertyValue($advancedPricingMock, '_validatedRows', []);
        $this->validator->expects($this->once())->method('isValid')->willReturn(false);
        $messages = ['value'];
        $this->validator->expects($this->once())->method('getMessages')->willReturn($messages);
        $advancedPricingMock->expects($this->once())->method('addRowError')->with('value', $rowNum);

        $advancedPricingMock->validateRow($rowData, $rowNum);
    }

    /**
     * Test method saveAndReplaceAdvancedPrices whether AddRowError is called.
     */
    public function testSaveAndReplaceAdvancedPricesAddRowErrorCall()
    {
        $rowNum = 0;
        $testBunch = [
            $rowNum => [
                'bunch',
            ]
        ];
        $this->dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($testBunch);
        $this->advancedPricing->expects($this->once())->method('validateRow')->willReturn(false);
        $this->advancedPricing->expects($this->any())->method('saveProductPrices')->will($this->returnSelf());

        $this->advancedPricing
            ->expects($this->once())
            ->method('addRowError')
            ->with(RowValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);

        $this->invokeMethod($this->advancedPricing, 'saveAndReplaceAdvancedPrices');
    }

    /**
     * Test method saveAdvancedPricing.
     */
    public function testSaveAdvancedPricing()
    {
        $this->advancedPricing
            ->expects($this->once())
            ->method('saveAndReplaceAdvancedPrices');

        $result = $this->advancedPricing->saveAdvancedPricing();

        $this->assertEquals($this->advancedPricing, $result);
    }

    /**
     * Test method saveAndReplaceAdvancedPrices with append import behaviour.
     * Take into consideration different data and check relative internal calls.
     *
     * @dataProvider saveAndReplaceAdvancedPricesAppendBehaviourDataProvider
     */
    public function testSaveAndReplaceAdvancedPricesAppendBehaviourDataAndCalls(
        $data,
        $tierCustomerGroupId,
        $groupCustomerGroupId,
        $tierWebsiteId,
        $groupWebsiteId,
        $expectedTierPrices
    ) {
        $this->advancedPricing
            ->expects($this->any())
            ->method('getBehavior')
            ->willReturn(\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND);
        $this->dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricing->expects($this->any())->method('validateRow')->willReturn(true);

        $this->advancedPricing->expects($this->any())->method('getCustomerGroupId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP], $tierCustomerGroupId],
            ]
        );

        $this->advancedPricing->expects($this->any())->method('getWebSiteId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_WEBSITE], $tierWebsiteId],
            ]
        );

        $this->advancedPricing->expects($this->any())->method('saveProductPrices')->will($this->returnSelf());

        $this->advancedPricing->expects($this->any())->method('processCountExistingPrices')->willReturnSelf();
        $this->advancedPricing->expects($this->any())->method('processCountNewPrices')->willReturnSelf();

        $result = $this->invokeMethod($this->advancedPricing, 'saveAndReplaceAdvancedPrices');

        $this->assertEquals($this->advancedPricing, $result);
    }

    /**
     * Test method saveAndReplaceAdvancedPrices with replace import behaviour.
     */
    public function testSaveAndReplaceAdvancedPricesReplaceBehaviourInternalCalls()
    {
        $skuVal = 'sku value';
        $data = [
            0 => [
                AdvancedPricing::COL_SKU => $skuVal,
            ],
        ];
        $expectedTierPrices = [];
        $listSku = [
            $skuVal
        ];
        $this->advancedPricing->expects($this->any())->method('getBehavior')->willReturn(
            \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE
        );
        $this->dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricing->expects($this->once())->method('validateRow')->willReturn(true);

        $this->advancedPricing
            ->expects($this->never())
            ->method('getCustomerGroupId');
        $this->advancedPricing
            ->expects($this->never())
            ->method('getWebSiteId');

        $this->advancedPricing
            ->expects($this->any())
            ->method('deleteProductTierPrices')
            ->withConsecutive(
                [
                    $listSku,
                    AdvancedPricing::TABLE_TIER_PRICE,
                ]
            )
            ->willReturn(true);

        $this->advancedPricing
            ->expects($this->any())
            ->method('saveProductPrices')
            ->withConsecutive(
                [
                    $expectedTierPrices,
                    AdvancedPricing::TABLE_TIER_PRICE
                ]
            )
            ->will($this->returnSelf());

        $this->invokeMethod($this->advancedPricing, 'saveAndReplaceAdvancedPrices');
    }

    /**
     * Test method deleteAdvancedPricing() whether correct $listSku is formed.
     */
    public function testDeleteAdvancedPricingFormListSkuToDelete()
    {
        $skuOne = 'sku value';
        $skuTwo = 'sku value';
        $data = [
            0 => [
                AdvancedPricing::COL_SKU => $skuOne
            ],
            1 => [
                AdvancedPricing::COL_SKU => $skuTwo
            ],
        ];

        $this->dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricing->expects($this->any())->method('validateRow')->willReturn(true);
        $expectedSkuList = ['sku value'];
        $this->advancedPricing
            ->expects($this->once())
            ->method('deleteProductTierPrices')
            ->withConsecutive(
                [$expectedSkuList, AdvancedPricing::TABLE_TIER_PRICE]
            )->will($this->returnSelf());

        $this->advancedPricing->deleteAdvancedPricing();
    }

    /**
     * Test method deleteAdvancedPricing() whether _cachedSkuToDelete property is set to null.
     */
    public function testDeleteAdvancedPricingResetCachedSkuToDelete()
    {
        $this->setPropertyValue($this->advancedPricing, '_cachedSkuToDelete', 'some value');
        $this->dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn([]);

        $this->advancedPricing->deleteAdvancedPricing();

        $cachedSkuToDelete = $this->getPropertyValue($this->advancedPricing, '_cachedSkuToDelete');
        $this->assertNull($cachedSkuToDelete);
    }

    /**
     * Test method replaceAdvancedPricing().
     */
    public function testReplaceAdvancedPricing()
    {
        $this->advancedPricing
            ->expects($this->once())
            ->method('saveAndReplaceAdvancedPrices');

        $result = $this->advancedPricing->saveAdvancedPricing();

        $this->assertEquals($this->advancedPricing, $result);
    }

    /**
     * Data provider for testSaveAndReplaceAdvancedPricesAppendBehaviour().
     *
     * @return array
     */
    public function saveAndReplaceAdvancedPricesAppendBehaviourDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                '$data' => [
                    0 => [
                        AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier price website value',
                        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups ',
                        AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                    ],
                ],
                '$tierCustomerGroupId' => 'tier customer group id value',
                '$groupCustomerGroupId' => 'group customer group id value',
                '$tierWebsiteId' => 'tier website id value',
                '$groupWebsiteId' => 'group website id value',
                '$expectedTierPrices' => [
                    'sku value' => [
                        [
                            'all_groups' => false,
                            //$rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS
                            'customer_group_id' => 'tier customer group id value',
                            //$tierCustomerGroupId
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                        ],
                    ],
                ],
            ],
            [// tier customer group is equal to all group
                '$data' => [
                    0 => [
                        AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier price website value',
                        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => AdvancedPricing::VALUE_ALL_GROUPS,
                        AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                    ],
                ],
                '$tierCustomerGroupId' => 'tier customer group id value',
                '$groupCustomerGroupId' => 'group customer group id value',
                '$tierWebsiteId' => 'tier website id value',
                '$groupWebsiteId' => 'group website id value',
                '$expectedTierPrices' => [
                    'sku value' => [
                        [
                            'all_groups' => true,
                            //$rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS
                            'customer_group_id' => 'tier customer group id value',
                            //$tierCustomerGroupId
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                        ],
                    ],
                ],
            ],
            [
                '$data' => [
                    0 => [
                        AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        AdvancedPricing::COL_TIER_PRICE_WEBSITE => null,
                        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups',
                        AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                    ],
                ],
                '$tierCustomerGroupId' => 'tier customer group id value',
                '$groupCustomerGroupId' => 'group customer group id value',
                '$tierWebsiteId' => 'tier website id value',
                '$groupWebsiteId' => 'group website id value',
                '$expectedTierPrices' => [],
            ],
            [
                '$data' => [
                    0 => [
                        AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier price website value',
                        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups',
                        AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                    ],
                ],
                '$tierCustomerGroupId' => 'tier customer group id value',
                '$groupCustomerGroupId' => 'group customer group id value',
                '$tierWebsiteId' => 'tier website id value',
                '$groupWebsiteId' => 'group website id value',
                '$expectedTierPrices' => [
                    'sku value' => [
                        [
                            'all_groups' => false,
                            //$rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS
                            'customer_group_id' => 'tier customer group id value',
                            //$tierCustomerGroupId
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                        ],
                    ]
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Data provider for testValidateRowResult().
     *
     * @return array
     */
    public function validateRowResultDataProvider()
    {
        return [
            [
                '$rowData' => [
                    AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$behavior' => null,
                '$expectedResult' => true,
            ],
            [
                '$rowData' => [
                    AdvancedPricing::COL_SKU => null,
                ],
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$expectedResult' => false,
            ],
            [
                '$rowData' => [
                    AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$expectedResult' => true,
            ]
        ];
    }

    /**
     * Data provider for testValidateRowAddRowErrorCall().
     *
     * @return array
     */
    public function validateRowAddRowErrorCallDataProvider()
    {
        return [
            [
                '$rowData' => [
                    AdvancedPricing::COL_SKU => null,
                ],
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$error' => RowValidatorInterface::ERROR_SKU_IS_EMPTY,
            ],
            [
                '$rowData' => [
                    AdvancedPricing::COL_SKU => false,
                ],
                '$behavior' => null,
                '$error' => RowValidatorInterface::ERROR_ROW_IS_ORPHAN,
            ],
        ];
    }

    /**
     * @param array $priceData
     * @param array $oldSkus
     * @param array $priceIn
     * @param int $callNum
     * @dataProvider saveProductPricesDataProvider
     */
    public function testSaveProductPrices($priceData, $oldSkus, $priceIn, $callNum)
    {
        $this->advancedPricing = $this->getAdvancedPricingMock(['retrieveOldSkus']);

        $this->advancedPricing->expects($this->any())->method('retrieveOldSkus')->willReturn($oldSkus);

        $this->connection->expects($this->exactly($callNum))
            ->method('insertOnDuplicate')
            ->with(self::TABLE_NAME, $priceIn, ['value']);

        $this->invokeMethod($this->advancedPricing, 'saveProductPrices', [$priceData, 'table']);
    }

    public function saveProductPricesDataProvider()
    {
        return [
            [[], ['oSku1' => 'product1', 'oSku2' => 'product2'], [], 0],
            [
                [
                    'oSku1' => ['row1' => ['row1-1', 'row1-2'], 'row2' => ['row2-1', 'row2-2']],
                    'nSku' => ['row3', 'row4'],
                ],
                ['oSku1' => 'product1', 'oSku2' => 'product2'],
                [
                    ['row1-1', 'row1-2', self::LINK_FIELD => 'product1'],
                    ['row2-1', 'row2-2', self::LINK_FIELD => 'product1']
                ],
                1
            ],
        ];
    }

    /**
     * @param array $listSku
     * @param array $cachedSkuToDelete
     * @param int $numCallAddError
     * @param int $numCallDelete
     * @param boolean $exceptionInDelete
     * @param boolean $result
     * @dataProvider deleteProductTierPricesDataProvider
     */
    public function testDeleteProductTierPrices(
        $listSku,
        $cachedSkuToDelete,
        $numCallAddError,
        $numCallDelete,
        $exceptionInDelete,
        $result
    ) {
        $this->advancedPricing = $this->getAdvancedPricingMock(['addRowError', 'retrieveOldSkus']);
        $dbSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        if ($listSku) {
            $this->connection->expects($this->once())
                ->method('fetchCol')
                ->willReturn($cachedSkuToDelete);
            $this->connection->expects($this->once())
                ->method('select')
                ->willReturn($dbSelectMock);
            $dbSelectMock->expects($this->once())
                ->method('from')
                ->with(self::TABLE_NAME, self::LINK_FIELD)
                ->willReturnSelf();
            $dbSelectMock->expects($this->once())
                ->method('where')
                ->willReturnSelf();
        }

        $this->advancedPricing->expects($this->exactly($numCallAddError))
            ->method('addRowError');
        if ($exceptionInDelete) {
            $this->connection->expects($this->exactly($numCallDelete))
                ->method('delete')
                ->willThrowException(new \Exception());
        } else {
            $this->connection->expects($this->exactly($numCallDelete))
                ->method('delete')
                ->willReturn(1);
        }
        $this->assertEquals(
            $result,
            $this->invokeMethod($this->advancedPricing, 'deleteProductTierPrices', [$listSku, 'table'])
        );
    }

    public function deleteProductTierPricesDataProvider()
    {
        return [
            [
                [],
                ['toDelete1', 'toDelete2'],
                0,
                0,
                0,
                false
            ],
            [
                ['sku1', 'sku2'],
                ['toDelete1', 'toDelete2'],
                0,
                1,
                0,
                true
            ],
            [
                ['sku1', 'sku2'],
                ['toDelete1', 'toDelete2'],
                0,
                1,
                1,
                false
            ],
            [
                ['sku1', 'sku2'],
                [],
                1,
                0,
                0,
                false
            ],
        ];
    }

    /**
     * @param array $prices
     * @param array $existingPrices
     * @param array $oldSkus
     * @param int $numCall
     * @param array $args
     * @dataProvider processCountExistingPricesDataProvider
     */
    public function testProcessCountExistingPrices(
        $prices,
        $existingPrices,
        $oldSkus,
        $numCall,
        $args
    ) {
        $this->advancedPricing = $this->getAdvancedPricingMock(
            [
                'incrementCounterUpdated',
                'retrieveOldSkus'
            ]
        );
        $dbSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $this->connection->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn($existingPrices);
        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($dbSelectMock);
        $dbSelectMock->expects($this->once())
            ->method('from')
            ->with(
                self::TABLE_NAME,
                ['value_id', self::LINK_FIELD, 'all_groups', 'customer_group_id']
            )->willReturnSelf();
        $this->advancedPricing->expects($this->once())
            ->method('retrieveOldSkus')
            ->willReturn($oldSkus);
        $this->advancedPricing->expects($this->exactly($numCall))
            ->method('incrementCounterUpdated')
            ->withConsecutive($args);

        $this->invokeMethod($this->advancedPricing, 'processCountExistingPrices', [$prices, 'table']);
    }

    public function processCountExistingPricesDataProvider()
    {
        return [
            [
                ['oSku1' => ['price1'], 'nSku' => 'price'],
                [[self::LINK_FIELD => 'product1']],
                ['oSku1' => 'product1', 'oSku2' => 'product2'],
                1,
                [['price1'], [self::LINK_FIELD => 'product1']]
            ],
            [
                ['oSku1' => ['price1'], 'nSku' => 'price'],
                [[self::LINK_FIELD => 'product']],
                ['oSku1' => 'product1', 'oSku2' => 'product2'],
                0,
                [['price1'], [self::LINK_FIELD => 'product1']]
            ],
        ];
    }

    /**
     * Get any object property value.
     *
     * @param $object
     * @param $property
     * @return mixed
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
     * Invoke any method of class AdvancedPricing.
     *
     * @param object $object
     * @param string $method
     * @param array $args
     *
     * @return mixed the method result.
     */
    private function invokeMethod($object, $method, $args = [])
    {
        $class = new \ReflectionClass(\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    /**
     * Get AdvancedPricing Mock object with predefined methods.
     *
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdvancedPricingMock($methods = [])
    {
        $metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $metadataMock = $this->getMock(
            \Magento\Framework\EntityManager\EntityMetadata::class,
            [],
            [],
            '',
            false
        );
        $metadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn(self::LINK_FIELD);
        $metadataPoolMock->expects($this->any())
            ->method('getMetaData')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $advancedPricingMock = $this->getMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class,
            $methods,
            [
                $this->jsonHelper,
                $this->importExportData,
                $this->dataSourceModel,
                $this->eavConfig,
                $this->resource,
                $this->resourceHelper,
                $this->stringObject,
                $this->errorAggregator,
                $this->dateTime,
                $this->resourceFactory,
                $this->productModel,
                $this->catalogData,
                $this->storeResolver,
                $this->importProduct,
                $this->validator,
                $this->websiteValidator,
                $this->tierPriceValidator
            ],
            ''
        );
        $reflection = new \ReflectionClass(\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($advancedPricingMock, $metadataPoolMock);

        return $advancedPricingMock;
    }
}
