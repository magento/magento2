<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeOptionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeOptionProvider
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataMock;

    /**
     * @var ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolver;

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
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeResource;

    /**
     * @var StockStatusRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusRepository;

    /**
     * @var StockStatusCriteriaInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusCriteriaFactory;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var StockStatusCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusCriteriaInterface;

    /**
     * @var StockStatusCollectionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusCollection;

    protected function setUp()
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['select', 'fetchAll'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->select = $this->getMockBuilder(Select::class)
            ->setMethods(['from', 'joinInner', 'joinLeft', 'where'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

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
        $this->attributeResource = $this->getMockBuilder(Attribute::class)
            ->setMethods(['getTable', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeResource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->stockStatusRepository = $this->getMockBuilder(StockStatusRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatusCriteriaFactory = $this->getMockBuilder(StockStatusCriteriaInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatusCriteriaInterface = $this->getMockBuilder(StockStatusCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockStatusCollection = $this->getMockBuilder(StockStatusCollectionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            AttributeOptionProvider::class,
            [
                'attributeResource' => $this->attributeResource,
                'stockStatusRepository' => $this->stockStatusRepository,
                'stockStatusCriteriaFactory' => $this->stockStatusCriteriaFactory,
                'scopeResolver' => $this->scopeResolver,
                'metadataPool' => $this->metadataPool
            ]
        );
        $reflection = new \ReflectionClass(AttributeOptionProvider::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $this->metadataPoolMock);
    }

    /**
     * @param array $options
     * @dataProvider testOptionsDataProvider
     */
    public function testGetInStockAttributeOptions(array $options)
    {
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
        $status1 = $this->getMockBuilder(StockStatusInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $status2 = $this->getMockBuilder(StockStatusInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $statuses = [$status1, $status2];

        $this->scopeResolver->expects($this->any())->method('getScope')->willReturn($this->scope);
        $this->scope->expects($this->any())->method('getId')->willReturn(123);

        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('joinInner')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();

        $this->stockStatusCriteriaFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->stockStatusCriteriaInterface);
        $this->stockStatusCriteriaInterface->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();
        $this->stockStatusRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->stockStatusCollection);
        $this->stockStatusCollection->expects($this->any())
            ->method('getItems')
            ->willReturn($statuses);
        $status1->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn('Configurable1-White');
        $status2->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn('Configurable1-Red');

        $this->abstractAttribute->expects($this->any())
            ->method('getBackendTable')
            ->willReturn('getBackendTable value');
        $this->abstractAttribute->expects($this->any())
            ->method('getAttributeId')
            ->willReturn('getAttributeId value');

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($options);

        $this->assertEquals(
            $expectedOptions,
            $this->model->getInStockAttributeOptions($this->abstractAttribute, 1)
        );
    }

    /**
     * @param array $options
     * @dataProvider testOptionsDataProvider
     */
    public function testGetAttributeOptions(array $options)
    {
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

        $this->assertEquals(
            $options,
            $this->model->getAttributeOptions($this->abstractAttribute, 1)
        );
    }

    /**
     * @return array
     */
    public function testOptionsDataProvider()
    {
        return [
            [
                [
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
                ]
            ]
        ];
    }
}
