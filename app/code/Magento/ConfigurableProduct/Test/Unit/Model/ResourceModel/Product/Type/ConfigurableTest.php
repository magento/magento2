<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var ProductRelation|MockObject
     */
    private $relation;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $abstractAttribute;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var AttributeOptionProvider|MockObject
     */
    private $attributeOptionProvider;

    /**
     * @var OptionProvider|MockObject
     */
    private $optionProvider;

    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['select', 'fetchAll', 'insertOnDuplicate'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->setMethods(['getConnection', 'getTableName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->relation = $this->getMockBuilder(ProductRelation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractAttribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getBackendTable', 'getAttributeId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->product = $this->getMockBuilder(Product::class)
            ->setMethods(['__sleep', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionProvider = $this->getMockBuilder(AttributeOptionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionProvider = $this->getMockBuilder(OptionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getResources'])
            ->setConstructorArgs(
                $this->objectManagerHelper->getConstructArguments(
                    Context::class,
                    [
                        'resources' => $this->resource
                    ]
                )
            )
            ->getMock();
        $context->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->configurable = $this->objectManagerHelper->getObject(
            Configurable::class,
            [
                'catalogProductRelation' => $this->relation,
                'scopeResolver' => $scopeResolver,
                'attributeOptionProvider' => $this->attributeOptionProvider,
                'optionProvider' => $this->optionProvider,
                'context' => $context
            ]
        );
    }

    public function testSaveProducts()
    {
        $this->product->expects($this->once())
            ->method('getData')
            ->willReturn(3);
        $this->optionProvider->expects($this->once())
            ->method('getProductEntityLinkField')
            ->willReturnSelf();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->resource->expects($this->any())->method('getTableName')->willReturn('table name');

        $select = $this->getMockBuilder(Select::class)
            ->setMethods(['from', 'where'])
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->exactly(1))->method('from')->willReturnSelf();
        $select->expects($this->exactly(1))->method('where')->willReturnSelf();

        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($select);

        $existingProductIds = [1, 2];
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($select)
            ->willReturn($existingProductIds);

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->with(
                'table name',
                [
                    ['product_id' => 3, 'parent_id' => 3],
                    ['product_id' => 4, 'parent_id' => 3],
                ]
            )
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with(
                'table name',
                ['parent_id = ?' => 3, 'product_id IN (?)' => [1]]
            )
            ->willReturnSelf();

        $this->assertSame(
            $this->configurable,
            $this->configurable->saveProducts($this->product, [2, 3, 4])
        );
    }

    public function testGetConfigurableOptions()
    {
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

        $this->optionProvider->expects($this->once())
            ->method('getProductEntityLinkField')
            ->willReturn('link');
        $this->attributeOptionProvider->expects($this->once())
            ->method('getAttributeOptions')
            ->willReturn('fetchAll value');

        $expectedAttributesOptionsData = [
            'getAttributeId value' => 'fetchAll value',
        ];
        $actualAttributesOptionsData = $this->configurable->getConfigurableOptions($this->product, $attributes);
        $this->assertEquals($expectedAttributesOptionsData, $actualAttributesOptionsData);
    }
}
