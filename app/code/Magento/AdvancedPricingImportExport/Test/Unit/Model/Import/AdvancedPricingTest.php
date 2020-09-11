<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPrice;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory as ResourceFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD)
 */
class AdvancedPricingTest extends AbstractImportTestCase
{
    /**
     * DB Table data
     */
    const TABLE_NAME = 'tableName';
    const LINK_FIELD = 'linkField';

    /**
     * @var ResourceFactory|MockObject
     */
    protected $resourceFactory;

    /**
     * @var \Magento\Catalog\Helper\Data|MockObject
     */
    protected $catalogData;

    /**
     * @var StoreResolver|MockObject
     */
    protected $storeResolver;

    /**
     * @var Product|MockObject
     */
    protected $importProduct;

    /**
     * @var \Magento\Catalog\Model\Product|MockObject
     */
    protected $productModel;

    /**
     * @var AdvancedPricing\Validator|MockObject
     */
    protected $validator;

    /**
     * @var AdvancedPricing\Validator\Website|MockObject
     */
    protected $websiteValidator;

    /**
     * @var AdvancedPricing\Validator\TierPrice|MockObject
     */
    protected $tierPriceValidator;

    /**
     * @var Helper|MockObject
     */
    protected $resourceHelper;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data|MockObject
     */
    protected $dataSourceModel;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $dateTime;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Json\Helper\Data|MockObject
     */
    protected $jsonHelper;

    /**
     * @var \Magento\ImportExport\Helper\Data|MockObject
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
     * @var AdvancedPricing|MockObject
     */
    protected $advancedPricing;

    /**
     * @var ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jsonHelper = $this->createMock(Data::class);
        $this->importExportData = $this->createMock(\Magento\ImportExport\Helper\Data::class);
        $this->resourceHelper = $this->createMock(Helper::class);
        $this->resource = $this->createPartialMock(ResourceConnection::class, ['getConnection']);
        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false
        );
        $this->resource->method('getConnection')->willReturn($this->connection);
        $this->dataSourceModel = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);
        $entityType = $this->createMock(Type::class);
        $entityType->method('getEntityTypeId')->willReturn('');
        $this->resourceFactory = $this->getMockBuilder(
            \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory::class
        )
            ->setMethods(['create', 'getTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceFactory->method('create')->willReturnSelf();
        $this->resourceFactory->method('getTable')->willReturn(self::TABLE_NAME);
        $this->catalogData = $this->createMock(\Magento\Catalog\Helper\Data::class);
        $this->storeResolver = $this->createMock(
            StoreResolver::class
        );
        $this->importProduct = $this->createMock(Product::class);
        $this->productModel = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->validator = $this->createPartialMock(
            Validator::class,
            ['isValid', 'getMessages']
        );
        $this->websiteValidator = $this->createMock(
            Website::class
        );
        $this->tierPriceValidator = $this->createMock(
            TierPrice::class
        );
        $this->errorAggregator = $this->getErrorAggregatorObject();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->addMethods(['format'])
            ->onlyMethods(['date'])
            ->getMock();
        $this->dateTime->method('date')->willReturnSelf();

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

        $this->advancedPricing->method('retrieveOldSkus')->willReturn([]);
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
     * @param array $rowData
     * @param string|null $behavior
     * @param bool $expectedResult
     * @throws \ReflectionException
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
        $this->validator->method('isValid')->willReturn(true);
        $advancedPricingMock->method('getBehavior')->willReturn($behavior);

        $result = $advancedPricingMock->validateRow($rowData, $rowNum);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test method validateRow whether AddRowError is called.
     *
     * @dataProvider validateRowAddRowErrorCallDataProvider
     * @param array $rowData
     * @param string|null $behavior
     * @param string $error
     * @throws \ReflectionException
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
        $this->validator->method('isValid')->willReturn(true);
        $advancedPricingMock->method('getBehavior')->willReturn($behavior);
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
        $this->advancedPricing->method('saveProductPrices')->willReturnSelf();

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
     * @param array $data
     * @param string $tierCustomerGroupId
     * @param string $groupCustomerGroupId
     * @param string $tierWebsiteId
     * @param string $groupWebsiteId
     * @param array $expectedTierPrices
     * @throws \ReflectionException
     */
    public function testSaveAndReplaceAdvancedPricesAppendBehaviourDataAndCalls(
        $data,
        $tierCustomerGroupId,
        $groupCustomerGroupId,
        $tierWebsiteId,
        $groupWebsiteId,
        $expectedTierPrices
    ) {
        $skuProduct = 'product1';
        $sku = $data[0][AdvancedPricing::COL_SKU];
        $advancedPricing = $this->getAdvancedPricingMock(
            [
                'retrieveOldSkus',
                'validateRow',
                'addRowError',
                'getCustomerGroupId',
                'getWebSiteId',
                'deleteProductTierPrices',
                'getBehavior',
                'saveAndReplaceAdvancedPrices',
                'processCountExistingPrices',
                'processCountNewPrices'
            ]
        );
        $advancedPricing
            ->method('getBehavior')
            ->willReturn(Import::BEHAVIOR_APPEND);
        $this->dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $advancedPricing->method('validateRow')->willReturn(true);

        $advancedPricing->method('getCustomerGroupId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP], $tierCustomerGroupId],
            ]
        );

