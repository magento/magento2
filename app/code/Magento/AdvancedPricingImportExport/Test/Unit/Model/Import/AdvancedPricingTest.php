<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPrice;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory as ResourceFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Helper\Data as ImportExportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AdvancedPricingTest extends AbstractImportTestCase
{
    const TABLE_NAME = 'tableName';
    const LINK_FIELD = 'linkField';

    /**
     * @var ResourceFactory|MockObject
     */
    private $resourceFactoryMock;

    /**
     * @var CatalogHelper|MockObject
     */
    private $catalogHelperMock;

    /**
     * @var StoreResolver|MockObject
     */
    private $storeResolverMock;

    /**
     * @var Product|MockObject
     */
    private $importProductMock;

    /**
     * @var ProductModel|MockObject
     */
    private $productModelMock;

    /**
     * @var Validator|MockObject
     */
    private $validatorMock;

    /**
     * @var Website|MockObject
     */
    private $websiteValidatorMock;

    /**
     * @var TierPrice|MockObject
     */
    private $tierPriceValidatorMock;

    /**
     * @var Helper|MockObject
     */
    private $resourceHelperMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Data|MockObject
     */
    private $dataSourceModelMock;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $dateTimeMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var JsonHelper|MockObject
     */
    private $jsonHelperMock;

    /**
     * @var ImportExportHelper|MockObject
     */
    private $importExportHelperMock;

    /**
     * @var AdvancedPricing|MockObject
     */
    private $advancedPricingMock;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var ProcessingErrorAggregatorInterface
     */
    private $errorAggregator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jsonHelperMock = $this->createMock(JsonHelper::class);
        $this->importExportHelperMock = $this->createMock(ImportExportHelper::class);
        $this->resourceHelperMock = $this->createMock(Helper::class);
        $this->resourceMock = $this->createPartialMock(ResourceConnection::class, ['getConnection']);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class, [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->dataSourceModelMock = $this->createMock(Data::class);
        $this->eavConfig = $this->createMock(Config::class);
        $entityType = $this->createMock(Type::class);
        $entityType->method('getEntityTypeId')->willReturn('');
        $this->eavConfig->method('getEntityType')->willReturn($entityType);
        $this->resourceFactoryMock = $this->getMockBuilder(ResourceFactory::class)
            ->setMethods(['create', 'getTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceFactoryMock->expects($this->any())->method('create')->willReturnSelf();
        $this->resourceFactoryMock->expects($this->any())->method('getTable')->willReturn(self::TABLE_NAME);
        $this->catalogHelperMock = $this->createMock(CatalogHelper::class);
        $this->storeResolverMock = $this->createMock(StoreResolver::class);
        $this->importProductMock = $this->createMock(Product::class);
        $this->productModelMock = $this->createMock(ProductModel::class);
        $this->validatorMock = $this->createPartialMock(Validator::class, ['isValid', 'getMessages']);
        $this->websiteValidatorMock = $this->createMock(Website::class);
        $this->tierPriceValidatorMock = $this->createMock(TierPrice::class);
        $this->stringUtils = $this->createMock(StringUtils::class);
        $this->errorAggregator = $this->getErrorAggregatorObject();
        $this->dateTimeMock = $this->createPartialMock(DateTime::class, ['date', 'format']);
        $this->dateTimeMock->expects($this->any())->method('date')->willReturnSelf();

        $this->advancedPricingMock = $this->getAdvancedPricingMock(
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

        $this->advancedPricingMock->expects($this->any())->method('retrieveOldSkus')->willReturn([]);
    }

    /**
     * Test getter for entity type code.
     */
    public function testGetEntityTypeCode()
    {
        $result = $this->advancedPricingMock->getEntityTypeCode();
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
        $this->validatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $advancedPricingMock->expects($this->any())->method('getBehavior')->willReturn($behavior);

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
        $this->validatorMock->expects($this->any())->method('isValid')->willReturn(true);
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
        $rowData = [AdvancedPricing::COL_SKU => 'sku value'];
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
        $this->validatorMock->expects($this->once())->method('isValid')->willReturn(false);
        $messages = ['value'];
        $this->validatorMock->expects($this->once())->method('getMessages')->willReturn($messages);
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
        $this->dataSourceModelMock->expects($this->at(0))->method('getNextBunch')->willReturn($testBunch);
        $this->advancedPricingMock->expects($this->once())->method('validateRow')->willReturn(false);
        $this->advancedPricingMock->expects($this->any())->method('saveProductPrices')->will($this->returnSelf());

        $this->advancedPricingMock
            ->expects($this->once())
            ->method('addRowError')
            ->with(RowValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);

        $this->invokeMethod($this->advancedPricingMock, 'saveAndReplaceAdvancedPrices');
    }

    /**
     * Test method saveAdvancedPricing.
     */
    public function testSaveAdvancedPricing()
    {
        $this->advancedPricingMock
            ->expects($this->once())
            ->method('saveAndReplaceAdvancedPrices');

        $result = $this->advancedPricingMock->saveAdvancedPricing();

        $this->assertEquals($this->advancedPricingMock, $result);
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
            ->expects($this->any())
            ->method('getBehavior')
            ->willReturn(Import::BEHAVIOR_APPEND);
        $this->dataSourceModelMock->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $advancedPricing->expects($this->any())->method('validateRow')->willReturn(true);

        $advancedPricing->expects($this->any())->method('getCustomerGroupId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP], $tierCustomerGroupId],
            ]
        );

        $advancedPricing->expects($this->any())->method('getWebSiteId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_WEBSITE], $tierWebsiteId],
            ]
        );

        $oldSkus = [$sku => $skuProduct];
        $expectedTierPrices[$sku][0][self::LINK_FIELD] = $skuProduct;
        $advancedPricing->expects($this->once())->method('retrieveOldSkus')->willReturn($oldSkus);
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(self::TABLE_NAME, $expectedTierPrices[$sku], ['value', 'percentage_value']);

        $advancedPricing->expects($this->any())->method('processCountExistingPrices')->willReturnSelf();
        $advancedPricing->expects($this->any())->method('processCountNewPrices')->willReturnSelf();

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
            ->expects($this->any())
            ->method('getBehavior')
            ->willReturn(Import::BEHAVIOR_APPEND);
        $this->dataSourceModelMock->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $advancedPricing->expects($this->any())->method('validateRow')->willReturn(true);

        $advancedPricing->expects($this->any())->method('getCustomerGroupId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP], $tierCustomerGroupId],
            ]
        );

        $advancedPricing->expects($this->any())->method('getWebSiteId')->willReturnMap(
            [
                [$data[0][AdvancedPricing::COL_TIER_PRICE_WEBSITE], $tierWebsiteId],
            ]
        );

        $oldSkus = [$sku => $skuProduct];
        $expectedTierPrices[$sku][0][self::LINK_FIELD] = $skuProduct;
        $advancedPricing->expects($this->never())->method('retrieveOldSkus')->willReturn($oldSkus);
        $this->connectionMock->expects($this->never())
            ->method('insertOnDuplicate')
            ->with(self::TABLE_NAME, $expectedTierPrices[$sku], ['value', 'percentage_value']);

        $advancedPricing->expects($this->any())->method('processCountExistingPrices')->willReturnSelf();
        $advancedPricing->expects($this->any())->method('processCountNewPrices')->willReturnSelf();

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
        $this->advancedPricingMock->expects($this->any())->method('getBehavior')->willReturn(Import::BEHAVIOR_REPLACE);
        $this->dataSourceModelMock->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricingMock->expects($this->once())->method('validateRow')->willReturn(true);

        $this->advancedPricingMock
            ->expects($this->never())
            ->method('getCustomerGroupId');
        $this->advancedPricingMock
            ->expects($this->never())
            ->method('getWebSiteId');

        $this->advancedPricingMock
            ->expects($this->any())
            ->method('deleteProductTierPrices')
            ->withConsecutive([$listSku, AdvancedPricing::TABLE_TIER_PRICE])
            ->willReturn(true);

        $this->advancedPricingMock
            ->expects($this->any())
            ->method('saveProductPrices')
            ->withConsecutive([$expectedTierPrices, AdvancedPricing::TABLE_TIER_PRICE])
            ->will($this->returnSelf());

        $this->invokeMethod($this->advancedPricingMock, 'saveAndReplaceAdvancedPrices');
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

        $this->dataSourceModelMock->expects($this->at(0))->method('getNextBunch')->willReturn($data);
        $this->advancedPricingMock->expects($this->any())->method('validateRow')->willReturn(true);
        $expectedSkuList = ['sku value'];
        $this->advancedPricingMock
            ->expects($this->once())
            ->method('deleteProductTierPrices')
            ->withConsecutive(
                [$expectedSkuList, AdvancedPricing::TABLE_TIER_PRICE]
            )->will($this->returnSelf());

        $this->advancedPricingMock->deleteAdvancedPricing();
    }

    /**
     * Test method deleteAdvancedPricing() whether _cachedSkuToDelete property is set to null.
     */
    public function testDeleteAdvancedPricingResetCachedSkuToDelete()
    {
        $this->setPropertyValue($this->advancedPricingMock, '_cachedSkuToDelete', 'some value');
        $this->dataSourceModelMock->expects($this->at(0))->method('getNextBunch')->willReturn([]);

        $this->advancedPricingMock->deleteAdvancedPricing();

        $cachedSkuToDelete = $this->getPropertyValue($this->advancedPricingMock, '_cachedSkuToDelete');
        $this->assertNull($cachedSkuToDelete);
    }

    /**
     * Test method replaceAdvancedPricing().
     */
    public function testReplaceAdvancedPricing()
    {
        $this->advancedPricingMock
            ->expects($this->once())
            ->method('saveAndReplaceAdvancedPrices');

        $result = $this->advancedPricingMock->saveAdvancedPricing();

        $this->assertEquals($this->advancedPricingMock, $result);
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
     * @dataProvider saveProductPricesDataProvider
     *
     * @param array $priceData
     * @param array $oldSkus
     * @param array $priceIn
     * @param int $callNum
     * @throws \ReflectionException
     */
    public function testSaveProductPrices($priceData, $oldSkus, $priceIn, $callNum)
    {
        $this->advancedPricingMock = $this->getAdvancedPricingMock(['retrieveOldSkus']);

        $this->advancedPricingMock->expects($this->any())->method('retrieveOldSkus')->willReturn($oldSkus);

        $this->connectionMock->expects($this->exactly($callNum))
            ->method('insertOnDuplicate')
            ->with(self::TABLE_NAME, $priceIn, ['value', 'percentage_value']);

        $this->invokeMethod($this->advancedPricingMock, 'saveProductPrices', [$priceData, 'table']);
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
     * @dataProvider deleteProductTierPricesDataProvider
     *
     * @param array $listSku
     * @param array $cachedSkuToDelete
     * @param int $numCallAddError
     * @param int $numCallDelete
     * @param boolean $exceptionInDelete
     * @param boolean $result
     * @throws \ReflectionException
     */
    public function testDeleteProductTierPrices(
        $listSku,
        $cachedSkuToDelete,
        $numCallAddError,
        $numCallDelete,
        $exceptionInDelete,
        $result
    ) {
        $this->advancedPricingMock = $this->getAdvancedPricingMock(['addRowError', 'retrieveOldSkus']);
        $dbSelectMock = $this->createMock(Select::class);
        if ($listSku) {
            $this->connectionMock->expects($this->once())
                ->method('fetchCol')
                ->willReturn($cachedSkuToDelete);
            $this->connectionMock->expects($this->once())
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

        $this->advancedPricingMock->expects($this->exactly($numCallAddError))
            ->method('addRowError');
        if ($exceptionInDelete) {
            $this->connectionMock->expects($this->exactly($numCallDelete))
                ->method('delete')
                ->willThrowException(new \Exception());
        } else {
            $this->connectionMock->expects($this->exactly($numCallDelete))
                ->method('delete')
                ->willReturn(1);
        }
        $this->assertEquals(
            $result,
            $this->invokeMethod($this->advancedPricingMock, 'deleteProductTierPrices', [$listSku, 'table'])
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
     * @dataProvider processCountExistingPricesDataProvider
     *
     * @param array $prices
     * @param array $existingPrices
     * @param array $oldSkus
     * @param int $numCall
     * @param array $args
     * @throws \ReflectionException
     */
    public function testProcessCountExistingPrices(
        $prices,
        $existingPrices,
        $oldSkus,
        $numCall,
        $args
    ) {
        $this->advancedPricingMock = $this->getAdvancedPricingMock(['incrementCounterUpdated', 'retrieveOldSkus']);
        $dbSelectMock = $this->createMock(Select::class);
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($existingPrices);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($dbSelectMock);
        $dbSelectMock->expects($this->once())
            ->method('from')
            ->with(
                self::TABLE_NAME,
                [self::LINK_FIELD, 'all_groups', 'customer_group_id', 'qty']
            )->willReturnSelf();
        $this->advancedPricingMock->expects($this->once())
            ->method('retrieveOldSkus')
            ->willReturn($oldSkus);
        $this->advancedPricingMock->expects($this->exactly($numCall))
            ->method('incrementCounterUpdated')
            ->withConsecutive($args);

        $this->invokeMethod($this->advancedPricingMock, 'processCountExistingPrices', [$prices, 'table']);
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
        $class = new \ReflectionClass(AdvancedPricing::class);
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
        $metadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn(self::LINK_FIELD);
        $metadataPoolMock->expects($this->any())
            ->method('getMetaData')
            ->with(ProductInterface::class)
            ->willReturn($metadataMock);
        $advancedPricingMock = $this->getMockBuilder(AdvancedPricing::class)
            ->setMethods($methods)
            ->setConstructorArgs(
                [
                    $this->jsonHelperMock,
                    $this->importExportHelperMock,
                    $this->dataSourceModelMock,
                    $this->eavConfig,
                    $this->resourceMock,
                    $this->resourceHelperMock,
                    $this->stringUtils,
                    $this->errorAggregator,
                    $this->dateTimeMock,
                    $this->resourceFactoryMock,
                    $this->productModelMock,
                    $this->catalogHelperMock,
                    $this->storeResolverMock,
                    $this->importProductMock,
                    $this->validatorMock,
                    $this->websiteValidatorMock,
                    $this->tierPriceValidatorMock
                ]
            )
            ->getMock();
        $reflection = new \ReflectionClass(AdvancedPricing::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($advancedPricingMock, $metadataPoolMock);

        return $advancedPricingMock;
    }
}
