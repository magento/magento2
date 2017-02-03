<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
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
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var ProductRelation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $relation;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractAttribute;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var AttributeOptionProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeOptionProvider;

    /**
     * @var OptionProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionProvider;

    /**
     * @var ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolver;

    protected function setUp()
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
        $this->product = $this->getMockBuilder(Product::class)
            ->setMethods(['__sleep', '__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionProvider = $this->getMockBuilder(AttributeOptionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionProvider = $this->getMockBuilder(OptionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->getMock(
            Context::class,
            ['getResources'],
            $this->objectManagerHelper->getConstructArguments(
                Context::class,
                [
                    'resources' => $this->resource
                ]
            )
        );
        $context->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->configurable = $this->objectManagerHelper->getObject(
            Configurable::class,
            [
                'catalogProductRelation' => $this->relation,
                'scopeResolver' => $this->scopeResolver,
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
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->willReturnSelf();

        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->resource->expects($this->any())->method('getTableName')->willReturn('table name');

        $statement  = $this->getMockBuilder(\Zend_Db_Statement::class)->disableOriginalConstructor()->getMock();
        $statement->method('fetchAll')->willReturn([1]);

        $this->assertSame(
            $this->configurable,
            $this->configurable->saveProducts($this->product, [1, 2, 3])
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
