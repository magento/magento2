<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;

/**
 * Class ProductTest
 * @package Magento\CatalogImportExport\Test\Unit\Model\Import
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    const MEDIA_DIRECTORY = 'media/import';

    const ENTITY_TYPE_ID = 1;

    const ENTITY_TYPE_CODE = 'catalog_product';

    const ENTITY_ID = 13;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_connection;

    /** @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonHelper;

    /** @var \Magento\ImportExport\Model\ResourceModel\Import\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dataSourceModel;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\ImportExport\Model\ResourceModel\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceHelper;

    /** @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject */
    protected $string;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_eventManager;

    /** @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockRegistry;

    /** @var \Magento\CatalogImportExport\Model\Import\Product\OptionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $optionFactory;

    /** @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockConfiguration;

    /** @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockStateProvider;

    /** @var \Magento\CatalogImportExport\Model\Import\Product\Option|\PHPUnit_Framework_MockObject_MockObject */
    protected $optionEntity;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTime;

    /** @var array */
    protected $data;

    /** @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $importExportData;

    /** @var \Magento\ImportExport\Model\ResourceModel\Import\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $importData;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\ImportExport\Model\ResourceModel\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceHelper;

    /** @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_catalogData;

    /** @var \Magento\ImportExport\Model\Import\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_importConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceFactory;

    // @codingStandardsIgnoreStart
    /** @var  \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_setColFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\Type\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_productTypeFactory;

    /** @var  \Magento\Catalog\Model\ResourceModel\Product\LinkFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_linkFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_proxyProdFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_uploaderFactory;

    /** @var  \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $_filesystem;

    /** @var  \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_mediaDirectory;

    /** @var  \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_stockResItemFac;

    /** @var  \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_localeDate;

    /** @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject  */
    protected $indexerRegistry;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject  */
    protected $_logger;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\StoreResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeResolver;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $skuProcessor;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryProcessor;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validator;

    /** @var  \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectRelationProcessor;

    /** @var  \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionManager;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor|\PHPUnit_Framework_MockObject_MockObject */
    // @codingStandardsIgnoreEnd
    protected $taxClassProcessor;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product */
    protected $importProduct;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject*/
    protected $scopeConfig;

    /** @var \Magento\Catalog\Model\Product\Url|\PHPUnit_Framework_MockObject_MockObject*/
    protected $productUrl;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        parent::setUp();

        /* For parent object construct */
        $this->jsonHelper =
            $this->getMockBuilder(\Magento\Framework\Json\Helper\Data::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->importExportData =
            $this->getMockBuilder(\Magento\ImportExport\Helper\Data::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_dataSourceModel =
            $this->getMockBuilder(\Magento\ImportExport\Model\ResourceModel\Import\Data::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->config =
            $this->getMockBuilder(\Magento\Eav\Model\Config::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->resource =
            $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->resourceHelper =
            $this->getMockBuilder(\Magento\ImportExport\Model\ResourceModel\Helper::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->string =
            $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)
                ->disableOriginalConstructor()
                ->getMock();

        /* For object construct */
        $this->_eventManager =
            $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
                ->getMock();
        $this->stockRegistry =
            $this->getMockBuilder(\Magento\CatalogInventory\Api\StockRegistryInterface::class)
                ->getMock();
        $this->stockConfiguration =
            $this->getMockBuilder(\Magento\CatalogInventory\Api\StockConfigurationInterface::class)
                ->getMock();
        $this->stockStateProvider =
            $this->getMockBuilder(\Magento\CatalogInventory\Model\Spi\StockStateProviderInterface::class)
                ->getMock();
        $this->_catalogData =
            $this->getMockBuilder(\Magento\Catalog\Helper\Data::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_importConfig =
            $this->getMockBuilder(\Magento\ImportExport\Model\Import\Config::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_resourceFactory = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_setColFactory = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_productTypeFactory = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product\Type\Factory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_linkFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\LinkFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_proxyProdFactory = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_uploaderFactory = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\UploaderFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_filesystem =
            $this->getMockBuilder(\Magento\Framework\Filesystem::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_mediaDirectory =
            $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
                ->getMock();
        $this->_stockResItemFac = $this->getMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_localeDate =
            $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
                ->getMock();
        $this->dateTime =
            $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->indexerRegistry =
            $this->getMockBuilder(\Magento\Framework\Indexer\IndexerRegistry::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_logger =
            $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
                ->getMock();
        $this->storeResolver =
            $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\StoreResolver::class)
                ->setMethods([
                    'getStoreCodeToId',
                ])
                ->disableOriginalConstructor()
                ->getMock();
        $this->skuProcessor =
            $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\SkuProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->categoryProcessor =
            $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->validator =
            $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Validator::class)
                ->setMethods(['isAttributeValid', 'getMessages', 'isValid'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->objectRelationProcessor =
            $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->transactionManager =
            $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface::class)
                ->getMock();

        $this->taxClassProcessor =
            $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productUrl = $this->getMockBuilder(\Magento\Catalog\Model\Product\Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorAggregator = $this->getErrorAggregatorObject();

        $this->data = [];

        $this->_objectConstructor()
            ->_parentObjectConstructor()
            ->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->importProduct = $objectManager->getObject(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            [
                'jsonHelper' => $this->jsonHelper,
                'importExportData' => $this->importExportData,
                'importData' => $this->_dataSourceModel,
                'config' => $this->config,
                'resource' => $this->resource,
                'resourceHelper' => $this->resourceHelper,
                'string' => $this->string,
                'errorAggregator' => $this->errorAggregator,
                'eventManager' => $this->_eventManager,
                'stockRegistry' => $this->stockRegistry,
                'stockConfiguration' => $this->stockConfiguration,
                'stockStateProvider' => $this->stockStateProvider,
                'catalogData' => $this->_catalogData,
                'importConfig' => $this->_importConfig,
                'resourceFactory' => $this->_resourceFactory,
                'optionFactory' => $this->optionFactory,
                'setColFactory' => $this->_setColFactory,
                'productTypeFactory' => $this->_productTypeFactory,
                'linkFactory' => $this->_linkFactory,
                'proxyProdFactory' => $this->_proxyProdFactory,
                'uploaderFactory' => $this->_uploaderFactory,
                'filesystem' => $this->_filesystem,
                'stockResItemFac' => $this->_stockResItemFac,
                'localeDate' => $this->_localeDate,
                'dateTime' => $this->dateTime,
                'logger' => $this->_logger,
                'indexerRegistry' => $this->indexerRegistry,
                'storeResolver' => $this->storeResolver,
                'skuProcessor' => $this->skuProcessor,
                'categoryProcessor' => $this->categoryProcessor,
                'validator' => $this->validator,
                'objectRelationProcessor' => $this->objectRelationProcessor,
                'transactionManager' => $this->transactionManager,
                'taxClassProcessor' => $this->taxClassProcessor,
                'scopeConfig' => $this->scopeConfig,
                'productUrl' => $this->productUrl,
                'data' => $this->data
            ]
        );
    }

    /**
     * @return $this
     */
    protected function _objectConstructor()
    {
        $this->optionFactory = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product\OptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionEntity = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Option::class)
            ->disableOriginalConstructor()->getMock();
        $this->optionFactory->expects($this->once())->method('create')->willReturn($this->optionEntity);

        $this->_filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue(self::MEDIA_DIRECTORY));

        $this->validator->expects($this->any())->method('init');
        return $this;
    }

    /**
     * @return $this
     */
    protected function _parentObjectConstructor()
    {
        $type = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)->disableOriginalConstructor()->getMock();
        $type->expects($this->any())->method('getEntityTypeId')->will($this->returnValue(self::ENTITY_TYPE_ID));
        $this->config->expects($this->any())->method('getEntityType')->with(self::ENTITY_TYPE_CODE)->willReturn($type);

        $this->_connection = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->_connection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initAttributeSets()
    {
        $attributeSetOne = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Set::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetOne->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn('attributeSet1');
        $attributeSetOne->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $attributeSetTwo = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Set::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetTwo->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn('attributeSet2');
        $attributeSetTwo->expects($this->any())
            ->method('getId')
            ->willReturn('2');
        $attributeSetCol = [$attributeSetOne, $attributeSetTwo];
        $collection = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('setEntityTypeFilter')
            ->with(self::ENTITY_TYPE_ID)
            ->willReturn($attributeSetCol);
        $this->_setColFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initTypeModels()
    {
        $entityTypes = [
            'simple' => [
                'model' => 'simple_product',
                'params' => [],
            ]];
        $productTypeInstance =
            $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class)
                ->disableOriginalConstructor()->getMock();
        $productTypeInstance->expects($this->once())
            ->method('isSuitable')
            ->willReturn(true);
        $productTypeInstance->expects($this->once())
            ->method('getParticularAttributes')
            ->willReturn([]);
        $productTypeInstance->expects($this->once())
            ->method('getCustomFieldsMapping')
            ->willReturn([]);
        $this->_importConfig->expects($this->once())
            ->method('getEntityTypes')
            ->with(self::ENTITY_TYPE_CODE)
            ->willReturn($entityTypes);
        $this->_productTypeFactory->expects($this->once())->method('create')->willReturn($productTypeInstance);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initSkus()
    {
        $this->skuProcessor->expects($this->once())->method('setTypeModels');
        $this->skuProcessor->expects($this->once())->method('getOldSkus');
        return $this;
    }

    public function testSaveProductAttributes()
    {
        $testTable = 'test_table';
        $attributeId = 'test_attribute_id';
        $storeId = 'test_store_id';
        $testSku = 'test_sku';
        $attributesData = [
            $testTable => [
                $testSku => [
                    $attributeId => [
                        $storeId => [
                            'foo' => 'bar'
                        ]
                    ]
                ]
            ]
        ];
        $this->skuProcessor->expects($this->once())
            ->method('getNewSku')
            ->with($testSku)
            ->willReturn(['entity_id' => self::ENTITY_ID]);
        $this->_connection->expects($this->any())
            ->method('quoteInto')
            ->willReturnCallback([$this, 'returnQuoteCallback']);

        $tableData[] = [
            'entity_id' => self::ENTITY_ID,
            'attribute_id' => $attributeId,
            'store_id' => $storeId,
            'value' => $attributesData[$testTable][$testSku][$attributeId][$storeId],
        ];
        $this->_connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($testTable, $tableData, ['value']);
        $object = $this->invokeMethod($this->importProduct, '_saveProductAttributes', [$attributesData]);
        $this->assertEquals($this->importProduct, $object);
    }

    /**
     * @dataProvider isAttributeValidAssertAttrValidDataProvider
     */
    public function testIsAttributeValidAssertAttrValid($attrParams, $rowData)
    {
        $attrCode = 'code';
        $rowNum = 0;
        $string = $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)->setMethods(null)->getMock();
        $this->setPropertyValue($this->importProduct, 'string', $string);

        $this->validator->expects($this->once())->method('isAttributeValid')->willReturn(true);

        $result = $this->importProduct->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider isAttributeValidAssertAttrInvalidDataProvider
     */
    public function testIsAttributeValidAssertAttrInvalid($attrParams, $rowData)
    {
        $attrCode = 'code';
        $rowNum = 0;
        $string = $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)->setMethods(null)->getMock();
        $this->setPropertyValue($this->importProduct, 'string', $string);

        $this->validator->expects($this->once())->method('isAttributeValid')->willReturn(false);
        $messages = ['validator message'];
        $this->validator->expects($this->once())->method('getMessages')->willReturn($messages);

        $result = $this->importProduct->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
        $this->assertFalse($result);
    }

    public function testGetMultipleValueSeparatorDefault()
    {
        $this->setPropertyValue($this->importProduct, '_parameters', null);
        $this->assertEquals(
            Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
            $this->importProduct->getMultipleValueSeparator()
        );
    }

    public function testGetMultipleValueSeparatorFromParameters()
    {
        $expectedSeparator = 'value';
        $this->setPropertyValue($this->importProduct, '_parameters', [
            \Magento\ImportExport\Model\Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => $expectedSeparator,
        ]);

        $this->assertEquals(
            $expectedSeparator,
            $this->importProduct->getMultipleValueSeparator()
        );
    }

    public function testDeleteProductsForReplacement()
    {
        $importProduct = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setParameters', '_deleteProducts'
            ])
            ->getMock();

        $importProduct->expects($this->once())->method('setParameters')->with(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
            ]
        );
        $importProduct->expects($this->once())->method('_deleteProducts');

        $result = $importProduct->deleteProductsForReplacement();

        $this->assertEquals($importProduct, $result);
    }

    public function testGetMediaGalleryAttributeIdIfNotSetYet()
    {
        // reset possible existing id
        $this->setPropertyValue($this->importProduct, '_mediaGalleryAttributeId', null);

        $expectedId = '100';
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $attribute->expects($this->once())->method('getId')->willReturn($expectedId);
        $resource = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $resource->expects($this->once())->method('getAttribute')->willReturn($attribute);
        $this->_resourceFactory->expects($this->once())->method('create')->willReturn($resource);

        $result = $this->importProduct->getMediaGalleryAttributeId();
        $this->assertEquals($expectedId, $result);
    }

    /**
     * @dataProvider getRowScopeDataProvider
     */
    public function testGetRowScope($rowData, $expectedResult)
    {
        $result = $this->importProduct->getRowScope($rowData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testValidateRowIsAlreadyValidated()
    {
        $rowNum = 0;
        $this->setPropertyValue($this->importProduct, '_validatedRows', [$rowNum => true]);
        $result = $this->importProduct->validateRow([], $rowNum);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider validateRowDataProvider
     */
    public function testValidateRow($rowScope, $oldSku, $expectedResult, $behaviour = Import::BEHAVIOR_DELETE)
    {
        $importProduct = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBehavior', 'getRowScope', 'getErrorAggregator'])
            ->getMock();
        $importProduct
            ->expects($this->any())
            ->method('getBehavior')
            ->willReturn($behaviour);
        $importProduct
            ->method('getErrorAggregator')
            ->willReturn($this->getErrorAggregatorObject());
        $importProduct->expects($this->once())->method('getRowScope')->willReturn($rowScope);
        $skuKey = \Magento\CatalogImportExport\Model\Import\Product::COL_SKU;
        $rowData = [
            $skuKey => 'sku',
        ];
        $this->setPropertyValue($importProduct, '_oldSku', [$rowData[$skuKey] => $oldSku]);
        $rowNum = 0;
        $result = $importProduct->validateRow($rowData, $rowNum);
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateRowDeleteBehaviourAddRowErrorCall()
    {
        $importProduct = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBehavior', 'getRowScope', 'addRowError'])
            ->getMock();

        $importProduct->expects($this->exactly(2))->method('getBehavior')
            ->willReturn(\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE);
        $importProduct->expects($this->once())->method('getRowScope')
            ->willReturn(\Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT);
        $importProduct->expects($this->once())->method('addRowError');
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => 'sku',
        ];

        $importProduct->validateRow($rowData, 0);
    }

    public function testValidateRowValidatorCheck()
    {
        $messages = ['validator message'];
        $this->validator->expects($this->once())->method('getMessages')->willReturn($messages);
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => 'sku',
        ];
        $rowNum = 0;
        $this->importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * Cover getProductWebsites().
     */
    public function testGetProductWebsites()
    {
        $productSku = 'productSku';
        $productValue = [
            'key 1' => 'val',
            'key 2' => 'val',
            'key 3' => 'val',
        ];
        $expectedResult = array_keys($productValue);
        $this->setPropertyValue($this->importProduct, 'websitesCache', [
            $productSku => $productValue
        ]);

        $actualResult = $this->importProduct->getProductWebsites($productSku);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover getProductCategories().
     */
    public function testGetProductCategories()
    {
        $productSku = 'productSku';
        $productValue = [
            'key 1' => 'val',
            'key 2' => 'val',
            'key 3' => 'val',
        ];
        $expectedResult = array_keys($productValue);
        $this->setPropertyValue($this->importProduct, 'categoriesCache', [
            $productSku => $productValue
        ]);

        $actualResult = $this->importProduct->getProductCategories($productSku);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover getStoreIdByCode().
     *
     * @dataProvider getStoreIdByCodeDataProvider
     */
    public function testGetStoreIdByCode($storeCode, $expectedResult)
    {
        $this->storeResolver
            ->expects($this->any())
            ->method('getStoreCodeToId')
            ->willReturn('getStoreCodeToId value');

        $actualResult = $this->importProduct->getStoreIdByCode($storeCode);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover getNewSku().
     */
    public function testGetNewSku()
    {
        $expectedSku = 'value';
        $expectedResult = 'result value';

        $this->skuProcessor
            ->expects($this->any())
            ->method('getNewSku')
            ->with($expectedSku)
            ->willReturn($expectedResult);

        $actualResult = $this->importProduct->getNewSku($expectedSku);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover getCategoryProcessor().
     */
    public function testGetCategoryProcessor()
    {
        $expectedResult = 'value';
        $this->setPropertyValue($this->importProduct, 'categoryProcessor', $expectedResult);

        $actualResult = $this->importProduct->getCategoryProcessor();
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function getStoreIdByCodeDataProvider()
    {
        return [
            [
                '$storeCode' => null,
                '$expectedResult' => \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT,
            ],
            [
                '$storeCode' => 'value',
                '$expectedResult' => 'getStoreCodeToId value',
            ],
        ];
    }

    /**
     * @dataProvider validateRowCheckSpecifiedSkuDataProvider
     */
    public function testValidateRowCheckSpecifiedSku($sku, $expectedError)
    {
        $importProduct = $this->createModelMockWithErrorAggregator(
            [ 'addRowError', 'getOptionEntity', 'getRowScope'],
            ['isRowInvalid' => true]
        );

        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
            \Magento\CatalogImportExport\Model\Import\Product::COL_STORE => '',
        ];

        $this->storeResolver->expects($this->any())->method('getStoreCodeToId')->willReturn(null);
        $this->setPropertyValue($importProduct, 'storeResolver', $this->storeResolver);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct
            ->expects($this->once())
            ->method('getRowScope')
            ->willReturn(\Magento\CatalogImportExport\Model\Import\Product::SCOPE_STORE);
        $importProduct->expects($this->at(1))->method('addRowError')->with($expectedError, $rowNum)->willReturn(null);

        $importProduct->validateRow($rowData, $rowNum);
    }

    public function testValidateRowProcessEntityIncrement()
    {
        $count = 0;
        $rowNum = 0;
        $errorAggregator = $this->getErrorAggregatorObject(['isRowInvalid']);
        $errorAggregator->method('isRowInvalid')->willReturn(true);
        $this->setPropertyValue($this->importProduct, '_processedEntitiesCount', $count);
        $this->setPropertyValue($this->importProduct, 'errorAggregator', $errorAggregator);
        $rowData = [\Magento\CatalogImportExport\Model\Import\Product::COL_SKU => false];
        //suppress validator
        $this->_setValidatorMockInImportProduct($this->importProduct);
        $this->importProduct->validateRow($rowData, $rowNum);
        $this->assertEquals(++$count, $this->importProduct->getProcessedEntitiesCount());
    }

    public function testValidateRowValidateExistingProductTypeAddNewSku()
    {
        $importProduct = $this->createModelMockWithErrorAggregator(
            [ 'addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
        ];
        $oldSku = [
            $sku => [
                'entity_id' => 'entity_id_val',
                'type_id' => 'type_id_val',
                'attr_set_id' => 'attr_set_id_val',
            ],
        ];

        $_productTypeModels = [
            $oldSku[$sku]['type_id'] => 'type_id_val_val',
        ];
        $this->setPropertyValue($importProduct, '_productTypeModels', $_productTypeModels);

        $_attrSetIdToName = [
            $oldSku[$sku]['attr_set_id'] => 'attr_set_code_val'
        ];
        $this->setPropertyValue($importProduct, '_attrSetIdToName', $_attrSetIdToName);

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);

        $expectedData = [
            'entity_id' =>  $oldSku[$sku]['entity_id'], //entity_id_val
            'type_id' => $oldSku[$sku]['type_id'],// type_id_val
            'attr_set_id' => $oldSku[$sku]['attr_set_id'], //attr_set_id_val
            'attr_set_code' => $_attrSetIdToName[$oldSku[$sku]['attr_set_id']],//attr_set_id_val
        ];
        $this->skuProcessor->expects($this->once())->method('addNewSku')->with($sku, $expectedData);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    public function testValidateRowValidateExistingProductTypeAddErrorRowCall()
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
        ];
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val',
            ],
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $importProduct->expects($this->once())->method('addRowError')->with(
            \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_TYPE_UNSUPPORTED,
            $rowNum
        );

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    public function testValidateRowValidateExistingProductTypeResetSku()
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
        ];
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val',
            ],
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(['getOptionEntity']);

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);

        //suppress option validation
        $this->_rewriteGetOptionEntityInImportProduct($importProduct);
        //suppress validator
        $this->_setValidatorMockInImportProduct($importProduct);

        $expectedSku = false;
        $newSku = [
            'attr_set_code' => 'new_attr_set_code',
            'type_id' => 'new_type_id_val',
        ];
        $this->skuProcessor->expects($this->once())->method('getNewSku')->with($expectedSku)->willReturn($newSku);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);
        $productType = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setPropertyValue($importProduct, '_productTypeModels', [
            $newSku['type_id'] => $productType
        ]);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @dataProvider validateRowValidateNewProductTypeAddRowErrorCallDataProvider
     */
    public function testValidateRowValidateNewProductTypeAddRowErrorCall(
        $colType,
        $productTypeModelsColType,
        $colAttrSet,
        $attrSetNameToIdColAttrSet,
        $error
    ) {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
            \Magento\CatalogImportExport\Model\Import\Product::COL_TYPE => $colType,
            \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET => $colAttrSet,
        ];
        $_attrSetNameToId = [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => $attrSetNameToIdColAttrSet,
        ];
        $_productTypeModels = [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE] => $productTypeModelsColType,
        ];
        $oldSku = [
            $sku => null,
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $this->setPropertyValue($importProduct, '_productTypeModels', $_productTypeModels);
        $this->setPropertyValue($importProduct, '_attrSetNameToId', $_attrSetNameToId);

        $importProduct->expects($this->once())->method('addRowError')->with(
            $error,
            $rowNum
        );
        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    public function testValidateRowValidateNewProductTypeGetNewSkuCall()
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
            \Magento\CatalogImportExport\Model\Import\Product::COL_TYPE => 'value',
            \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET => 'value',
        ];
        $_productTypeModels = [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE] => 'value',
        ];
        $oldSku = [
            $sku => null,
        ];
        $_attrSetNameToId = [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => 'attr_set_code_val'
        ];
        $expectedData = [
            'entity_id' => null,
            'type_id' => $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE],//value
            //attr_set_id_val
            'attr_set_id' =>
                $_attrSetNameToId[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET]],
            'attr_set_code' => $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET],//value
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $this->setPropertyValue($importProduct, '_productTypeModels', $_productTypeModels);
        $this->setPropertyValue($importProduct, '_attrSetNameToId', $_attrSetNameToId);

        $this->skuProcessor->expects($this->once())->method('getNewSku')->willReturn(null);
        $this->skuProcessor->expects($this->once())->method('addNewSku')->with($sku, $expectedData);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    public function testValidateRowValidateNewProductTypeResetSku()
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
            \Magento\CatalogImportExport\Model\Import\Product::COL_TYPE => 'value',
            \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET => 'value',
        ];

        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val',
            ],
        ];
        $_productTypeModels = [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE] => 'value',
        ];
        $_attrSetNameToId = [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => 'attr_set_code_val'
        ];

        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => false]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $this->setPropertyValue($importProduct, '_productTypeModels', $_productTypeModels);
        $this->setPropertyValue($importProduct, '_attrSetNameToId', $_attrSetNameToId);

        $this->_rewriteGetOptionEntityInImportProduct($importProduct);//suppress option validation
        $this->_setValidatorMockInImportProduct($importProduct);//suppress validator

        $expectedSku = false;
        $newSku = [
            'attr_set_code' => 'new_attr_set_code',
            'type_id' => 'new_type_id_val',
        ];
        $this->skuProcessor->expects($this->once())->method('getNewSku')->with($expectedSku)->willReturn($newSku);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);
        $productType = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setPropertyValue($importProduct, '_productTypeModels', [
            $newSku['type_id'] => $productType
        ]);

        $importProduct->validateRow($rowData, $rowNum);
    }

    public function testValidateDefaultScopeNotValidAttributesResetSku()
    {
        $sku = 'sku';
        $rowNum = 0;
        $attrCode = 'code';
        $stringUtilsMock = $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)
            ->setMethods(null)
            ->getMock();
        $this->setPropertyValue($this->importProduct, 'string', $stringUtilsMock);

        $scopeMock = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['getRowScope'],
            [],
            '',
            false
        );

        $colStore = \Magento\CatalogImportExport\Model\Import\Product::COL_STORE;
        $scopeRowData = [
            $sku => 'sku',
            $colStore => null,
        ];
        $scopeResult = \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT;
        $scopeMock->expects($this->any())->method('getRowScope')->with($scopeRowData)->willReturn($scopeResult);
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val',
            ],
        ];

        $this->setPropertyValue($this->importProduct, '_oldSku', $oldSku);

        $expectedSku = false;
        $newSku = [
            'attr_set_code' => 'new_attr_set_code',
            'type_id' => 'new_type_id_val',
        ];
        $this->skuProcessor->expects($this->any())->method('getNewSku')->with($expectedSku)->willReturn($newSku);
        $this->setPropertyValue($this->importProduct, 'skuProcessor', $this->skuProcessor);

        $attrParams = [
            'type' => 'varchar',
        ];
        $attrRowData = [
            'code' => str_repeat(
                'a',
                \Magento\CatalogImportExport\Model\Import\Product::DB_MAX_VARCHAR_LENGTH + 1
            ),
        ];

        $this->validator->expects($this->once())->method('isAttributeValid')->willReturn(false);
        $messages = ['validator message'];
        $this->validator->expects($this->once())->method('getMessages')->willReturn($messages);

        $result = $this->importProduct->isAttributeValid($attrCode, $attrParams, $attrRowData, $rowNum);
        $this->assertFalse($result);
    }

    public function testValidateRowSetAttributeSetCodeIntoRowData()
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
            \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET => 'col_attr_set_val',
        ];
        $expectedAttrSetCode = 'new_attr_set_code';
        $newSku = [
            'attr_set_code' => $expectedAttrSetCode,
            'type_id' => 'new_type_id_val',
        ];
        $expectedRowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
            \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET => $newSku['attr_set_code'],
        ];
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val',
            ],
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(['getOptionEntity']);

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $this->skuProcessor->expects($this->any())->method('getNewSku')->willReturn($newSku);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);

        $productType = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productType->expects($this->once())->method('isRowValid')->with($expectedRowData);
        $this->setPropertyValue($importProduct, '_productTypeModels', [
            $newSku['type_id'] => $productType
        ]);

        //suppress option validation
        $this->_rewriteGetOptionEntityInImportProduct($importProduct);
        //suppress validator
        $this->_setValidatorMockInImportProduct($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    public function testValidateValidateOptionEntity()
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            \Magento\CatalogImportExport\Model\Import\Product::COL_SKU => $sku,
            \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET => 'col_attr_set_val',
        ];
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val',
            ],
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);

        //suppress validator
        $this->_setValidatorMockInImportProduct($importProduct);

        $option = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->once())->method('validateRow')->with($rowData, $rowNum);
        $importProduct->expects($this->once())->method('getOptionEntity')->willReturn($option);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @dataProvider getImagesFromRowDataProvider
     */
    public function testGetImagesFromRow($rowData, $expectedResult)
    {
        $this->assertEquals(
            $this->importProduct->getImagesFromRow($rowData),
            $expectedResult
        );
    }

    /**
     * @return array
     */
    public function getImagesFromRowDataProvider()
    {
        return [
            [
                [],
                [[], []]
            ],
            [
                [
                    'image' => 'image3.jpg',
                    '_media_image' => 'image1.jpg,image2.png',
                    '_media_image_label' => 'label1,label2'
                ],
                [
                    [
                        'image' => ['image3.jpg'],
                        '_media_image' => ['image1.jpg', 'image2.png']
                    ],
                    [
                        '_media_image' => ['label1', 'label2']
                    ],
                ]
            ]
        ];
    }

    public function validateRowValidateNewProductTypeAddRowErrorCallDataProvider()
    {
        return [
            [
                '$colType' => null,
                '$productTypeModelsColType' => 'value',
                '$colAttrSet' => null,
                '$attrSetNameToIdColAttrSet' => null,
                '$error' => \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_INVALID_TYPE
            ],
            [
                '$colType' => 'value',
                '$productTypeModelsColType' => null,
                '$colAttrSet' => null,
                '$attrSetNameToIdColAttrSet' => null,
                '$error' => \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_INVALID_TYPE,
            ],
            [
                '$colType' => 'value',
                '$productTypeModelsColType' => 'value',
                '$colAttrSet' => null,
                '$attrSetNameToIdColAttrSet' => 'value',
                '$error' => \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_INVALID_ATTR_SET,
            ],
            [
                '$colType' => 'value',
                '$productTypeModelsColType' => 'value',
                '$colAttrSet' => 'value',
                '$attrSetNameToIdColAttrSet' => null,
                '$error' => \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_INVALID_ATTR_SET,
            ],
        ];
    }

    public function validateRowCheckSpecifiedSkuDataProvider()
    {
        return [
            [
                '$sku' => null,
                '$expectedError' => \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_SKU_IS_EMPTY,
            ],
            [
                '$sku' => false,
                '$expectedError' => \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_ROW_IS_ORPHAN,
            ],
            [
                '$sku' => 'sku',
                '$expectedError' => \Magento\CatalogImportExport\Model\Import\Product\Validator::ERROR_INVALID_STORE,
            ],
        ];
    }

    public function validateRowDataProvider()
    {
        return [
            [
                '$rowScope' => \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT,
                '$oldSku' => null,
                '$expectedResult' => false,
            ],
            [
                '$rowScope' => null,
                '$oldSku' => null,
                '$expectedResult' => true,
            ],
            [
                '$rowScope' => null,
                '$oldSku' => true,
                '$expectedResult' => true,
            ],
            [
                '$rowScope' => \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT,
                '$oldSku' => true,
                '$expectedResult' => true,
            ],
            [
                '$rowScope' => \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT,
                '$oldSku' => null,
                '$expectedResult' => false,
                '$behaviour' => Import::BEHAVIOR_REPLACE
            ],
        ];
    }

    /**
     * @return array
     */
    public function isAttributeValidAssertAttrValidDataProvider()
    {
        return [
            [
                '$attrParams' => [
                    'type' => 'varchar',
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        \Magento\CatalogImportExport\Model\Import\Product::DB_MAX_VARCHAR_LENGTH - 1
                    ),
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'decimal',
                ],
                '$rowData' => [
                    'code' => 10,
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'select',
                    'options' => ['code' => 1]
                ],
                '$rowData' => [
                    'code' => 'code',
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'multiselect',
                    'options' => ['code' => 1]
                ],
                '$rowData' => [
                    'code' => 'code',
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'int',
                ],
                '$rowData' => [
                    'code' => 1000,
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'datetime',
                ],
                '$rowData' => [
                    'code' => "5 September 2015",
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'text',
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        \Magento\CatalogImportExport\Model\Import\Product::DB_MAX_TEXT_LENGTH - 1
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function isAttributeValidAssertAttrInvalidDataProvider()
    {
        return [
            [
                '$attrParams' => [
                    'type' => 'varchar',
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        \Magento\CatalogImportExport\Model\Import\Product::DB_MAX_VARCHAR_LENGTH + 1
                    ),
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'decimal',
                ],
                '$rowData' => [
                    'code' => 'incorrect',
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'select',
                    'not options' => null,
                ],
                '$rowData' => [
                    'code' => 'code',
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'multiselect',
                    'not options' => null,
                ],
                '$rowData' => [
                    'code' => 'code',
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'int',
                ],
                '$rowData' => [
                    'code' => 'not int',
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'datetime',
                ],
                '$rowData' => [
                    'code' => "incorrect datetime",
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'text',
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        \Magento\CatalogImportExport\Model\Import\Product::DB_MAX_TEXT_LENGTH + 1
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getRowScopeDataProvider()
    {
        $colSku = \Magento\CatalogImportExport\Model\Import\Product::COL_SKU;
        $colStore = \Magento\CatalogImportExport\Model\Import\Product::COL_STORE;

        return [
            [
                '$rowData' => [
                    $colSku => null,
                    $colStore => 'store',
                ],
                '$expectedResult' => \Magento\CatalogImportExport\Model\Import\Product::SCOPE_STORE
            ],
            [
                '$rowData' => [
                    $colSku => 'sku',
                    $colStore => null,
                ],
                '$expectedResult' => \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT
            ],
            [
                '$rowData' => [
                    $colSku => 'sku',
                    $colStore => 'store',
                ],
                '$expectedResult' => \Magento\CatalogImportExport\Model\Import\Product::SCOPE_STORE
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function returnQuoteCallback()
    {
        $args = func_get_args();
        return str_replace('?', (is_array($args[1]) ? implode(',', $args[1]) : $args[1]), $args[0]);
    }

    /**
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
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
     * @param $object
     * @param $property
     */
    protected function getPropertyValue(&$object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function overrideMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));

        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Used in group of validateRow method's tests.
     * Suppress part of validateRow func-ty to run some tests separately and bypass errors.
     *
     * @see _rewriteGetOptionEntityInImportProduct()
     * @see _setValidatorMockInImportProduct()
     * @param \Magento\CatalogImportExport\Model\Import\Product
     *  Param should go with rewritten getOptionEntity method.
     * @return \Magento\CatalogImportExport\Model\Import\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private function _suppressValidateRowOptionValidatorInvalidRows($importProduct)
    {
        //suppress option validation
        $this->_rewriteGetOptionEntityInImportProduct($importProduct);
        //suppress validator
        $this->_setValidatorMockInImportProduct($importProduct);

        return $importProduct;
    }

    /**
     * Used in group of validateRow method's tests.
     * Set validator mock in importProduct, return true for isValid method.
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product
     * @return \Magento\CatalogImportExport\Model\Import\Product\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function _setValidatorMockInImportProduct($importProduct)
    {
        $this->validator->expects($this->once())->method('isValid')->willReturn(true);
        $this->setPropertyValue($importProduct, 'validator', $this->validator);

        return $importProduct;
    }

    /**
     * Used in group of validateRow method's tests.
     * Make getOptionEntity return option mock.
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product
     *  Param should go with rewritten getOptionEntity method.
     * @return \Magento\CatalogImportExport\Model\Import\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private function _rewriteGetOptionEntityInImportProduct($importProduct)
    {
        $option = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importProduct->expects($this->once())->method('getOptionEntity')->willReturn($option);

        return $importProduct;
    }

    /**
     * @param array $methods
     * @param array $errorAggregatorMethods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createModelMockWithErrorAggregator(array $methods = [], array $errorAggregatorMethods = [])
    {
        $methods[] = 'getErrorAggregator';
        $importProduct = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        $errorMethods = array_keys($errorAggregatorMethods);
        $errorAggregator = $this->getErrorAggregatorObject($errorMethods);
        foreach ($errorAggregatorMethods as $method => $result) {
            $errorAggregator->method($method)->willReturn($result);
        }
        $importProduct->method('getErrorAggregator')->willReturn($errorAggregator);

        return $importProduct;
    }
}
