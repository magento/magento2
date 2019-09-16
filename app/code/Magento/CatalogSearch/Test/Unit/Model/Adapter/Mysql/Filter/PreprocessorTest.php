<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Filter;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class PreprocessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AliasResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasResolver;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor
     */
    protected $target;

    /**
     * @var Resource|MockObject
     */
    private $resource;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|MockObject
     */
    private $attribute;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var FilterInterface|MockObject
     */
    private $filter;

    /**
     * @var \Magento\Framework\App\ScopeInterface|MockObject
     */
    private $scope;

    /**
     * @var \Magento\Eav\Model\Config|MockObject
     */
    private $config;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ConditionManager|MockObject
     */
    private $conditionManager;

    /**
     * @var MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var int
     */
    private $customerGroupId = 42;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->conditionManager = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\ConditionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['wrapBrackets'])
            ->getMock();
        $this->scopeResolver = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScope'])
            ->getMockForAbstractClass();
        $this->scope = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue($this->scope));
        $this->config = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $methods = ['getBackendTable', 'isStatic', 'getAttributeId',
            'getAttributeCode', 'getFrontendInput', 'getBackendType'
        ];
        $this->attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMockForAbstractClass();
        $this->resource = $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'getIfNullSql', 'quote', 'quoteInto'])
            ->getMockForAbstractClass();
        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'join', 'where', '__toString', 'joinLeft', 'columns', 'having'])
            ->getMock();
        $this->connection->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));
        $this->connection->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));
        $resource->expects($this->atLeastOnce())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));
        $this->filter = $this->getMockBuilder(\Magento\Framework\Search\Request\FilterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getField', 'getValue', 'getType'])
            ->getMockForAbstractClass();

        $this->conditionManager->expects($this->any())
            ->method('wrapBrackets')
            ->with($this->select)
            ->will(
                $this->returnCallback(
                    function ($select) {
                        return '(' . $select . ')';
                    }
                )
            );

        $this->aliasResolver = $this->getMockBuilder(AliasResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($metadata);
        $metadata->expects($this->any())->method('getLinkField')->willReturn('entity_id');

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMock();

        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn($this->customerGroupId);

        $this->target = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor::class,
            [
                'conditionManager' => $this->conditionManager,
                'scopeResolver' => $this->scopeResolver,
                'config' => $this->config,
                'resource' => $resource,
                'attributePrefix' => 'attr_',
                'metadataPool' => $this->metadataPoolMock,
                'aliasResolver' => $this->aliasResolver,
                'customerSession' => $this->customerSessionMock
            ]
        );
    }

    public function testProcessPrice()
    {
        $expectedResult = 'price_index.min_price = 23 AND price_index.customer_group_id = ' . $this->customerGroupId;
        $isNegation = false;
        $query = 'price = 23';

        $this->filter->expects($this->exactly(2))
            ->method('getField')
            ->will($this->returnValue('price'));
        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'price')
            ->will($this->returnValue($this->attribute));

        $actualResult = $this->target->process($this->filter, $isNegation, $query);
        $this->assertSame($expectedResult, $this->removeWhitespaces($actualResult));
    }

    /**
     * @return array
     */
    public function processCategoryIdsDataProvider()
    {
        return [
            ['5', "category_ids_index.category_id in ('5')"],
            [3, "category_ids_index.category_id in (3)"],
            ["' and 1 = 0", "category_ids_index.category_id in ('\' and 1 = 0')"],
            [['5', '10'], "category_ids_index.category_id in ('5', '10')"]
        ];
    }

    /**
     * @param string|int $categoryId
     * @param string $expectedResult
     * @dataProvider processCategoryIdsDataProvider
     */
    public function testProcessCategoryIds($categoryId, $expectedResult)
    {
        $isNegation = false;
        $query = 'SELECT category_ids FROM catalog_product_entity';

        $this->filter->expects($this->exactly(3))
            ->method('getField')
            ->will($this->returnValue('category_ids'));

        $this->filter->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($categoryId));

        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'category_ids')
            ->will($this->returnValue($this->attribute));

        $this->connection
            ->expects($this->once())
            ->method('quoteInto')
            ->with('category_ids_index.category_id in (?)', $categoryId)
            ->willReturn($expectedResult);

        $actualResult = $this->target->process($this->filter, $isNegation, $query);
        $this->assertSame($expectedResult, $this->removeWhitespaces($actualResult));
    }

    public function testProcessStaticAttribute()
    {
        $expectedResult = 'attr_table_alias.static_attribute LIKE %name%';
        $isNegation = false;
        $query = 'static_attribute LIKE %name%';

        $this->attribute->method('getAttributeCode')
            ->willReturn('static_attribute');
        $this->aliasResolver->expects($this->once())->method('getAlias')
            ->willReturn('attr_table_alias');
        $this->filter->expects($this->exactly(3))
            ->method('getField')
            ->will($this->returnValue('static_attribute'));
        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'static_attribute')
            ->will($this->returnValue($this->attribute));
        $this->attribute->expects($this->once())
            ->method('isStatic')
            ->will($this->returnValue(true));

        $actualResult = $this->target->process($this->filter, $isNegation, $query);
        $this->assertSame($expectedResult, $this->removeWhitespaces($actualResult));
    }

    /**
     * @dataProvider testTermFilterDataProvider
     */
    public function testProcessTermFilter($frontendInput, $fieldValue, $isNegation, $expected)
    {
        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'termField')
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())
            ->method('isStatic')
            ->will($this->returnValue(false));

        $this->filter->expects($this->once())
            ->method('getType')
            ->willReturn(FilterInterface::TYPE_TERM);
        $this->attribute->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);

        $this->aliasResolver->expects($this->once())->method('getAlias')
            ->willReturn('termAttrAlias');

        $this->filter->expects($this->exactly(4))
            ->method('getField')
            ->willReturn('termField');
        $this->filter->expects($this->exactly(2))
            ->method('getValue')
        ->willReturn($fieldValue);

        $this->connection->expects($this->atLeastOnce())->method('quote')->willReturnArgument(0);
        $actualResult = $this->target->process($this->filter, $isNegation, 'This filter is not depends on used query');
        $this->assertSame($expected, $this->removeWhitespaces($actualResult));
    }

    /**
     * @return array
     */
    public function testTermFilterDataProvider()
    {
        return [
            'selectPositiveEqual' => [
                'frontendInput' => 'select',
                'fieldValue' => 'positiveValue',
                'isNegation' => false,
                'expected' => 'termAttrAlias.value = positiveValue',
            ],
            'selectPositiveArray' => [
                'frontendInput' => 'select',
                'fieldValue' => [2, 3, 15],
                'isNegation' => false,
                'expected' => 'termAttrAlias.value IN (2,3,15)',
            ],
            'selectNegativeEqual' => [
                'frontendInput' => 'select',
                'fieldValue' => 'positiveValue',
                'isNegation' => true,
                'expected' => 'termAttrAlias.value != positiveValue',
            ],
            'selectNegativeArray' => [
                'frontendInput' => 'select',
                'fieldValue' => [4, 3, 42],
                'isNegation' => true,
                'expected' => 'termAttrAlias.value NOT IN (4,3,42)',
            ],
            'multiSelectPositiveEqual' => [
                'frontendInput' => 'multiselect',
                'fieldValue' => 'positiveValue',
                'isNegation' => false,
                'expected' => 'termAttrAlias.value = positiveValue',
            ],
            'multiSelectPositiveArray' => [
                'frontendInput' => 'multiselect',
                'fieldValue' => [2, 3, 15],
                'isNegation' => false,
                'expected' => 'termAttrAlias.value IN (2,3,15)',
            ],
            'multiSelectNegativeEqual' => [
                'frontendInput' => 'multiselect',
                'fieldValue' => 'negativeValue',
                'isNegation' => true,
                'expected' => 'termAttrAlias.value != negativeValue',
            ],
            'multiSelectNegativeArray' => [
                'frontendInput' => 'multiselect',
                'fieldValue' => [4, 3, 42],
                'isNegation' => true,
                'expected' => 'termAttrAlias.value NOT IN (4,3,42)',
            ],
        ];
    }

    public function testProcessNotStaticAttribute()
    {
        $expectedResult = 'search_index.entity_id IN (select entity_id from (TEST QUERY PART) as filter)';
        $scopeId = 0;
        $isNegation = false;
        $query = 'SELECT field FROM table';
        $attributeId = 1234567;

        $this->scope->expects($this->once())->method('getId')->will($this->returnValue($scopeId));
        $this->filter->expects($this->exactly(5))
            ->method('getField')
            ->will($this->returnValue('not_static_attribute'));
        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'not_static_attribute')
            ->will($this->returnValue($this->attribute));
        $this->attribute->expects($this->once())
            ->method('isStatic')
            ->will($this->returnValue(false));
        $this->attribute->expects($this->once())
            ->method('getBackendTable')
            ->will($this->returnValue('backend_table'));
        $this->attribute->expects($this->once())
            ->method('getAttributeId')
            ->will($this->returnValue($attributeId));
        $this->connection->expects($this->once())
            ->method('getIfNullSql')
            ->with('current_store.value', 'main_table.value')
            ->will($this->returnValue('IF NULL SQL'));
        $this->resource->expects($this->once())->method('getTableName')->willReturn('catalog_product_entity');
        $this->select->expects($this->once())
            ->method('from')
            ->with(['e' => 'catalog_product_entity'], ['entity_id'])
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('join')
            ->with(['main_table' => 'backend_table'], "main_table.entity_id = e.entity_id")
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(['current_store' => 'backend_table'])
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('columns')
            ->with(['not_static_attribute' => 'IF NULL SQL'])
            ->will($this->returnSelf());
        $this->select->expects($this->exactly(2))
            ->method('where')
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('TEST QUERY PART'));

        $actualResult = $this->target->process($this->filter, $isNegation, $query);
        $this->assertSame($expectedResult, $this->removeWhitespaces($actualResult));
    }

    /**
     * @param $actualResult
     * @return mixed
     */
    private function removeWhitespaces($actualResult)
    {
        return preg_replace(['/(\s)+/', '/(\() /', '/ (\))/'], '${1}', $actualResult);
    }

    public function testProcessRangeFilter()
    {
        $query = 'static_attribute LIKE %name%';
        $expected = 'search_index.entity_id IN (select entity_id from () as filter)';

        $this->filter->expects($this->any())
            ->method('getField')
            ->willReturn('termField');
        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'termField')
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())
            ->method('isStatic')
            ->will($this->returnValue(false));

        $this->filter->expects($this->any())
            ->method('getType')
            ->willReturn(FilterInterface::TYPE_RANGE);
        $this->attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn('decimal');

        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('join')->willReturnSelf();
        $this->select->expects($this->any())->method('columns')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->select->expects($this->any())->method('having')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $actualResult = $this->target->process($this->filter, false, $query);
        $this->assertSame($expected, $this->removeWhitespaces($actualResult));
    }
}
