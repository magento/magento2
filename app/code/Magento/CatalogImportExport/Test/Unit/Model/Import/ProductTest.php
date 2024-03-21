<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Url;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\ImageTypeProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Option;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\CatalogImportExport\Model\Import\Product\Type\Factory;
use Magento\CatalogImportExport\Model\Import\Product\Validator;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel;
use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Test import entity product model
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ProductTest extends AbstractImportTestCase
{
    private const MEDIA_DIRECTORY = 'media/import';

    private const ENTITY_TYPE_ID = 1;

    private const ENTITY_TYPE_CODE = 'catalog_product';

    private const ENTITY_ID = 13;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\Json\Helper\Data|MockObject
     */
    protected $jsonHelper;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data|MockObject
     */
    protected $_dataSourceModel;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resource;

    /**
     * @var Helper|MockObject
     */
    protected $_resourceHelper;

    /**
     * @var StringUtils|MockObject
     */
    protected $string;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $_eventManager;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\OptionFactory|MockObject
     */
    protected $optionFactory;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    protected $stockConfiguration;

    /**
     * @var StockStateProviderInterface|MockObject
     */
    protected $stockStateProvider;

    /**
     * @var Option|MockObject
     */
    protected $optionEntity;

    /**
     * @var DateTime|MockObject
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Magento\ImportExport\Helper\Data|MockObject
     */
    protected $importExportData;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data|MockObject
     */
    protected $importData;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var Helper|MockObject
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Catalog\Helper\Data|MockObject
     */
    protected $_catalogData;

    /**
     * @var \Magento\ImportExport\Model\Import\Config|MockObject
     */
    protected $_importConfig;

    /**
     * @var MockObject
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory|MockObject
     */
    protected $_setColFactory;

    /**
     * @var Factory|MockObject
     */
    protected $_productTypeFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\LinkFactory|MockObject
     */
    protected $_linkFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory|MockObject
     */
    protected $_proxyProdFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory|MockObject
     */
    protected $_uploaderFactory;

    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystem;

    /**
     * @var WriteInterface|MockObject
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory|MockObject
     */
    protected $_stockResItemFac;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $_localeDate;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistry;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $_logger;

    /**
     * @var StoreResolver|MockObject
     */
    protected $storeResolver;

    /**
     * @var SkuProcessor|MockObject
     */
    protected $skuProcessor;

    /**
     * @var CategoryProcessor|MockObject
     */
    protected $categoryProcessor;

    /**
     * @var Validator|MockObject
     */
    protected $validator;

    /**
     * @var ObjectRelationProcessor|MockObject
     */
    protected $objectRelationProcessor;

    /**
     * @var TransactionManagerInterface|MockObject
     */
    protected $transactionManager;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor|MockObject
     */
    protected $taxClassProcessor;

    /**
     * @var Product
     */
    protected $importProduct;

    /**
     * @var ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var Url|MockObject
     */
    protected $productUrl;

    /**
     * @var ImageTypeProcessor|MockObject
     */
    protected $imageTypeProcessor;

    /**
     * @var DriverFile|MockObject
     */
    private $driverFile;

    /** @var Select|MockObject */
    protected $select;

    /**
     * @var SkuStorage
     */
    private $skuStorageMock;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();

        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $entityMetadataMock = $this->createMock(EntityMetadata::class);
        $metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);
        $entityMetadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        /* For parent object construct */
        $this->jsonHelper =
            $this->getMockBuilder(Data::class)
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
            $this->getMockBuilder(Config::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->resource =
            $this->getMockBuilder(ResourceConnection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->resourceHelper =
            $this->getMockBuilder(Helper::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->string =
            $this->getMockBuilder(StringUtils::class)
                ->disableOriginalConstructor()
                ->getMock();

        /* For object construct */
        $this->_eventManager =
            $this->getMockBuilder(ManagerInterface::class)
                ->getMock();
        $this->stockRegistry =
            $this->getMockBuilder(StockRegistryInterface::class)
                ->getMock();
        $this->stockConfiguration =
            $this->getMockBuilder(StockConfigurationInterface::class)
                ->getMock();
        $this->stockStateProvider =
            $this->getMockBuilder(StockStateProviderInterface::class)
                ->getMock();
        $this->_catalogData =
            $this->getMockBuilder(\Magento\Catalog\Helper\Data::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_importConfig =
            $this->getMockBuilder(\Magento\ImportExport\Model\Import\Config::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_resourceFactory = $this->createPartialMock(
            \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory::class,
            ['create']
        );
        $this->_setColFactory = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            ['create']
        );
        $this->_productTypeFactory = $this->createPartialMock(
            Factory::class,
            ['create']
        );
        $this->_linkFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\LinkFactory::class,
            ['create']
        );
        $this->_proxyProdFactory = $this->createPartialMock(
            \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory::class,
            ['create']
        );
        $this->_uploaderFactory = $this->createPartialMock(
            \Magento\CatalogImportExport\Model\Import\UploaderFactory::class,
            ['create']
        );
        $this->_filesystem =
            $this->getMockBuilder(Filesystem::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_mediaDirectory =
            $this->getMockBuilder(WriteInterface::class)
                ->getMock();
        $this->_stockResItemFac = $this->createPartialMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory::class,
            ['create']
        );
        $this->_localeDate =
            $this->getMockBuilder(TimezoneInterface::class)
                ->getMock();
        $this->dateTime =
            $this->getMockBuilder(DateTime::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->indexerRegistry =
            $this->getMockBuilder(IndexerRegistry::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->_logger =
            $this->getMockBuilder(LoggerInterface::class)
                ->getMock();
        $this->storeResolver =
            $this->getMockBuilder(StoreResolver::class)
                ->onlyMethods(['getStoreCodeToId'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->skuProcessor =
            $this->getMockBuilder(SkuProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflection = new \ReflectionClass(SkuProcessor::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->skuProcessor, $metadataPoolMock);

        $this->categoryProcessor =
            $this->getMockBuilder(CategoryProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->validator =
            $this->getMockBuilder(Validator::class)
                ->onlyMethods(['isAttributeValid', 'getMessages', 'isValid', 'init'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->objectRelationProcessor =
            $this->getMockBuilder(ObjectRelationProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->transactionManager =
            $this->getMockBuilder(TransactionManagerInterface::class)
                ->getMock();

        $this->taxClassProcessor =
            $this->getMockBuilder(TaxClassProcessor::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productUrl = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorAggregator = $this->getErrorAggregatorObject();

        $this->driverFile = $this->getMockBuilder(DriverFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->data = [];

        $this->imageTypeProcessor = $this->getMockBuilder(ImageTypeProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->skuStorageMock = $this->createMock(SkuStorage::class);

        $this->_objectConstructor()
            ->_parentObjectConstructor()
            ->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus()
            ->_initImagesArrayKeys();

        $objectManager = new ObjectManager($this);

        $this->importProduct = $objectManager->getObject(
            Product::class,
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
                'data' => $this->data,
                'imageTypeProcessor' => $this->imageTypeProcessor,
                'skuStorage' => $this->skuStorageMock
            ]
        );
        $reflection = new \ReflectionClass(Product::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->importProduct, $metadataPoolMock);
    }

    /**
     * @return $this
     */
    protected function _objectConstructor()
    {
        $this->optionFactory = $this->createPartialMock(
            \Magento\CatalogImportExport\Model\Import\Product\OptionFactory::class,
            ['create']
        );
        $this->optionEntity = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionFactory->expects($this->once())->method('create')->willReturn($this->optionEntity);

        $this->_filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->_mediaDirectory);

        $this->validator->expects($this->any())->method('init');
        return $this;
    }

    /**
     * @return $this
     */
    protected function _parentObjectConstructor()
    {
        $type = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $type->expects($this->any())->method('getEntityTypeId')->willReturn(self::ENTITY_TYPE_ID);
        $this->config->expects($this->any())->method('getEntityType')->with(self::ENTITY_TYPE_CODE)->willReturn($type);

        $this->_connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['from', 'where'])
            ->getMock();
        $this->select->expects($this->any())->method('from')->willReturnSelf();
        //$this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->_connection->expects($this->any())->method('select')->willReturn($this->select);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->_connection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initAttributeSets()
    {
        $attributeSetOne = $this->getMockBuilder(Set::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetOne->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn('attributeSet1');
        $attributeSetOne->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $attributeSetTwo = $this->getMockBuilder(Set::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetTwo->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn('attributeSet2');
        $attributeSetTwo->expects($this->any())
            ->method('getId')
            ->willReturn('2');
        $attributeSetCol = [$attributeSetOne, $attributeSetTwo];
        $collection = $this->getMockBuilder(Collection::class)
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
                'params' => []
            ]];
        $productTypeInstance =
            $this->getMockBuilder(AbstractType::class)
                ->disableOriginalConstructor()
                ->getMock();
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
        $this->skuProcessor->expects($this->never())->method('reloadOldSkus')->willReturnSelf();
        $this->skuProcessor->expects($this->never())->method('getOldSkus')->willReturn([]);
        $this->skuStorageMock->expects($this->once())->method('reset');
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initImagesArrayKeys()
    {
        $this->imageTypeProcessor->expects($this->once())->method('getImageTypes')->willReturn(
            ['image', 'small_image', 'thumbnail', 'swatch_image', '_media_image']
        );
        return $this;
    }

    /**
     * @return void
     */
    public function testSaveProductAttributes(): void
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
        $tableData[] = [
            'entity_id' => self::ENTITY_ID,
            'attribute_id' => $attributeId,
            'store_id' => $storeId,
            'value' => $attributesData[$testTable][$testSku][$attributeId][$storeId],
        ];
        $this->_connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with($testTable, $tableData, ['value']);
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $attribute->expects($this->once())->method('getId')->willReturn(1);
        $resource = $this->getMockBuilder(ResourceModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute'])
            ->getMock();
        $resource->expects($this->once())->method('getAttribute')->willReturn($attribute);
        $this->_resourceFactory->expects($this->once())->method('create')->willReturn($resource);
        $this->setPropertyValue($this->importProduct, '_oldSku', [$testSku => ['entity_id' => self::ENTITY_ID]]);
        $this->skuStorageMock->method('has')->willReturnCallback(function ($sku) use ($testSku) {
            return $sku === $testSku;
        });
        $this->skuStorageMock->method('get')->willReturnCallback(function ($sku) use ($testSku) {
            return $sku === $testSku ? ['entity_id' => self::ENTITY_ID] : null;
        });
        $object = $this->invokeMethod($this->importProduct, '_saveProductAttributes', [$attributesData]);
        $this->assertEquals($this->importProduct, $object);
    }

    /**
     * @return void
     * @dataProvider isAttributeValidAssertAttrValidDataProvider
     */
    public function testIsAttributeValidAssertAttrValid($attrParams, $rowData): void
    {
        $attrCode = 'code';
        $rowNum = 0;
        $string = $this->getMockBuilder(StringUtils::class)
            ->onlyMethods([])->getMock();
        $this->setPropertyValue($this->importProduct, 'string', $string);

        $this->validator->expects($this->once())->method('isAttributeValid')->willReturn(true);

        $result = $this->importProduct->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
        $this->assertTrue($result);
    }

    /**
     * @return void
     * @dataProvider isAttributeValidAssertAttrInvalidDataProvider
     */
    public function testIsAttributeValidAssertAttrInvalid($attrParams, $rowData): void
    {
        $attrCode = 'code';
        $rowNum = 0;
        $string = $this->getMockBuilder(StringUtils::class)
            ->onlyMethods([])->getMock();
        $this->setPropertyValue($this->importProduct, 'string', $string);

        $this->validator->expects($this->once())->method('isAttributeValid')->willReturn(false);
        $messages = ['validator message'];
        $this->validator->expects($this->once())->method('getMessages')->willReturn($messages);

        $result = $this->importProduct->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testGetMultipleValueSeparatorDefault(): void
    {
        $this->setPropertyValue($this->importProduct, '_parameters', null);
        $this->assertEquals(
            Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
            $this->importProduct->getMultipleValueSeparator()
        );
    }

    /**
     * @return void
     */
    public function testGetMultipleValueSeparatorFromParameters(): void
    {
        $expectedSeparator = 'value';
        $this->setPropertyValue(
            $this->importProduct,
            '_parameters',
            [
                Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => $expectedSeparator,
            ]
        );

        $this->assertEquals(
            $expectedSeparator,
            $this->importProduct->getMultipleValueSeparator()
        );
    }

    /**
     * @return void
     */
    public function testGetEmptyAttributeValueConstantDefault(): void
    {
        $this->setPropertyValue($this->importProduct, '_parameters', null);
        $this->assertEquals(
            Import::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT,
            $this->importProduct->getEmptyAttributeValueConstant()
        );
    }

    /**
     * @return void
     */
    public function testGetEmptyAttributeValueConstantFromParameters(): void
    {
        $expectedSeparator = '__EMPTY__VALUE__TEST__';
        $this->setPropertyValue(
            $this->importProduct,
            '_parameters',
            [
                Import::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT => $expectedSeparator
            ]
        );

        $this->assertEquals(
            $expectedSeparator,
            $this->importProduct->getEmptyAttributeValueConstant()
        );
    }

    /**
     * @return void
     */
    public function testDeleteProductsForReplacement(): void
    {
        $importProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setParameters', '_deleteProducts'])
            ->getMock();

        $importProduct->expects($this->once())->method('setParameters')->with(
            [
                'behavior' => Import::BEHAVIOR_DELETE,
            ]
        );
        $importProduct->expects($this->once())->method('_deleteProducts');

        $result = $importProduct->deleteProductsForReplacement();

        $this->assertEquals($importProduct, $result);
    }

    /**
     * @return void
     */
    public function testGetMediaGalleryAttributeIdIfNotSetYet(): void
    {
        // reset possible existing id
        $this->setPropertyValue($this->importProduct, '_mediaGalleryAttributeId', null);

        $expectedId = '100';
        $attribute = $this->getMockBuilder(AbstractAttribute::class)->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $attribute->expects($this->once())->method('getId')->willReturn($expectedId);
        $resource = $this->getMockBuilder(ResourceModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute'])
            ->getMock();
        $resource->expects($this->once())->method('getAttribute')->willReturn($attribute);
        $this->_resourceFactory->expects($this->once())->method('create')->willReturn($resource);

        $result = $this->importProduct->getMediaGalleryAttributeId();
        $this->assertEquals($expectedId, $result);
    }

    /**
     * @return void
     * @dataProvider getRowScopeDataProvider
     */
    public function testGetRowScope($rowData, $expectedResult): void
    {
        $result = $this->importProduct->getRowScope($rowData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testValidateRowIsAlreadyValidated(): void
    {
        $rowNum = 0;
        $this->setPropertyValue($this->importProduct, '_validatedRows', [$rowNum => true]);
        $result = $this->importProduct->validateRow([], $rowNum);
        $this->assertTrue($result);
    }

    /**
     * @return void
     * @dataProvider validateRowDataProvider
     */
    public function testValidateRow($rowScope, $oldSku, $expectedResult, $behaviour = Import::BEHAVIOR_DELETE): void
    {
        $importProduct = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getBehavior', 'getRowScope', 'getErrorAggregator'])
            ->getMock();
        $importProduct
            ->expects($this->any())
            ->method('getBehavior')
            ->willReturn($behaviour);
        $importProduct
            ->method('getErrorAggregator')
            ->willReturn($this->getErrorAggregatorObject());
        $importProduct->expects($this->once())->method('getRowScope')->willReturn($rowScope);
        $skuKey = Product::COL_SKU;
        $rowData = [
            $skuKey => 'sku',
        ];
        $this->setPropertyValue($importProduct, '_oldSku', [$rowData[$skuKey] => $oldSku]);
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $this->skuStorageMock->method('has')->willReturnCallback(function ($sku) use ($oldSku) {
            return $sku === 'sku' && $oldSku;
        });

        $this->skuStorageMock->method('get')->willReturnCallback(function ($sku) use ($rowData, $oldSku) {
            return $sku === 'sku' && $oldSku ? $rowData : null;
        });

        $rowNum = 0;
        $result = $importProduct->validateRow($rowData, $rowNum);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return void
     */
    public function testValidateRowDeleteBehaviourAddRowErrorCall(): void
    {
        $importProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBehavior', 'getRowScope', 'addRowError', 'getErrorAggregator'])
            ->getMock();

        $importProduct->expects($this->exactly(2))->method('getBehavior')
            ->willReturn(Import::BEHAVIOR_DELETE);
        $importProduct->expects($this->once())->method('getRowScope')
            ->willReturn(Product::SCOPE_DEFAULT);
        $importProduct->expects($this->once())->method('addRowError');
        $importProduct->method('getErrorAggregator')
            ->willReturn(
                $this->getErrorAggregatorObject(['addRowToSkip'])
            );
        $rowData = [
            Product::COL_SKU => 'sku',
        ];

        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $importProduct->validateRow($rowData, 0);
    }

    /**
     * @return void
     */
    public function testValidateRowValidatorCheck(): void
    {
        $messages = ['validator message'];
        $this->validator->expects($this->once())->method('getMessages')->willReturn($messages);
        $rowData = [
            Product::COL_SKU => 'sku',
        ];
        $rowNum = 0;
        $this->importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * Cover getProductWebsites().
     *
     * @return void
     */
    public function testGetProductWebsites(): void
    {
        $productSku = 'productSku';
        $productValue = [
            'key 1' => 'val',
            'key 2' => 'val',
            'key 3' => 'val',
        ];
        $expectedResult = array_keys($productValue);
        $this->setPropertyValue(
            $this->importProduct,
            'websitesCache',
            [
                $productSku => $productValue
            ]
        );

        $actualResult = $this->importProduct->getProductWebsites($productSku);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover getProductCategories().
     *
     * @return void
     */
    public function testGetProductCategories(): void
    {
        $productSku = 'productSku';
        $productValue = [
            'key 1' => 'val',
            'key 2' => 'val',
            'key 3' => 'val',
        ];
        $expectedResult = array_keys($productValue);
        $this->setPropertyValue(
            $this->importProduct,
            'categoriesCache',
            [
                $productSku => $productValue
            ]
        );

        $actualResult = $this->importProduct->getProductCategories($productSku);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover getStoreIdByCode().
     *
     * @return void
     * @dataProvider getStoreIdByCodeDataProvider
     */
    public function testGetStoreIdByCode($storeCode, $expectedResult): void
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
     *
     * @return void
     */
    public function testGetNewSku(): void
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
     *
     * @return void
     */
    public function testGetCategoryProcessor(): void
    {
        $expectedResult = 'value';
        $this->setPropertyValue($this->importProduct, 'categoryProcessor', $expectedResult);

        $actualResult = $this->importProduct->getCategoryProcessor();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public static function getStoreIdByCodeDataProvider(): array
    {
        return [
            [
                '$storeCode' => null,
                '$expectedResult' => Product::SCOPE_DEFAULT
            ],
            [
                '$storeCode' => 'value',
                '$expectedResult' => 'getStoreCodeToId value'
            ]
        ];
    }

    /**
     * @return void
     * @dataProvider validateRowCheckSpecifiedSkuDataProvider
     */
    public function testValidateRowCheckSpecifiedSku($sku): void
    {
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity', 'getRowScope'],
            ['isRowInvalid' => true]
        );

        $rowNum = 0;
        $rowData = [
            Product::COL_SKU => $sku,
            Product::COL_STORE => ''
        ];

        $this->storeResolver->method('getStoreCodeToId')->willReturn(null);
        $this->setPropertyValue($importProduct, 'storeResolver', $this->storeResolver);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct
            ->expects($this->once())
            ->method('getRowScope')
            ->willReturn(Product::SCOPE_STORE);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @return void
     */
    public function testValidateRowProcessEntityIncrement(): void
    {
        $count = 0;
        $rowNum = 0;
        $errorAggregator = $this->getErrorAggregatorObject(['isRowInvalid']);
        $errorAggregator->method('isRowInvalid')->willReturn(true);
        $this->setPropertyValue($this->importProduct, '_processedEntitiesCount', $count);
        $this->setPropertyValue($this->importProduct, 'errorAggregator', $errorAggregator);
        $rowData = [Product::COL_SKU => false];
        //suppress validator
        $this->_setValidatorMockInImportProduct($this->importProduct);
        $this->importProduct->validateRow($rowData, $rowNum);
        $this->assertEquals(++$count, $this->importProduct->getProcessedEntitiesCount());
    }

    /**
     * @return void
     */
    public function testValidateRowValidateExistingProductTypeAddNewSku(): void
    {
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            Product::COL_SKU => $sku,
        ];
        $oldSku = [
            $sku => [
                'entity_id' => 'entity_id_val',
                'type_id' => 'type_id_val',
                'attr_set_id' => 'attr_set_id_val'
            ]
        ];

        $_productTypeModels = [
            $oldSku[$sku]['type_id'] => 'type_id_val_val'
        ];
        $this->setPropertyValue($importProduct, '_productTypeModels', $_productTypeModels);

        $_attrSetIdToName = [
            $oldSku[$sku]['attr_set_id'] => 'attr_set_code_val'
        ];
        $this->setPropertyValue($importProduct, '_attrSetIdToName', $_attrSetIdToName);

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);

        $expectedData = [
            'entity_id' => $oldSku[$sku]['entity_id'], //entity_id_val
            'type_id' => $oldSku[$sku]['type_id'],// type_id_val
            'attr_set_id' => $oldSku[$sku]['attr_set_id'], //attr_set_id_val
            'attr_set_code' => $_attrSetIdToName[$oldSku[$sku]['attr_set_id']],//attr_set_id_val
        ];
        $this->skuProcessor->expects($this->once())->method('addNewSku')->with($sku, $expectedData);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $this->skuStorageMock->method('has')->willReturnCallback(function ($sku) use ($oldSku) {
            return isset($oldSku[$sku]);
        });
        $this->skuStorageMock->method('get')->willReturnCallback(function ($sku) use ($oldSku) {
            return $oldSku[$sku] ?? null;
        });

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @return void
     */
    public function testValidateRowValidateExistingProductTypeAddErrorRowCall(): void
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            Product::COL_SKU => $sku,
        ];
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val'
            ],
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $this->skuStorageMock->method('has')->willReturnCallback(function ($sku) use ($oldSku) {
            return isset($oldSku[$sku]);
        });
        $this->skuStorageMock->method('get')->willReturnCallback(function ($sku) use ($oldSku) {
            return $oldSku[$sku] ?? null;
        });

        $importProduct->expects($this->once())->method('addRowError')->with(
            Validator::ERROR_TYPE_UNSUPPORTED,
            $rowNum
        );

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @param string $colType
     * @param string $productTypeModelsColType
     * @param string $colAttrSet
     * @param string $attrSetNameToIdColAttrSet
     * @param string $error
     *
     * @return void
     * @dataProvider validateRowValidateNewProductTypeAddRowErrorCallDataProvider
     */
    public function testValidateRowValidateNewProductTypeAddRowErrorCall(
        $colType,
        $productTypeModelsColType,
        $colAttrSet,
        $attrSetNameToIdColAttrSet,
        $error
    ): void {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            Product::COL_SKU => $sku,
            Product::COL_TYPE => $colType,
            Product::COL_ATTR_SET => $colAttrSet
        ];
        $_attrSetNameToId = [
            $rowData[Product::COL_ATTR_SET] => $attrSetNameToIdColAttrSet
        ];
        $_productTypeModels = [
            $rowData[Product::COL_TYPE] => $productTypeModelsColType
        ];
        $oldSku = [
            $sku => null
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $this->setPropertyValue($importProduct, '_productTypeModels', $_productTypeModels);
        $this->setPropertyValue($importProduct, '_attrSetNameToId', $_attrSetNameToId);
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $importProduct->expects($this->once())->method('addRowError')->with(
            $error,
            $rowNum
        );
        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @return void
     */
    public function testValidateRowValidateNewProductTypeGetNewSkuCall(): void
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            Product::COL_SKU => $sku,
            Product::COL_TYPE => 'value',
            Product::COL_ATTR_SET => 'value'
        ];
        $_productTypeModels = [
            $rowData[Product::COL_TYPE] => 'value'
        ];
        $oldSku = [
            $sku => null
        ];
        $_attrSetNameToId = [
            $rowData[Product::COL_ATTR_SET] => 'attr_set_code_val'
        ];
        $expectedData = [
            'entity_id' => null,
            'type_id' => $rowData[Product::COL_TYPE],//value
            //attr_set_id_val
            'attr_set_id' => $_attrSetNameToId[$rowData[Product::COL_ATTR_SET]],
            'attr_set_code' => $rowData[Product::COL_ATTR_SET],//value
            'row_id' => null
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
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $this->_suppressValidateRowOptionValidatorInvalidRows($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @return void
     */
    public function testValidateDefaultScopeNotValidAttributesResetSku(): void
    {
        $this->validator->expects($this->once())->method('isAttributeValid')->willReturn(false);
        $messages = ['validator message'];
        $this->validator->expects($this->once())->method('getMessages')->willReturn($messages);

        $result = $this->importProduct->isAttributeValid('code', ['attribute params'], ['row data'], 1);
        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testValidateRowSetAttributeSetCodeIntoRowData(): void
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            Product::COL_SKU => $sku,
            Product::COL_ATTR_SET => 'col_attr_set_val'
        ];
        $expectedAttrSetCode = 'new_attr_set_code';
        $newSku = [
            'attr_set_code' => $expectedAttrSetCode,
            'type_id' => 'new_type_id_val'
        ];
        $expectedRowData = [
            Product::COL_SKU => $sku,
            Product::COL_ATTR_SET => $newSku['attr_set_code']
        ];
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val'
            ]
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(['getOptionEntity']);

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);
        $this->skuProcessor->expects($this->any())->method('getNewSku')->willReturn($newSku);
        $this->setPropertyValue($importProduct, 'skuProcessor', $this->skuProcessor);
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $productType = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productType->expects($this->once())->method('isRowValid')->with($expectedRowData);
        $this->setPropertyValue(
            $importProduct,
            '_productTypeModels',
            [
                $newSku['type_id'] => $productType
            ]
        );

        //suppress option validation
        $this->_rewriteGetOptionEntityInImportProduct($importProduct);
        //suppress validator
        $this->_setValidatorMockInImportProduct($importProduct);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @return void
     */
    public function testValidateValidateOptionEntity(): void
    {
        $sku = 'sku';
        $rowNum = 0;
        $rowData = [
            Product::COL_SKU => $sku,
            Product::COL_ATTR_SET => 'col_attr_set_val'
        ];
        $oldSku = [
            $sku => [
                'type_id' => 'type_id_val'
            ]
        ];
        $importProduct = $this->createModelMockWithErrorAggregator(
            ['addRowError', 'getOptionEntity'],
            ['isRowInvalid' => true]
        );

        $this->setPropertyValue($importProduct, '_oldSku', $oldSku);

        //suppress validator
        $this->_setValidatorMockInImportProduct($importProduct);

        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->once())->method('validateRow')->with($rowData, $rowNum);
        $importProduct->expects($this->once())->method('getOptionEntity')->willReturn($option);
        $this->setPrivatePropertyValue($importProduct, 'skuStorage', $this->skuStorageMock);

        $importProduct->validateRow($rowData, $rowNum);
    }

    /**
     * @return void
     * @dataProvider getImagesFromRowDataProvider
     */
    public function testGetImagesFromRow($rowData, $expectedResult): void
    {
        $this->assertEquals(
            $this->importProduct->getImagesFromRow($rowData),
            $expectedResult
        );
    }

    /**
     * @return void
     */
    public function testParseAttributesWithoutWrappedValuesWillReturnsLowercasedAttributeCodes(): void
    {
        $entityTypeModel = $this->createPartialMock(
            Configurable::class,
            ['retrieveAttributeFromCache']
        );
        $entityTypeModel->expects($this->exactly(2))->method('retrieveAttributeFromCache')->willReturn([
            'type' => 'multiselect'
        ]);
        $importProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['retrieveProductTypeByName'])
            ->getMock();
        $importProduct->expects($this->exactly(2))->method('retrieveProductTypeByName')->willReturn($entityTypeModel);

        $attributesData = 'PARAM1=value1,param2=value2|value3';
        $preparedAttributes = $this->invokeMethod(
            $importProduct,
            'parseAttributesWithoutWrappedValues',
            [$attributesData, 'configurable']
        );

        $this->assertArrayHasKey('param1', $preparedAttributes);
        $this->assertEquals('value1', $preparedAttributes['param1']);

        $this->assertArrayHasKey('param2', $preparedAttributes);
        $this->assertEquals('value2', $preparedAttributes['param2'][0]);
        $this->assertEquals('value3', $preparedAttributes['param2'][1]);

        $this->assertArrayNotHasKey('PARAM1', $preparedAttributes);
    }

    /**
     * @return void
     */
    public function testParseAttributesWithWrappedValuesWillReturnsLowercasedAttributeCodes(): void
    {
        $attribute1 = $this->getMockBuilder(AbstractAttribute::class)->disableOriginalConstructor()
            ->onlyMethods(['getFrontendInput'])
            ->getMockForAbstractClass();

        $attribute1->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('text');

        $attribute2 = $this->getMockBuilder(AbstractAttribute::class)->disableOriginalConstructor()
            ->onlyMethods(['getFrontendInput'])
            ->getMockForAbstractClass();

        $attribute2->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('multiselect');

        $attributeCache = [
            'param1' => $attribute1,
            'param2' => $attribute2
        ];

        $this->setPropertyValue($this->importProduct, '_attributeCache', $attributeCache);

        $attributesData = 'PARAM1="value1",PARAM2="value2"';
        $attributes = $this->invokeMethod(
            $this->importProduct,
            'parseAttributesWithWrappedValues',
            [$attributesData]
        );

        $this->assertArrayHasKey('param1', $attributes);
        $this->assertEquals('value1', $attributes['param1']);

        $this->assertArrayHasKey('param2', $attributes);
        $this->assertEquals('"value2"', $attributes['param2']);

        $this->assertArrayNotHasKey('PARAM1', $attributes);
        $this->assertArrayNotHasKey('PARAM2', $attributes);
    }

    /**
     * @param bool $isRead
     * @param bool $isWrite
     * @param string $message
     *
     * @return void
     * @dataProvider fillUploaderObjectDataProvider
     */
    public function testFillUploaderObject($isRead, $isWrite, $message): void
    {
        $dir = $this->createMock(WriteInterface::class);
        $dir->method('getAbsolutePath')
            ->willReturn('pub/media');
        $this->_filesystem->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($dir);

        $fileUploaderMock = $this
            ->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploaderMock
            ->method('setTmpDir')
            ->with('pub/media/import')
            ->willReturn($isRead);

        $fileUploaderMock
            ->method('setDestDir')
            ->with('pub/media/catalog/product')
            ->willReturn($isWrite);

        $this->_mediaDirectory
            ->method('getDriver')
            ->willReturn($this->driverFile);

        $this->_mediaDirectory
            ->method('getRelativePath')
            ->willReturnMap(
                [
                    ['import', 'import'],
                    ['catalog/product', 'catalog/product'],
                    ['pub/media', 'pub/media']
                ]
            );

        $this->_mediaDirectory
            ->method('create')
            ->with('pub/media/catalog/product');

        $this->_uploaderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($fileUploaderMock);

        try {
            $this->importProduct->getUploader();
            $this->assertNotNull($this->getPropertyValue($this->importProduct, '_fileUploader'));
        } catch (LocalizedException $e) {
            $this->assertNull($this->getPropertyValue($this->importProduct, '_fileUploader'));
            $this->assertEquals($message, $e->getMessage());
        }
    }

    /**
     * Test that errors occurred during importing images are logged.
     *
     * @param string $fileName
     * @param bool $throwException
     *
     * @return void
     * @dataProvider uploadMediaFilesDataProvider
     */
    public function testUploadMediaFiles(string $fileName, bool $throwException): void
    {
        $exception = new \Exception();
        $expectedFileName = $fileName;
        if ($throwException) {
            $expectedFileName = '';
            $this->_logger->expects($this->once())->method('critical')->with($exception);
        }
        $fileUploaderMock = $this
            ->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploaderMock
            ->expects($this->once())
            ->method('move')
            ->willReturnCallback(
                function ($name) use ($throwException, $exception) {
                    if ($throwException) {
                        throw $exception;
                    }
                    return ['file' => $name];
                }
            );
        $this->setPropertyValue(
            $this->importProduct,
            '_fileUploader',
            $fileUploaderMock
        );
        $actualFileName = $this->invokeMethod(
            $this->importProduct,
            'uploadMediaFiles',
            [$fileName]
        );
        $this->assertEquals(
            $expectedFileName,
            $actualFileName
        );
    }

    /**
     * Check that getProductCategoriesDataSave method will return array with product-category-position relations
     * where new products positioned before existing
     *
     * @param array $categoriesData
     * @param string $tableName
     * @param array $result
     * @dataProvider productCategoriesDataProvider
     */
    public function testGetProductCategoriesDataSave(array $categoriesData, string $tableName, array $result)
    {
        $this->_connection->method('fetchOne')->willReturnOnConsecutiveCalls('0', '-2');
        $this->skuProcessor->method('getNewSku')
            ->willReturnOnConsecutiveCalls(
                ['entity_id' => 2],
                ['entity_id' => 5]
            );
        $actualResult = $this->invokeMethod(
            $this->importProduct,
            'getProductCategoriesDataSave',
            [$categoriesData, $tableName]
        );
        $this->assertEquals($result, $actualResult);
    }

    /**
     * Data provider for testGetProductCategoriesDataSave.
     *
     * @return array
     */
    public static function productCategoriesDataProvider()
    {
        return [
            [
                [
                    'simple_2' => [3 => true],
                    'simple_5' => [5 => true]
                ],
                'catalog_category_product',
                [
                    [2, 5],
                    [
                        [
                            'product_id' => 2,
                            'category_id' => 3,
                            'position' => -1
                        ],
                        [
                            'product_id' => 5,
                            'category_id' => 5,
                            'position' => -3
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider for testFillUploaderObject.
     *
     * @return array
     */
    public static function fillUploaderObjectDataProvider(): array
    {
        return [
            [false, true, 'File directory \'pub/media/import\' is not readable.'],
            [true, false, 'File directory \'pub/media/catalog/product\' is not writable.'],
            [true, true, ''],
        ];
    }

    /**
     * Data provider for testUploadMediaFiles.
     *
     * @return array
     */
    public static function uploadMediaFilesDataProvider(): array
    {
        return [
            ['test1.jpg', false],
            ['test2.jpg', true]
        ];
    }

    /**
     * @return array
     */
    public static function getImagesFromRowDataProvider(): array
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
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function validateRowValidateNewProductTypeAddRowErrorCallDataProvider(): array
    {
        return [
            [
                '$colType' => null,
                '$productTypeModelsColType' => 'value',
                '$colAttrSet' => null,
                '$attrSetNameToIdColAttrSet' => null,
                '$error' => Validator::ERROR_INVALID_TYPE
            ],
            [
                '$colType' => 'value',
                '$productTypeModelsColType' => null,
                '$colAttrSet' => null,
                '$attrSetNameToIdColAttrSet' => null,
                '$error' => Validator::ERROR_INVALID_TYPE
            ],
            [
                '$colType' => 'value',
                '$productTypeModelsColType' => 'value',
                '$colAttrSet' => null,
                '$attrSetNameToIdColAttrSet' => 'value',
                '$error' => Validator::ERROR_INVALID_ATTR_SET
            ],
            [
                '$colType' => 'value',
                '$productTypeModelsColType' => 'value',
                '$colAttrSet' => 'value',
                '$attrSetNameToIdColAttrSet' => null,
                '$error' => Validator::ERROR_INVALID_ATTR_SET
            ]
        ];
    }

    /**
     * @return array
     */
    public static function validateRowCheckSpecifiedSkuDataProvider(): array
    {
        return [
            [
                '$sku' => null
            ],
            [
                '$sku' => false
            ],
            [
                '$sku' => 'sku'
            ]
        ];
    }

    /**
     * @return array
     */
    public static function validateRowDataProvider(): array
    {
        return [
            [
                '$rowScope' => Product::SCOPE_DEFAULT,
                '$oldSku' => null,
                '$expectedResult' => false
            ],
            [
                '$rowScope' => null,
                '$oldSku' => null,
                '$expectedResult' => true
            ],
            [
                '$rowScope' => null,
                '$oldSku' => true,
                '$expectedResult' => true
            ],
            [
                '$rowScope' => Product::SCOPE_DEFAULT,
                '$oldSku' => true,
                '$expectedResult' => true
            ],
            [
                '$rowScope' => Product::SCOPE_DEFAULT,
                '$oldSku' => null,
                '$expectedResult' => false,
                '$behaviour' => Import::BEHAVIOR_REPLACE
            ]
        ];
    }

    /**
     * @return array
     */
    public static function isAttributeValidAssertAttrValidDataProvider(): array
    {
        return [
            [
                '$attrParams' => [
                    'type' => 'varchar',
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        Product::DB_MAX_VARCHAR_LENGTH - 1
                    ),
                ],
            ],
            [
                '$attrParams' => [
                    'type' => 'decimal'
                ],
                '$rowData' => [
                    'code' => 10
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'select',
                    'options' => ['code' => 1]
                ],
                '$rowData' => [
                    'code' => 'code'
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'multiselect',
                    'options' => ['code' => 1]
                ],
                '$rowData' => [
                    'code' => 'code'
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'int'
                ],
                '$rowData' => [
                    'code' => 1000
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'datetime'
                ],
                '$rowData' => [
                    'code' => "5 September 2015"
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'text'
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        Product::DB_MAX_TEXT_LENGTH - 1
                    )
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function isAttributeValidAssertAttrInvalidDataProvider(): array
    {
        return [
            [
                '$attrParams' => [
                    'type' => 'varchar'
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        Product::DB_MAX_VARCHAR_LENGTH + 1
                    )
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'decimal'
                ],
                '$rowData' => [
                    'code' => 'incorrect'
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'select',
                    'not options' => null
                ],
                '$rowData' => [
                    'code' => 'code'
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'multiselect',
                    'not options' => null
                ],
                '$rowData' => [
                    'code' => 'code'
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'int'
                ],
                '$rowData' => [
                    'code' => 'not int'
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'datetime'
                ],
                '$rowData' => [
                    'code' => "incorrect datetime"
                ]
            ],
            [
                '$attrParams' => [
                    'type' => 'text'
                ],
                '$rowData' => [
                    'code' => str_repeat(
                        'a',
                        Product::DB_MAX_TEXT_LENGTH + 1
                    )
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getRowScopeDataProvider(): array
    {
        $colSku = Product::COL_SKU;
        $colStore = Product::COL_STORE;

        return [
            [
                '$rowData' => [
                    $colSku => null,
                    $colStore => 'store'
                ],
                '$expectedResult' => Product::SCOPE_STORE
            ],
            [
                '$rowData' => [
                    $colSku => 'sku',
                    $colStore => null
                ],
                '$expectedResult' => Product::SCOPE_DEFAULT
            ],
            [
                '$rowData' => [
                    $colSku => 'sku',
                    $colStore => 'store'
                ],
                '$expectedResult' => Product::SCOPE_STORE
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
     * @param $property
     * @param $value
     */
    private function setPrivatePropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        while (strpos($reflection->getName(), 'Mock') !== false) {
            $reflection = $reflection->getParentClass();
        }
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
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
     * @param Product  Param should go with rewritten getOptionEntity method.
     *
     * @return Option|MockObject
     */
    private function _suppressValidateRowOptionValidatorInvalidRows($importProduct): MockObject
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
     * @param Product
     *
     * @return Validator|MockObject
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
     * @param Product  Param should go with rewritten getOptionEntity method.
     *
     * @return Option|MockObject
     */
    private function _rewriteGetOptionEntityInImportProduct($importProduct): MockObject
    {
        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importProduct->expects($this->once())->method('getOptionEntity')->willReturn($option);

        return $importProduct;
    }

    /**
     * @param array $methods
     * @param array $errorAggregatorMethods
     *
     * @return MockObject
     */
    protected function createModelMockWithErrorAggregator(
        array $methods = [],
        array $errorAggregatorMethods = []
    ): MockObject {
        $methods[] = 'getErrorAggregator';
        $importProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
        $errorMethods = array_keys($errorAggregatorMethods);
        $errorAggregator = $this->getErrorAggregatorObject($errorMethods);
        foreach ($errorAggregatorMethods as $method => $result) {
            $errorAggregator->method($method)->willReturn($result);
        }
        $importProduct->method('getErrorAggregator')->willReturn($errorAggregator);

        return $importProduct;
    }

    /**
     * @dataProvider valuesDataProvider
     */
    public function testParseMultiselectValues($value, $fieldSeparator, $valueSeparator)
    {
        $this->importProduct->setParameters(
            [
                Import::FIELD_FIELD_SEPARATOR => $fieldSeparator,
                Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => $valueSeparator
            ]
        );
        $this->assertEquals(explode($valueSeparator, $value), $this->importProduct->parseMultiselectValues($value));
    }

    /**
     * @return array
     */
    public static function valuesDataProvider(): array
    {
        return [
            'pipeWithCustomFieldSeparator' => [
                'value' => 'L|C|D|T|H',
                'fieldSeparator' => ';',
                'valueSeparator' => '|'
            ],
            'commaWithCustomFieldSeparator' => [
                'value' => 'L,C,D,T,H',
                'fieldSeparator' => ';',
                'valueSeparator' => ','
            ],
            'pipeWithDefaultFieldSeparator' => [
                'value' => 'L|C|D|T|H',
                'fieldSeparator' => ',',
                'valueSeparator' => '|'
            ],
            'commaWithDefaultFieldSeparator' => [
                'value' => 'L,C,D,T,H',
                'fieldSeparator' => ',',
                'valueSeparator' => ','
            ],
            'anonymousValueSeparatorWithDefaultFieldSeparator' => [
                'value' => 'L+C+D+T+H',
                'fieldSeparator' => ',',
                'valueSeparator' => '+'
            ],
            'anonymousValueSeparatorWithDefaultFieldSeparatorAndSingleValue' => [
                'value' => 'L',
                'fieldSeparator' => ',',
                'valueSeparator' => '*'
            ]
        ];
    }
}
