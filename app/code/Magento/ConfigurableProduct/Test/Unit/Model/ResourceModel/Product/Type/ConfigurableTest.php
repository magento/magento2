<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    protected $connection;
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
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

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')->getMock();

        $this->resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($this->connection));
        $this->relation = $this->getMock('Magento\Catalog\Model\ResourceModel\Product\Relation', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configurable = $this->objectManagerHelper->getObject(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable',
            [
                'resource' => $this->resource,
                'catalogProductRelation' => $this->relation
            ]
        );
    }

    public function testSaveProducts()
    {
        $mainProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getIsDuplicate', '__sleep', '__wakeup', 'getTypeInstance', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $mainProduct->expects($this->once())->method('getIsDuplicate')->will($this->returnValue(false));


        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $this->connection->method('select')->willReturn($select);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();

        $statement  = $this->getMockBuilder(\Zend_Db_Statement::class)->disableOriginalConstructor()->getMock();
        $select->method('query')->willReturn($statement);
        $statement->method('fetchAll')->willReturn([1]);

        $this->configurable->saveProducts($mainProduct, [1, 2, 3]);
    }

    public function testSaveProductsForDuplicate()
    {
        $mainProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getIsDuplicate', '__sleep', '__wakeup', 'getTypeInstance', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        $mainProduct->expects($this->once())->method('getIsDuplicate')->will($this->returnValue(true));
        $mainProduct->expects($this->never())->method('getTypeInstance')->will($this->returnSelf());

        $this->configurable->saveProducts($mainProduct, [1, 2, 3]);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetConfigurableOptions()
    {
        $scope = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)->getMock();
        $scope->expects($this->any())->method('getId')->willReturn(123);

        $scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)->getMockForAbstractClass();
        $scopeResolver->expects($this->any())->method('getScope')->willReturn($scope);

        $configurable = $this->getMock(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable',
            [
                'getTable',
                'getConnection'
            ],
            [
                $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock(),
                $this->relation
            ],
            '',
            true
        );

        $reflection = new \ReflectionClass('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
        $reflectionProperty = $reflection->getProperty('scopeResolver');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($configurable, $scopeResolver);

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    '__sleep',
                    '__wakeup',
                    'getId',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getId')
            ->willReturn('getId value');

        $configurable->expects($this->exactly(6))
            ->method('getTable')
            ->will(
                $this->returnValueMap(
                    [
                        ['catalog_product_super_attribute', 'catalog_product_super_attribute value'],
                        ['catalog_product_super_link', 'catalog_product_super_link value'],
                        ['eav_attribute', 'eav_attribute value'],
                        ['catalog_product_entity', 'catalog_product_entity value'],
                        ['eav_attribute_option_value', 'eav_attribute_option_value value'],
                        ['catalog_product_super_attribute_label', 'catalog_product_super_attribute_label value']
                    ]
                )
            );
        $select = $this->getMock(
            '\Magento\Framework\DB\Select',
            [
                'from',
                'joinInner',
                'joinLeft',
                'where',
            ],
            [],
            '',
            false
        );
        $select->expects($this->once())
            ->method('from')
            ->with(
                ['super_attribute' => 'catalog_product_super_attribute value'],
                [
                    'sku' => 'entity.sku',
                    'product_id' => 'super_attribute.product_id',
                    'attribute_code' => 'attribute.attribute_code',
                    'option_title' => null,
                    'value_index' => 'entity_value.value',
                    'default_title' => 'default_option_value.value',
                ]
            )
            ->willReturnSelf();

        $superAttribute = $this->getMock(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute',
            [
                'getBackendTable',
                'getAttributeId',
            ],
            [],
            '',
            false
        );
        $superAttribute->expects($this->any())
            ->method('getBackendTable')
            ->willReturn('getBackendTable value');
        $superAttribute->expects($this->any())
            ->method('getAttributeId')
            ->willReturn('getAttributeId value');
        $attributes = [
            $superAttribute,
        ];

        $select->expects($this->exactly(4))
            ->method('joinInner')
            ->will($this->returnSelf())
            ->withConsecutive(
                [
                    ['product_link' => 'catalog_product_super_link value'],
                    'product_link.parent_id = super_attribute.product_id',
                    []
                ],
                [
                    ['attribute' => 'eav_attribute value'],
                    'attribute.attribute_id = super_attribute.attribute_id',
                    []
                ],
                [
                    ['entity' => 'catalog_product_entity value'],
                    'entity.entity_id = product_link.product_id',
                    []
                ],
                [
                    ['entity_value' => 'getBackendTable value'],
                    implode(
                        ' AND ',
                        [
                            'entity_value.attribute_id = super_attribute.attribute_id',
                            'entity_value.store_id = 0',
                            'entity_value.entity_id = product_link.product_id'
                        ]
                    ),
                    []
                ]
            );

        $select->expects($this->exactly(2))
            ->method('joinLeft')
            ->will($this->returnSelf())
            ->withConsecutive(
                [
                    ['option_value' => 'eav_attribute_option_value value'],
                    implode(
                        ' AND ',
                        [
                            'option_value.option_id = entity_value.value',
                            'option_value.store_id = ' . 123
                        ]
                    ),
                    []
                ],
                [
                    ['default_option_value' => 'eav_attribute_option_value value'],
                    implode(
                        ' AND ',
                        [
                            'default_option_value.option_id = entity_value.value',
                            'default_option_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    ),
                    []
                ]
            );
        $select->expects($this->exactly(2))
            ->method('where')
            ->will($this->returnSelf())
            ->withConsecutive(
                [
                    'super_attribute.product_id = ?',
                    'getId value'
                ],
                [
                    'attribute.attribute_id = ?',
                    'getAttributeId value'
                ]
            );


        $readerAdapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods([
                'select',
                'fetchAll',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $readerAdapter->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $readerAdapter->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn('fetchAll value');

        $configurable->expects($this->any())
            ->method('getConnection')
            ->willReturn($readerAdapter);
        $expectedAttributesOptionsData = [
            'getAttributeId value' => 'fetchAll value',
        ];

        $actualAttributesOptionsData = $configurable->getConfigurableOptions($product, $attributes);

        $this->assertEquals($expectedAttributesOptionsData, $actualAttributesOptionsData);
    }
}
