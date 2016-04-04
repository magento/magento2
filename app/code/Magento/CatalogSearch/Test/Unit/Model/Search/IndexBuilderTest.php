<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

/**
 * Test for \Magento\CatalogSearch\Model\Search\IndexBuilder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\CatalogSearch\Model\Search\TableMapper|\PHPUnit_Framework_MockObject_MockObject */
    private $tableMapper;

    /** @var  \Magento\Framework\Search\Adapter\Mysql\ConditionManager|\PHPUnit_Framework_MockObject_MockObject */
    private $conditionManager;

    /** @var  \Magento\Search\Model\IndexScopeResolver|\PHPUnit_Framework_MockObject_MockObject */
    private $scopeResolver;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $connection;

    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    private $select;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $config;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $storeManager;

    /** @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var \Magento\Search\Model\IndexScopeResolver|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    /** @var \Magento\CatalogInventory\Api\StockConfigurationInterface|MockObject */
    private $stockConfiguration;

    /**
     * @var \Magento\CatalogSearch\Model\Search\IndexBuilder
     */
    private $target;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dimensionScopeResolver;

    /**
     * @var \Magento\Framework\App\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeInterface;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp()
    {
        $this->select = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods(['from', 'joinLeft', 'where', 'joinInner'])
            ->getMock();

        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'quoteInto'])
            ->getMockForAbstractClass();
        $this->connection->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->select));

        $this->resource = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));

        $this->request = $this->getMockBuilder('\Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getIndex', 'getDimensions', 'getQuery'])
            ->getMockForAbstractClass();

        $this->config = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag'])
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')->getMock();

        $this->scopeResolver = $this->getMockBuilder('\Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->conditionManager = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\ConditionManager')
            ->setMethods(['combineQueries', 'wrapBrackets', 'generateCondition'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->conditionManager->expects($this->any())
            ->method('combineQueries')
            ->willReturnCallback(
                function (array $queries, $expression) {
                    return implode(' ' . $expression . ' ', $queries);
                }
            );
        $this->conditionManager->expects($this->any())
            ->method('wrapBrackets')
            ->willReturnCallback(
                function ($expression) {
                    return '(' . $expression . ')';
                }
            );
        $this->conditionManager->expects($this->any())
            ->method('generateCondition')
            ->willReturnCallback(
                function ($left, $operator, $right) {
                    return $left . $operator . $right;
                }
            );

        $this->tableMapper = $this->getMockBuilder('\Magento\CatalogSearch\Model\Search\TableMapper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->tableMapper->expects($this->once())
            ->method('addTables')
            ->with($this->select, $this->request)
            ->willReturnArgument(0);
        $this->dimensionScopeResolver = $this->getMockForAbstractClass(
            '\Magento\Framework\App\ScopeResolverInterface',
            [],
            '',
            false
        );
        $this->scopeInterface = $this->getMockForAbstractClass(
            '\Magento\Framework\App\ScopeInterface',
            [],
            '',
            false
        );
        $this->stockConfiguration = $this
            ->getMockBuilder('\Magento\CatalogInventory\Api\StockConfigurationInterface')
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Search\IndexBuilder',
            [
                'resource' => $this->resource,
                'config' => $this->config,
                'storeManager' => $this->storeManager,
                'conditionManager' => $this->conditionManager,
                'scopeResolver' => $this->scopeResolver,
                'tableMapper' => $this->tableMapper,
                'dimensionScopeResolver' => $this->dimensionScopeResolver
            ]
        );

        // Todo: \Magento\Framework\TestFramework\Unit\Helper\ObjectManager to do this automatically (MAGETWO-49793)
        $reflection = new \ReflectionClass(get_class($this->target));
        $reflectionProperty = $reflection->getProperty('stockConfiguration');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->target, $this->stockConfiguration);
    }

    public function testBuildWithOutOfStock()
    {
        $tableSuffix = '';
        $index = 'test_name_of_index';

        $this->mockBuild($index, $tableSuffix);

        $this->config->expects($this->once())
            ->method('isSetFlag')
            ->with('cataloginventory/options/show_out_of_stock')
            ->will($this->returnValue(true));

        $this->request->expects($this->exactly(2))
            ->method('getDimensions')
            ->willReturn([]);

        $result = $this->target->build($this->request);
        $this->assertSame($this->select, $result);
    }

    public function testBuildWithoutOutOfStock()
    {
        $scopeId = '113';
        $tableSuffix = 'scope113_someNamesomeValue';
        $index = 'test_index_name';

        $dimensions = [
            $this->createDimension('scope', $scopeId),
            $this->createDimension('someName', 'someValue'),
        ];

        $this->request->expects($this->exactly(2))
            ->method('getDimensions')
            ->willReturn($dimensions);
        $this->dimensionScopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn('someValue');

        $this->mockBuild($index, $tableSuffix, false);

        $this->stockConfiguration->expects($this->once())->method('getDefaultScopeId')->willReturn(1);
        $this->config->expects($this->once())
            ->method('isSetFlag')
            ->with('cataloginventory/options/show_out_of_stock')
            ->will($this->returnValue(false));
        $this->connection->expects($this->once())->method('quoteInto')
            ->with(' AND stock_index.website_id = ?', 1)->willReturn(' AND stock_index.website_id = 1');
        $this->select->expects($this->at(2))
            ->method('where')
            ->with('(someName=someValue)')
            ->willReturnSelf();
        $this->select->expects($this->at(3))
            ->method('joinLeft')
            ->with(
                ['stock_index' => 'cataloginventory_stock_status'],
                'search_index.entity_id = stock_index.product_id'
                . ' AND stock_index.website_id = 1',
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(4))
            ->method('where')
            ->with('stock_index.stock_status = ?', 1)
            ->will($this->returnSelf());

        $result = $this->target->build($this->request);
        $this->assertSame($this->select, $result);
    }

    protected function mockBuild($index, $tableSuffix, $hasFilters = false)
    {
        $this->request->expects($this->atLeastOnce())
            ->method('getIndex')
            ->will($this->returnValue($index));

        $this->resource->expects($this->any())
            ->method('getTableName')
            ->will(
                $this->returnCallback(
                    function ($index) {
                        return is_array($index) ? $index[0] . $index[1] : $index;
                    }
                )
            );

        $this->scopeResolver->expects($this->any())
            ->method('resolve')
            ->will(
                $this->returnCallback(
                    function ($index, $dimensions) {
                        $tableNameParts = [];
                        foreach ($dimensions as $dimension) {
                            $tableNameParts[] = $dimension->getName() . $dimension->getValue();
                        }
                        return $index . '_' . implode('_', $tableNameParts);
                    }
                )
            );

        $this->select->expects($this->at(0))
            ->method('from')
            ->with(
                ['search_index' => $index . '_' . $tableSuffix],
                ['entity_id' => 'entity_id']
            )
            ->will($this->returnSelf());

        $this->select->expects($this->at(1))
            ->method('joinLeft')
            ->with(
                ['cea' => 'catalog_eav_attribute'],
                'search_index.attribute_id = cea.attribute_id',
                []
            )
            ->will($this->returnSelf());
        if ($hasFilters) {
            $this->select->expects($this->at(2))
                ->method('joinLeft')
                ->with(
                    ['category_index' => 'catalog_category_product_index'],
                    'search_index.entity_id = category_index.product_id',
                    []
                )
                ->will($this->returnSelf());
            $this->select->expects($this->at(3))
                ->method('joinLeft')
                ->with(
                    ['cpie' => $this->resource->getTableName('catalog_product_index_eav')],
                    'search_index.entity_id = cpie.entity_id AND search_index.attribute_id = cpie.attribute_id',
                    []
                )
                ->willReturnSelf();
        }
    }

    /**
     * @param $name
     * @param $value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createDimension($name, $value)
    {
        $dimension = $this->getMockBuilder('\Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getName', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($value);
        return $dimension;
    }
}
