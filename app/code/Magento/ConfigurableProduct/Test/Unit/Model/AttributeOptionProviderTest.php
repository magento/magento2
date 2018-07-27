<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;

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
            ->setMethods(['from', 'joinInner', 'joinLeft', 'where', 'columns', 'order'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

        $this->metadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractAttribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getBackendTable', 'getAttributeId', 'getSourceModel', 'getSource'])
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
            ]
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

        $this->select->expects($this->exactly(1))->method('from')->willReturnSelf();
        $this->select->expects($this->exactly(1))->method('columns')->willReturnSelf();
        $this->select->expects($this->exactly(6))->method('joinInner')->willReturnSelf();
        $this->select->expects($this->exactly(3))->method('joinLeft')->willReturnSelf();
        $this->select->expects($this->exactly(1))->method('order')->willReturnSelf();
        $this->select->expects($this->exactly(2))->method('where')->willReturnSelf();

        $this->attributeResource->expects($this->exactly(9))
            ->method('getTable')
            ->will(
                $this->returnValueMap(
                    [
                        ['catalog_product_super_attribute', 'catalog_product_super_attribute value'],
                        ['catalog_product_entity', 'catalog_product_entity value'],
                        ['catalog_product_super_link', 'catalog_product_super_link value'],
                        ['eav_attribute', 'eav_attribute value'],
                        ['catalog_product_entity', 'catalog_product_entity value'],
                        ['catalog_product_super_attribute_label', 'catalog_product_super_attribute_label value'],
                        ['eav_attribute_option', 'eav_attribute_option value'],
                        ['eav_attribute_option_value', 'eav_attribute_option_value value']
                    ]
                )
            );

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
     * @param array $options
     * @dataProvider testOptionsWithBackendModelDataProvider
     */
    public function testGetAttributeOptionsWithBackendModel(array $options)
    {
        $this->scopeResolver->expects($this->any())->method('getScope')->willReturn($this->scope);
        $this->scope->expects($this->any())->method('getId')->willReturn(123);

        $this->select->expects($this->exactly(1))->method('from')->willReturnSelf();
        $this->select->expects($this->exactly(0))->method('columns')->willReturnSelf();
        $this->select->expects($this->exactly(6))->method('joinInner')->willReturnSelf();
        $this->select->expects($this->exactly(1))->method('joinLeft')->willReturnSelf();
        $this->select->expects($this->exactly(1))->method('order')->willReturnSelf();
        $this->select->expects($this->exactly(2))->method('where')->willReturnSelf();

        $this->attributeResource->expects($this->exactly(7))
            ->method('getTable')
            ->will(
                $this->returnValueMap(
                    [
                        ['catalog_product_super_attribute', 'catalog_product_super_attribute value'],
                        ['catalog_product_entity', 'catalog_product_entity value'],
                        ['catalog_product_super_link', 'catalog_product_super_link value'],
                        ['eav_attribute', 'eav_attribute value'],
                        ['catalog_product_entity', 'catalog_product_entity value'],
                        ['catalog_product_super_attribute_label', 'catalog_product_super_attribute_label value'],
                        ['eav_attribute_option', 'eav_attribute_option value']
                    ]
                )
            );

        $source = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionText'])
            ->getMockForAbstractClass();
        $source->expects($this->any())
            ->method('getOptionText')
            ->willReturnCallback(
                function ($optionIndex) {
                    return 'Option Value for index ' . $optionIndex;
                }
            );

        $this->abstractAttribute->expects($this->atLeastOnce())
            ->method('getSource')
            ->willReturn($source);
        $this->abstractAttribute->expects($this->any())
            ->method('getBackendTable')
            ->willReturn('getBackendTable value');
        $this->abstractAttribute->expects($this->any())
            ->method('getSourceModel')
            ->willReturn('getSourceModel value');
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
                        'option_title' => 'Black',
                    ],
                    [
                        'sku' => 'Configurable1-White',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '14',
                        'option_title' => 'White',
                    ],
                    [
                        'sku' => 'Configurable1-Red',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '15',
                        'option_title' => 'Red',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function testOptionsWithBackendModelDataProvider()
    {
        return [
            [
                [
                    [
                        'sku' => 'Configurable1-Black',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '13',
                        'option_title' => 'Option Value for index 13',
                        'default_title' => 'Option Value for index 13',
                    ],
                    [
                        'sku' => 'Configurable1-White',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '14',
                        'option_title' => 'Option Value for index 14',
                        'default_title' => 'Option Value for index 14',
                    ],
                    [
                        'sku' => 'Configurable1-Red',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '15',
                        'option_title' => 'Option Value for index 15',
                        'default_title' => 'Option Value for index 15',
                    ],
                ],
            ],
        ];
    }
}
