<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Attribute;

use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilder;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionSelectBuilderTest extends TestCase
{
    /**
     * @var OptionSelectBuilder
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Attribute|MockObject
     */
    private $attributeResourceMock;

    /**
     * @var OptionProvider|MockObject
     */
    private $attributeOptionProviderMock;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $abstractAttributeMock;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scope;

    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->onlyMethods(['select', 'getIfNullSql'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->select = $this->getMockBuilder(Select::class)
            ->onlyMethods(['from', 'joinInner', 'joinLeft', 'where', 'columns', 'order'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select', 'getIfNullSql')
            ->willReturn($this->select);

        $this->attributeResourceMock = $this->getMockBuilder(Attribute::class)
            ->onlyMethods(['getTable', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeResourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->attributeOptionProviderMock = $this->getMockBuilder(OptionProvider::class)
            ->onlyMethods(['getProductEntityLinkField'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->abstractAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->onlyMethods(['getBackendTable', 'getAttributeId', 'getSourceModel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            OptionSelectBuilder::class,
            [
                'attributeResource' => $this->attributeResourceMock,
                'attributeOptionProvider' => $this->attributeOptionProviderMock,
            ]
        );
    }

    /**
     * Test for method getSelect
     */
    public function testGetSelect()
    {
        $this->select->expects($this->exactly(1))->method('from')->willReturnSelf();
        $this->select->expects($this->exactly(1))->method('columns')->willReturnSelf();
        $this->select->expects($this->exactly(5))->method('joinInner')->willReturnSelf();
        $this->select->expects($this->exactly(4))->method('joinLeft')->willReturnSelf();
        $this->select->expects($this->exactly(1))->method('order')->willReturnSelf();
        $this->select->expects($this->exactly(2))->method('where')->willReturnSelf();

        $this->attributeResourceMock->expects($this->exactly(9))
            ->method('getTable')
            ->willReturnMap(
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
            );

        $this->abstractAttributeMock->expects($this->atLeastOnce())
            ->method('getAttributeId')
            ->willReturn('getAttributeId value');
        $this->abstractAttributeMock->expects($this->atLeastOnce())
            ->method('getBackendTable')
            ->willReturn('getMainTable value');

        $this->scope->expects($this->any())->method('getId')->willReturn(123);

        $this->assertEquals(
            $this->select,
            $this->model->getSelect($this->abstractAttributeMock, 4, $this->scope)
        );
    }

    /**
     * Test for method getSelect with backend table
     */
    public function testGetSelectWithBackendModel()
    {
        $this->select->expects($this->exactly(1))->method('from')->willReturnSelf();
        $this->select->expects($this->exactly(0))->method('columns')->willReturnSelf();
        $this->select->expects($this->exactly(5))->method('joinInner')->willReturnSelf();
        $this->select->expects($this->exactly(2))->method('joinLeft')->willReturnSelf();
        $this->select->expects($this->exactly(1))->method('order')->willReturnSelf();
        $this->select->expects($this->exactly(2))->method('where')->willReturnSelf();

        $this->attributeResourceMock->expects($this->exactly(7))
            ->method('getTable')
            ->willReturnMap(
                [
                    ['catalog_product_super_attribute', 'catalog_product_super_attribute value'],
                    ['catalog_product_entity', 'catalog_product_entity value'],
                    ['catalog_product_super_link', 'catalog_product_super_link value'],
                    ['eav_attribute', 'eav_attribute value'],
                    ['catalog_product_entity', 'catalog_product_entity value'],
                    ['catalog_product_super_attribute_label', 'catalog_product_super_attribute_label value'],
                    ['eav_attribute_option', 'eav_attribute_option value']
                ]
            );

        $this->abstractAttributeMock->expects($this->atLeastOnce())
            ->method('getAttributeId')
            ->willReturn('getAttributeId value');
        $this->abstractAttributeMock->expects($this->atLeastOnce())
            ->method('getBackendTable')
            ->willReturn('getMainTable value');
        $this->abstractAttributeMock->expects($this->atLeastOnce())
            ->method('getSourceModel')
            ->willReturn('source model value');

        $this->scope->expects($this->any())->method('getId')->willReturn(123);

        $this->assertEquals(
            $this->select,
            $this->model->getSelect($this->abstractAttributeMock, 4, $this->scope)
        );
    }
}
