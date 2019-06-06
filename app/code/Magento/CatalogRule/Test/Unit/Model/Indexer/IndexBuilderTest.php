<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\IndexBuilder\ProductLoader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class IndexBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Zend_Db_Statement_Interface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $db;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $website;

    /**
     * @var \Magento\Rule\Model\Condition\Combine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $combine;

    /**
     * @var \Magento\CatalogRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rules;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backend;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $reindexRuleProductPrice;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $reindexRuleGroupWebsite;

    /**
     * @var ProductLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLoader;

    /**
     * Set up test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->resource = $this->createPartialMock(
            \Magento\Framework\App\ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->ruleCollectionFactory = $this->createPartialMock(
            \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory::class,
            ['create']
        );
        $this->backend = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class);
        $this->select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->metadataPool = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $metadata = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);
        $this->metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);
        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->db = $this->createMock(\Zend_Db_Statement_Interface::class);
        $this->website = $this->createMock(\Magento\Store\Model\Website::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->combine = $this->createMock(\Magento\Rule\Model\Condition\Combine::class);
        $this->rules = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $this->priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->dateFormat = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $this->dateTime = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $this->eavConfig = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getAttribute']);
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productFactory = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->connection->expects($this->any())->method('select')->will($this->returnValue($this->select));
        $this->connection->expects($this->any())->method('query')->will($this->returnValue($this->db));
        $this->select->expects($this->any())->method('distinct')->will($this->returnSelf());
        $this->select->expects($this->any())->method('where')->will($this->returnSelf());
        $this->select->expects($this->any())->method('from')->will($this->returnSelf());
        $this->select->expects($this->any())->method('order')->will($this->returnSelf());
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($this->connection));
        $this->resource->expects($this->any())->method('getTableName')->will($this->returnArgument(0));
        $this->storeManager->expects($this->any())->method('getWebsites')->will($this->returnValue([$this->website]));
        $this->storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($this->website));
        $this->rules->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->rules->expects($this->any())->method('getWebsiteIds')->will($this->returnValue([1]));
        $this->rules->expects($this->any())->method('getCustomerGroupIds')->will($this->returnValue([1]));

        $ruleCollection = $this->createMock(\Magento\CatalogRule\Model\ResourceModel\Rule\Collection::class);
        $this->ruleCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $ruleIterator = new \ArrayIterator([$this->rules]);
        $ruleCollection->method('getIterator')
            ->willReturn($ruleIterator);

        $this->product->expects($this->any())->method('load')->will($this->returnSelf());
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->product->expects($this->any())->method('getWebsiteIds')->will($this->returnValue([1]));

        $this->rules->expects($this->any())->method('validate')->with($this->product)->willReturn(true);
        $this->attribute->expects($this->any())->method('getBackend')->will($this->returnValue($this->backend));
        $this->productFactory->expects($this->any())->method('create')->will($this->returnValue($this->product));
        $this->productLoader = $this->getMockBuilder(ProductLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexBuilder = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Model\Indexer\IndexBuilder::class,
            [
                'ruleCollectionFactory' => $this->ruleCollectionFactory,
                'priceCurrency' => $this->priceCurrency,
                'resource' => $this->resource,
                'storeManager' => $this->storeManager,
                'logger' => $this->logger,
                'eavConfig' => $this->eavConfig,
                'dateFormat' => $this->dateFormat,
                'dateTime' => $this->dateTime,
                'productFactory' => $this->productFactory,
                'productLoader' => $this->productLoader,
            ]
        );

        $this->reindexRuleProductPrice = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice::class
        );
        $this->reindexRuleGroupWebsite = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite::class
        );
        $this->setProperties(
            $this->indexBuilder,
            [
                'metadataPool' => $this->metadataPool,
                'reindexRuleProductPrice' => $this->reindexRuleProductPrice,
                'reindexRuleGroupWebsite' => $this->reindexRuleGroupWebsite,
            ]
        );
    }

    /**
     * Test UpdateCatalogRuleGroupWebsiteData
     *
     * @covers \Magento\CatalogRule\Model\Indexer\IndexBuilder::updateCatalogRuleGroupWebsiteData
     * @return void
     */
    public function testUpdateCatalogRuleGroupWebsiteData()
    {
        $priceAttrMock = $this->createPartialMock(\Magento\Catalog\Model\Entity\Attribute::class, ['getBackend']);
        $backendModelMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice::class,
            ['getResource']
        );
        $resourceMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice::class,
            ['getMainTable']
        );
        $resourceMock->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue('catalog_product_entity_tear_price'));
        $backendModelMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));
        $priceAttrMock->expects($this->any())
            ->method('getBackend')
            ->will($this->returnValue($backendModelMock));

        $iterator = [$this->product];
        $this->productLoader->expects($this->once())
            ->method('getProducts')
            ->willReturn($iterator);

        $this->reindexRuleProductPrice->expects($this->once())->method('execute')->willReturn(true);
        $this->reindexRuleGroupWebsite->expects($this->once())->method('execute')->willReturn(true);

        $this->indexBuilder->reindexByIds([1]);
    }

    /**
     * @param $object
     * @param array $properties
     */
    private function setProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }
}
