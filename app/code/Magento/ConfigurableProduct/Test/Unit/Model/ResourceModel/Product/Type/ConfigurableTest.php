<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeInterface;
use Magento\Catalog\Model\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relation;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolver;

    /**
     * @var StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockRegistryMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractAttribute;

    /**
     * @var ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scope;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    protected function setUp()
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['select', 'fetchAll'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->select = $this->getMockBuilder(Select::class)
            ->setMethods(['from', 'joinInner', 'joinLeft', 'where',])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->select);
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->relation = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Relation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->metadataMock);
        $this->stockRegistryMock = $this->getMockBuilder(StockRegistryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractAttribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getBackendTable', 'getAttributeId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->product = $this->getMockBuilder(Product::class)
            ->setMethods(['__sleep', '__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configurable = $this->objectManagerHelper->getObject(
            Configurable::class,
            [
                'resource' => $this->resource,
                'catalogProductRelation' => $this->relation,
                'scopeResolver' => $this->scopeResolver,
                'stockRegistry' => $this->stockRegistryMock
            ]
        );
        $reflection = new \ReflectionClass(Configurable::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->configurable, $this->metadataPoolMock);
    }

    public function testSaveProducts()
    {
        $this->metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('link');
        $this->product->expects($this->once())
            ->method('getData')
            ->with('link')
            ->willReturn(3);

        $this->connectionMock->method('select')->willReturn($this->select);
        $this->select->method('from')->willReturnSelf();
        $this->select->method('where')->willReturnSelf();

        $statement  = $this->getMockBuilder(\Zend_Db_Statement::class)->disableOriginalConstructor()->getMock();
        $this->select->method('query')->willReturn($statement);
        $statement->method('fetchAll')->willReturn([1]);

        $this->configurable->saveProducts($this->product, [1, 2, 3]);
    }

    public function testGetInStockAttributeOptions()
    {
        $options = [
            [
                'sku' => 'Configurable1-Black',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '13',
                'option_title' => 'Black'
            ],
            [
                'sku' => 'Configurable1-White',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '14',
                'option_title' => 'White'
            ],
            [
                'sku' => 'Configurable1-Red',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '15',
                'option_title' => 'Red'
            ]
        ];
        $expectedOptions = [
            [
                'sku' => 'Configurable1-White',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '14',
                'option_title' => 'White'
            ],
            [
                'sku' => 'Configurable1-Red',
                'product_id' => 4,
                'attribute_code' => 'color',
                'value_index' => '15',
                'option_title' => 'Red'
            ]
        ];
        $this->scopeResolver->expects($this->any())->method('getScope')->willReturn($this->scope);
        $this->scope->expects($this->any())->method('getId')->willReturn(123);

        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('joinInner')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();

        $this->abstractAttribute->expects($this->any())
            ->method('getBackendTable')
            ->willReturn('getBackendTable value');
        $this->abstractAttribute->expects($this->any())
            ->method('getAttributeId')
            ->willReturn('getAttributeId value');

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($options);

        $this->stockRegistryMock->expects($this->any())
            ->method('getProductStockStatusBySku')
            ->willReturnMap(
                [
                    ['Configurable1-Black', null, 0],
                    ['Configurable1-White', null, 1],
                    ['Configurable1-Red', null, 1]
                ]
            );

        $this->assertEquals(
            $expectedOptions,
            $this->configurable->getInStockAttributeOptions($this->abstractAttribute, 1)
        );
    }

    public function testGetConfigurableOptions()
    {
        $this->scopeResolver->expects($this->any())->method('getScope')->willReturn($this->scope);
        $this->scope->expects($this->any())->method('getId')->willReturn(123);

        $this->metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('link');
        $this->product->expects($this->once())
            ->method('getData')
            ->with('link')
            ->willReturn('getId value');

        $this->abstractAttribute->expects($this->any())
            ->method('getBackendTable')
            ->willReturn('getBackendTable value');
        $this->abstractAttribute->expects($this->any())
            ->method('getAttributeId')
            ->willReturn('getAttributeId value');
        $attributes = [
            $this->abstractAttribute,
        ];

        $this->select->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $this->select->expects($this->exactly(5))
            ->method('joinInner')
            ->willReturnSelf();
        $this->select->expects($this->exactly(2))
            ->method('joinLeft')
            ->willReturnSelf();
        $this->select->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->select);
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn('fetchAll value');

        $expectedAttributesOptionsData = [
            'getAttributeId value' => 'fetchAll value',
        ];
        $actualAttributesOptionsData = $this->configurable->getConfigurableOptions($this->product, $attributes);
        $this->assertEquals($expectedAttributesOptionsData, $actualAttributesOptionsData);
    }
}
