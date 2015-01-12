<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Filter;

use Magento\Framework\DB\Select;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class PreprocessorTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \Magento\Framework\Search\Request\FilterInterface|MockObject
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

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->conditionManager = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\ConditionManager')
            ->disableOriginalConstructor()
            ->setMethods(['wrapBrackets'])
            ->getMock();
        $this->scopeResolver = $this->getMockBuilder('\Magento\Framework\App\ScopeResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getScope'])
            ->getMockForAbstractClass();
        $this->scope = $this->getMockBuilder('\Magento\Framework\App\ScopeInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->will($this->returnValue($this->scope));
        $this->config = $this->getMockBuilder('\Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $this->attribute = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods(['getBackendTable', 'isStatic', 'getAttributeId'])
            ->getMockForAbstractClass();
        $this->resource = $resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'getIfNullSql'])
            ->getMockForAbstractClass();
        $this->select = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods(['from', 'where', '__toString', 'joinLeft', 'columns', 'having'])
            ->getMock();
        $this->connection->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->select));
        $resource->expects($this->atLeastOnce())
            ->method('getConnection')
            ->with(\Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE)
            ->will($this->returnValue($this->connection));
        $this->filter = $this->getMockBuilder('\Magento\Framework\Search\Request\FilterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getField', 'getValue'])
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

        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor',
            [
                'conditionManager' => $this->conditionManager,
                'scopeResolver' => $this->scopeResolver,
                'config' => $this->config,
                'resource' => $resource,
                'attributePrefix' => 'attr_'
            ]
        );
    }

    public function testProcessPrice()
    {
        $expectedResult = 'search_index.product_id IN (select entity_id from (TEST QUERY PART) as filter)';
        $scopeId = 0;
        $isNegation = false;
        $query = 'SELECT table.price FROM catalog_product_entity';

        $this->scope->expects($this->once())->method('getId')->will($this->returnValue($scopeId));
        $this->filter->expects($this->exactly(2))
            ->method('getField')
            ->will($this->returnValue('price'));
        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'price')
            ->will($this->returnValue($this->attribute));
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('catalog_product_index_price')
            ->will($this->returnValue('table_name'));
        $this->select->expects($this->once())
            ->method('from')
            ->with(['main_table' => 'table_name'], 'entity_id')
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('where')
            ->with('SELECT table.min_price FROM catalog_product_entity')
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('TEST QUERY PART'));

        $actualResult = $this->target->process($this->filter, $isNegation, $query);
        $this->assertSame($expectedResult, $this->removeWhitespaces($actualResult));
    }

    public function testProcessCategoryIds()
    {
        $expectedResult = 'category_index.category_id = FilterValue';
        $scopeId = 0;
        $isNegation = false;
        $query = 'SELECT category_ids FROM catalog_product_entity';

        $this->scope->expects($this->once())->method('getId')->will($this->returnValue($scopeId));
        $this->filter->expects($this->exactly(3))
            ->method('getField')
            ->will($this->returnValue('category_ids'));

        $this->filter->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('FilterValue'));

        $this->config->expects($this->exactly(1))
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'category_ids')
            ->will($this->returnValue($this->attribute));

        $actualResult = $this->target->process($this->filter, $isNegation, $query);
        $this->assertSame($expectedResult, $this->removeWhitespaces($actualResult));
    }

    public function testProcessStaticAttribute()
    {
        $expectedResult = 'search_index.product_id IN (select entity_id from (TEST QUERY PART) as filter)';
        $scopeId = 0;
        $isNegation = false;
        $query = 'SELECT field FROM table';

        $this->scope->expects($this->once())->method('getId')->will($this->returnValue($scopeId));
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
        $this->attribute->expects($this->once())
            ->method('getBackendTable')
            ->will($this->returnValue('backend_table'));
        $this->select->expects($this->once())
            ->method('from')
            ->with(['main_table' => 'backend_table'], 'entity_id')
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('where')
            ->with('SELECT field FROM table')
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('TEST QUERY PART'));

        $actualResult = $this->target->process($this->filter, $isNegation, $query);
        $this->assertSame($expectedResult, $this->removeWhitespaces($actualResult));
    }

    public function testProcessNotStaticAttribute()
    {
        $expectedResult = 'search_index.product_id IN (select entity_id from (TEST QUERY PART) as filter)';
        $scopeId = 0;
        $isNegation = false;
        $query = 'SELECT field FROM table';
        $attributeId = 1234567;

        $this->scope->expects($this->once())->method('getId')->will($this->returnValue($scopeId));
        $this->filter->expects($this->exactly(4))
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
        $this->select->expects($this->once())
            ->method('from')
            ->with(['main_table' => 'backend_table'], 'entity_id')
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
}
