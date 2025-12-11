<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resource = $this->createPartialMock(ResourceConnection::class, ['getConnection', 'getTableName']);

        $this->relation = $this->createMock(ProductRelation::class);
        $scopeResolver = $this->createMock(ScopeResolverInterface::class);
        $this->abstractAttribute = $this->createMock(AbstractAttribute::class);
        $this->product = $this->createPartialMock(Product::class, ['__sleep', 'getData']);
        $this->attributeOptionProvider = $this->createMock(AttributeOptionProvider::class);
        $this->optionProvider = $this->createMock(OptionProvider::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->createPartialMock(Context::class, ['getResources']);
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
        $this->resource->method('getConnection')->willReturn($this->connectionMock);
        $this->resource->method('getTableName')->willReturn('table name');

        $select = $this->createPartialMock(Select::class, ['from', 'where']);
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

        $this->abstractAttribute->method('getBackendTable')->willReturn('getBackendTable value');
        $this->abstractAttribute->method('getAttributeId')->willReturn('getAttributeId value');
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