        $advancedPricing->method('getWebSiteId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_WEBSITE], $tierWebsiteId],
            ]
        );

        $oldSkus = [$sku => $skuProduct];
        $expectedTierPrices[$sku][0][self::LINK_FIELD] = $skuProduct;
        $advancedPricing->expects($this->once())->method('retrieveOldSkus')->willReturn($oldSkus);
        $this->connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(self::TABLE_NAME, $expectedTierPrices[$sku], ['value', 'percentage_value']);

        $advancedPricing->method('processCountExistingPrices')->willReturnSelf();
        $advancedPricing->method('processCountNewPrices')->willReturnSelf();

        $result = $this->invokeMethod($advancedPricing, 'saveAndReplaceAdvancedPrices');

        $this->assertEquals($advancedPricing, $result);
    }

    /**
     * Test method saveAndReplaceAdvancedPrices with append import behaviour.
     */
    public function testSaveAndReplaceAdvancedPricesAppendBehaviourDataAndCallsWithoutTierPrice()
    {
        $data = [
            0 => [
                AdvancedPricing::COL_SKU => 'sku value',
                AdvancedPricing::COL_TIER_PRICE_WEBSITE => null,
                AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups',
                AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                AdvancedPricing::COL_TIER_PRICE_TYPE => AdvancedPricing::TIER_PRICE_TYPE_FIXED
            ],
        ];
        $tierCustomerGroupId = 'tier customer group id value';
        $tierWebsiteId = 'tier website id value';
        $expectedTierPrices = [];

        $skuProduct = 'product1';
        $sku = $data[0][AdvancedPricing::COL_SKU];
        $advancedPricing = $this->getAdvancedPricingMock(
            [
                'retrieveOldSkus',
                'validateRow',
                'addRowError',
                'getCustomerGroupId',
                'getWebSiteId',
                'deleteProductTierPrices',
                'getBehavior',
                'saveAndReplaceAdvancedPrices',
                'processCountExistingPrices',
                'processCountNewPrices'
            ]
        );
        $advancedPricing
            ->method('getBehavior')
            ->willReturn(Import::BEHAVIOR_APPEND);
        $this->dataSourceModel->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $advancedPricing->method('validateRow')->willReturn(true);

        $advancedPricing->method('getCustomerGroupId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP], $tierCustomerGroupId],
            ]
        );

        $advancedPricing->method('getWebSiteId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_WEBSITE], $tierWebsiteId],
            ]
        );

        $oldSkus = [$sku => $skuProduct];
        $expectedTierPrices[$sku][0][self::LINK_FIELD] = $skuProduct;
        $advancedPricing->expects($this->never())->method('retrieveOldSkus')->willReturn($oldSkus);
        $this->connection->expects($this->never())
            ->method('insertOnDuplicate')
            ->with(self::TABLE_NAME, $expectedTierPrices[$sku], ['value', 'percentage_value']);

        $advancedPricing->method('processCountExistingPrices')->willReturnSelf();
        $advancedPricing->method('processCountNewPrices')->willReturnSelf();

        $result = $this->invokeMethod($advancedPricing, 'saveAndReplaceAdvancedPrices');

        $this->assertEquals($advancedPricing, $result);
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
        $this->advancedPricing->method('getBehavior')->willReturn(
            Import::BEHAVIOR_REPLACE
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
            ->method('deleteProductTierPrices')
            ->withConsecutive(
                [
                    $listSku,
                    AdvancedPricing::TABLE_TIER_PRICE,
                ]
            )
            ->willReturn(true);

        $this->advancedPricing
            ->method('saveProductPrices')
            ->withConsecutive(
                [
                    $expectedTierPrices,
                    AdvancedPricing::TABLE_TIER_PRICE
                ]
            )
            ->willReturnSelf();

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
        $this->advancedPricing->method('validateRow')->willReturn(true);
        $expectedSkuList = ['sku value'];
        $this->advancedPricing
            ->expects($this->once())
            ->method('deleteProductTierPrices')
            ->withConsecutive(
                [$expectedSkuList, AdvancedPricing::TABLE_TIER_PRICE]
            )->willReturnSelf();

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
                        AdvancedPricing::COL_TIER_PRICE_TYPE => AdvancedPricing::TIER_PRICE_TYPE_FIXED
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
                            'customer_group_id' => 'tier customer group id value',
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                            'percentage_value' => null
                        ],
                    ],
                ],
            ],
            [
                '$data' => [
                    0 => [
                        AdvancedPricing::COL_SKU => 'sku value',
                        //tier
                        AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier price website value',
                        AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'tier price customer group value - not all groups ',
                        AdvancedPricing::COL_TIER_PRICE_QTY => 'tier price qty value',
                        AdvancedPricing::COL_TIER_PRICE => 'tier price value',
                        AdvancedPricing::COL_TIER_PRICE_TYPE => AdvancedPricing::TIER_PRICE_TYPE_PERCENT
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
                            'customer_group_id' => 'tier customer group id value',
                            'qty' => 'tier price qty value',
                            'value' => 0,
                            'percentage_value' => 'tier price value',
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
                        AdvancedPricing::COL_TIER_PRICE_TYPE => AdvancedPricing::TIER_PRICE_TYPE_FIXED
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
                            'customer_group_id' => 'tier customer group id value',
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                            'percentage_value' => null
                        ],
                    ],
                ],
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
                        AdvancedPricing::COL_TIER_PRICE_TYPE => AdvancedPricing::TIER_PRICE_TYPE_FIXED
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
                            'customer_group_id' => 'tier customer group id value',
                            'qty' => 'tier price qty value',
                            'value' => 'tier price value',
                            'website_id' => 'tier website id value',
                            'percentage_value' => null
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
                '$behavior' => Import::BEHAVIOR_DELETE,
                '$expectedResult' => false,
            ],
            [
                '$rowData' => [
                    AdvancedPricing::COL_SKU => 'sku value',
                ],
                '$behavior' => Import::BEHAVIOR_DELETE,
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
                '$behavior' => Import::BEHAVIOR_DELETE,
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

        $this->advancedPricing->method('retrieveOldSkus')->willReturn($oldSkus);

        $this->connection->expects($this->exactly($callNum))
            ->method('insertOnDuplicate')
            ->with(self::TABLE_NAME, $priceIn, ['value', 'percentage_value']);

        $this->invokeMethod($this->advancedPricing, 'saveProductPrices', [$priceData, 'table']);
    }

    /**
     * @return array
     */
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
        $dbSelectMock = $this->createMock(Select::class);
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

    /**
     * @return array
     */
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
        $dbSelectMock = $this->createMock(Select::class);
        $this->connection->expects($this->once())
            ->method('fetchAll')
            ->willReturn($existingPrices);
        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($dbSelectMock);
        $dbSelectMock->expects($this->once())
            ->method('from')
            ->with(
                self::TABLE_NAME,
                [self::LINK_FIELD, 'all_groups', 'customer_group_id', 'qty']
            )->willReturnSelf();
        $this->advancedPricing->expects($this->once())
            ->method('retrieveOldSkus')
            ->willReturn($oldSkus);
        $this->advancedPricing->expects($this->exactly($numCall))
            ->method('incrementCounterUpdated')
            ->withConsecutive($args);

        $this->invokeMethod($this->advancedPricing, 'processCountExistingPrices', [$prices, 'table']);
    }

    /**
     * @return array
     */
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
     * @throws \ReflectionException
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
     * @return mixed
     * @throws \ReflectionException
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
     * @return mixed
     * @throws \ReflectionException
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
     * @return MockObject
     * @throws \ReflectionException
     */
    private function getAdvancedPricingMock($methods = [])
    {
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $metadataMock = $this->createMock(EntityMetadata::class);
        $metadataMock
            ->method('getLinkField')
            ->willReturn(self::LINK_FIELD);
        $metadataPoolMock
            ->method('getMetaData')
            ->with(ProductInterface::class)
            ->willReturn($metadataMock);
        $advancedPricingMock = $this->getMockBuilder(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class
        )
            ->setMethods($methods)
            ->setConstructorArgs(
                [
                    $this->jsonHelper,
                    $this->importExportData,
                    $this->dataSourceModel,
                    $this->resource,
                    $this->resourceHelper,
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
                ]
            )
            ->getMock();
        $reflection = new \ReflectionClass(\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($advancedPricingMock, $metadataPoolMock);

        return $advancedPricingMock;
    }
}
