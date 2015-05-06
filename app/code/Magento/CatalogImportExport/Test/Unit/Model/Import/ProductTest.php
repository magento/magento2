<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime;
use Zend\Server\Reflection\ReflectionClass;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    const MEDIA_DIRECTORY = 'media/import';

    const ENTITY_TYPE_ID = 1;

    const ENTITY_TYPE_CODE = 'catalog_product';

    const ENTITY_ID = 13;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_connection;

    /** @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonHelper;

    /** @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_importExportData;

    /** @var \Magento\ImportExport\Model\Resource\Import\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dataSourceModel;

    /** @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\ImportExport\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceHelper;

    /** @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject */
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

    /** @var \Magento\ImportExport\Model\Resource\Import\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $importData;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\ImportExport\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceHelper;

    /** @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_catalogData;

    /** @var \Magento\ImportExport\Model\Import\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_importConfig;

    /** @var  \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceFactory;

    /** @var  \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_setColFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\Type\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_productTypeFactory;

    /** @var  \Magento\Catalog\Model\Resource\Product\LinkFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_linkFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_proxyProdFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_uploaderFactory;

    /** @var  \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $_filesystem;

    /** @var  \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_mediaDirectory;

    /** @var  \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_stockResItemFac;

    /** @var  \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_localeDate;

    /** @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject  */
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

    /** @var  \Magento\Framework\Model\Resource\Db\ObjectRelationProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectRelationProcessor;

    /** @var  \Magento\Framework\Model\Resource\Db\TransactionManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionManager;

    /** @var  \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogProductFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $taxClassProcessor;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product */
    protected $importProduct;

    protected function setUp()
    {
        /* For parent object construct */
        $this->jsonHelper = $this->getMockBuilder('\Magento\Framework\Json\Helper\Data')->disableOriginalConstructor()->getMock();
        $this->importExportData = $this->getMockBuilder('\Magento\ImportExport\Helper\Data')->disableOriginalConstructor()->getMock();
        $this->_dataSourceModel = $this->getMockBuilder('\Magento\ImportExport\Model\Resource\Import\Data')->disableOriginalConstructor()->getMock();
        $this->config = $this->getMockBuilder('\Magento\Eav\Model\Config')->disableOriginalConstructor()->getMock();
        $this->resource = $this->getMockBuilder('\Magento\Framework\App\Resource')->disableOriginalConstructor()->getMock();
        $this->resourceHelper = $this->getMockBuilder('\Magento\ImportExport\Model\Resource\Helper')->disableOriginalConstructor()->getMock();
        $this->string = $this->getMockBuilder('\Magento\Framework\Stdlib\String')->disableOriginalConstructor()->getMock();

        /* For object construct */
        $this->_eventManager = $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')->getMock();
        $this->stockRegistry = $this->getMockBuilder('\Magento\CatalogInventory\Api\StockRegistryInterface')->getMock();
        $this->stockConfiguration = $this->getMockBuilder('\Magento\CatalogInventory\Api\StockConfigurationInterface')->getMock();
        $this->stockStateProvider = $this->getMockBuilder('\Magento\CatalogInventory\Model\Spi\StockStateProviderInterface')->getMock();
        $this->_catalogData = $this->getMockBuilder('\Magento\Catalog\Helper\Data')->disableOriginalConstructor()->getMock();
        $this->_importConfig = $this->getMockBuilder('\Magento\ImportExport\Model\Import\Config')->disableOriginalConstructor()->getMock();
        $this->_resourceFactory = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceFactory')->disableOriginalConstructor()->getMock();
        $this->_setColFactory = $this->getMockBuilder('\Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory')->disableOriginalConstructor()->getMock();
        $this->_productTypeFactory = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\Type\Factory')->disableOriginalConstructor()->getMock();
        $this->_linkFactory = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\LinkFactory')->disableOriginalConstructor()->getMock();
        $this->_proxyProdFactory = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory')->disableOriginalConstructor()->getMock();
        $this->_uploaderFactory = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\UploaderFactory')->disableOriginalConstructor()->getMock();
        $this->_filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem')->disableOriginalConstructor()->getMock();
        $this->_mediaDirectory = $this->getMockBuilder('\Magento\Framework\Filesystem\Directory\WriteInterface')->getMock();
        $this->_stockResItemFac = $this->getMockBuilder('\Magento\CatalogInventory\Model\Resource\Stock\ItemFactory')->disableOriginalConstructor()->getMock();
        $this->_localeDate = $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime\TimezoneInterface')->getMock();
        $this->dateTime = $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime')->disableOriginalConstructor()->getMock();
        $this->indexerRegistry = $this->getMockBuilder('\Magento\Indexer\Model\IndexerRegistry')->disableOriginalConstructor()->getMock();
        $this->_logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')->getMock();
        $this->storeResolver = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\StoreResolver')->disableOriginalConstructor()->getMock();
        $this->skuProcessor = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\SkuProcessor')->disableOriginalConstructor()->getMock();
        $this->categoryProcessor = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor')->disableOriginalConstructor()->getMock();
        $this->validator = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\Validator')->disableOriginalConstructor()->getMock();
        $this->objectRelationProcessor = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\ObjectRelationProcessor')->disableOriginalConstructor()->getMock();
        $this->transactionManager = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\TransactionManagerInterface')->getMock();
        $this->catalogProductFactory = $this->getMockBuilder('\Magento\Catalog\Model\ProductFactory')->disableOriginalConstructor()->getMock();
        $this->taxClassProcessor = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor')->disableOriginalConstructor()->getMock();

        $this->data = [];

        $this->_objectConstructor()
            ->_parentObjectConstructor()
            ->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus();

        $this->importProduct = new Product(
            $this->jsonHelper,
            $this->importExportData,
            $this->_dataSourceModel,
            $this->config,
            $this->resource,
            $this->resourceHelper,
            $this->string,
            $this->_eventManager,
            $this->stockRegistry,
            $this->stockConfiguration,
            $this->stockStateProvider,
            $this->_catalogData,
            $this->_importConfig,
            $this->_resourceFactory,
            $this->optionFactory,
            $this->_setColFactory,
            $this->_productTypeFactory,
            $this->_linkFactory,
            $this->_proxyProdFactory,
            $this->_uploaderFactory,
            $this->_filesystem,
            $this->_stockResItemFac,
            $this->_localeDate,
            $this->dateTime,
            $this->_logger,
            $this->indexerRegistry,
            $this->storeResolver,
            $this->skuProcessor,
            $this->categoryProcessor,
            $this->validator,
            $this->catalogProductFactory,
            $this->objectRelationProcessor,
            $this->transactionManager,
            $this->taxClassProcessor,
            $this->data
        );
    }

    /**
     * @return $this
     */
    protected function _objectConstructor()
    {
        $this->optionFactory = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\OptionFactory')->disableOriginalConstructor()->getMock();
        $this->optionEntity = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\Option')->disableOriginalConstructor()->getMock();
        $this->optionFactory->expects($this->once())->method('create')->willReturn($this->optionEntity);

        $this->_filesystem->expects($this->once())->method('getDirectoryWrite')->with(DirectoryList::ROOT)->will($this->returnValue(self::MEDIA_DIRECTORY));

        $this->validator->expects($this->once())->method('init');
        return $this;
    }

    /**
     * @return $this
     */
    protected function _parentObjectConstructor()
    {
        $type = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')->disableOriginalConstructor()->getMock();
        $type->expects($this->any())->method('getEntityTypeId')->will($this->returnValue(self::ENTITY_TYPE_ID));
        $this->config->expects($this->any())->method('getEntityType')->with(self::ENTITY_TYPE_CODE)->willReturn($type);

        $this->_connection = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->_connection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initAttributeSets()
    {
        $attributeSet1 = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\Set')->disableOriginalConstructor()->getMock();
        $attributeSet1->expects($this->any())->method('getAttributeSetName')->willReturn('attributeSet1');
        $attributeSet1->expects($this->any())->method('getId')->willReturn('1');
        $attributeSet2 = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\Set')->disableOriginalConstructor()->getMock();
        $attributeSet2->expects($this->any())->method('getAttributeSetName')->willReturn('attributeSet2');
        $attributeSet2->expects($this->any())->method('getId')->willReturn('2');
        $attributeSetCol = [$attributeSet1, $attributeSet2];
        $collection = $this->getMockBuilder('\Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection')->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('setEntityTypeFilter')->with(self::ENTITY_TYPE_ID)->willReturn($attributeSetCol);
        $this->_setColFactory->expects($this->once())->method('create')->willReturn($collection);
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
        $productTypeInstance = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType')->disableOriginalConstructor()->getMock();
        $productTypeInstance->expects($this->once())->method('isSuitable')->willReturn(true);
        $productTypeInstance->expects($this->once())->method('getParticularAttributes')->willReturn([]);
        $this->_importConfig->expects($this->once())->method('getEntityTypes')->with(self::ENTITY_TYPE_CODE)->willReturn($entityTypes);
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

    public function testGetAffectedProducts()
    {
        $testProduct = 'test_product';
        $rowData = ['data'];
        $rowNum = 666;
        $importProduct = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product')
            ->disableOriginalConstructor()
            ->setMethods(array('isRowAllowedToImport', '_populateToUrlGeneration'))
            ->getMock();

        $this->_dataSourceModel->expects($this->exactly(2))->method('getNextBunch')->willReturnOnConsecutiveCalls(
             [
                 $rowNum => $rowData
             ],
             null
        );
        $this->setPropertyValue($importProduct, '_dataSourceModel', $this->_dataSourceModel);
        $importProduct->expects($this->once())->method('isRowAllowedToImport')->with($rowData, $rowNum)->willReturn(true);
        $importProduct->expects($this->once())->method('_populateToUrlGeneration')->with($rowData)->willReturn($testProduct);

        $this->assertEquals([$testProduct], $importProduct->getAffectedProducts());
    }

    public function testSaveProductAttributes()
    {
        $test_table = 'test_table';
        $attribute_id = 'test_attribute_id';
        $store_id = 'test_store_id';
        $test_sku = 'test_sku';
        $attributesData = [
            $test_table => [
                $test_sku => [
                    $attribute_id => [
                        $store_id => [
                            'foo' => 'bar'
                        ]
                    ]
                ]
            ]
        ];
        $this->skuProcessor->expects($this->once())->method('getNewSku')->with($test_sku)->willReturn(['entity_id' => self::ENTITY_ID]);
        $this->_connection->expects($this->any())->method('quoteInto')->willReturnCallback([$this, 'returnQuoteCallback']);
        $this->_connection->expects($this->once())->method('delete')
            ->with($this->equalTo($test_table), $this->equalTo('(store_id NOT IN (' . $store_id . ') AND attribute_id = ' . $attribute_id . ' AND entity_id = ' . self::ENTITY_ID . ')'));

        $tableData[] = [
            'entity_id' => self::ENTITY_ID,
            'attribute_id' => $attribute_id,
            'store_id' => $store_id,
            'value' => $attributesData[$test_table][$test_sku][$attribute_id][$store_id],
        ];
        $this->_connection->expects($this->once())->method('insertOnDuplicate')->with($test_table, $tableData,['value']);
        $object = $this->invokeMethod($this->importProduct, '_saveProductAttributes', [$attributesData]);
        $this->assertEquals($this->importProduct, $object);
    }

    /**
     * @return mixed
     */
    public function returnQuoteCallback() {
        $args = func_get_args();
        return str_replace('?', (is_array($args[1]) ? implode(',', $args[1]) : $args[1]), $args[0]);
    }

    /**
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
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
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function overrideMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));

        $method = $reflection->getMethod($methodName);
        //$method->

        return $method->invokeArgs($object, $parameters);
    }
}