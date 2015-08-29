<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\CatalogSearch\Model\Search\TableMapper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TableMapperTest extends \PHPUnit_Framework_TestCase
{
    const WEBSITE_ID = 4512;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollection;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $website;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\CatalogSearch\Model\Search\TableMapper
     */
    private $target;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->any())
            ->method('quoteInto')
            ->willReturnCallback(
                function ($query, $expression) {
                    return str_replace('?', $expression, $query);
                }
            );

        $this->resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->method('getTableName')
            ->willReturnCallback(
                function ($table) {
                    return 'prefix_' . $table;
                }
            );
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->website = $this->getMockBuilder('\Magento\Store\Api\Data\WebsiteInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->website->expects($this->any())
            ->method('getId')
            ->willReturn(self::WEBSITE_ID);
        $this->storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->willReturn($this->website);
        $this->attributeCollection = $this->getMockBuilder(
            '\Magento\Catalog\Model\Resource\Product\Attribute\Collection'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCollectionFactory = $this->getMockBuilder(
            '\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeCollection);
        $this->target = $objectManager->getObject(
            '\Magento\CatalogSearch\Model\Search\TableMapper',
            [
                'resource' => $this->resource,
                'storeManager' => $this->storeManager,
                'attributeCollectionFactory' => $attributeCollectionFactory
            ]
        );

        $this->select = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder('\Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testAddPriceFilter()
    {
        $priceFilter = $this->createRangeFilter('price');
        $query = $this->createFilterQuery($priceFilter);
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['price_index' => 'prefix_catalog_product_index_price'],
                'search_index.entity_id = price_index.entity_id AND price_index.website_id = ' . self::WEBSITE_ID,
                []
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddStaticAttributeFilter()
    {
        $priceFilter = $this->createRangeFilter('static');
        $query = $this->createFilterQuery($priceFilter);
        $this->createAttributeMock('static', 'static', 'backend_table');
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['4111c4a3daddb5c5dba31cdac705114b' => 'backend_table'],
                'search_index.entity_id = 4111c4a3daddb5c5dba31cdac705114b.entity_id',
                null
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddCategoryIds()
    {
        $categoryIdsFilter = $this->createTermFilter('category_ids');
        $query = $this->createFilterQuery($categoryIdsFilter);
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['category_ids_index' => 'prefix_catalog_category_product_index'],
                'search_index.entity_id = category_ids_index.product_id',
                []
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddTermFilter()
    {
        $categoryIdsFilter = $this->createTermFilter('color');
        $query = $this->createFilterQuery($categoryIdsFilter);
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['cpie' => 'prefix_catalog_product_index_eav'],
                'search_index.entity_id = cpie.entity_id',
                []
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddBoolQueryWithTermFiltersInside()
    {
        $query = $this->createBoolQuery(
            [
                $this->createFilterQuery($this->createTermFilter('must1')),
            ],
            [
                $this->createFilterQuery($this->createTermFilter('should1')),
            ],
            [
                $this->createFilterQuery($this->createTermFilter('mustNot1')),
            ]
        );
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->at(0))
            ->method('joinLeft')
            ->with(
                ['cpie' => 'prefix_catalog_product_index_eav'],
                'search_index.entity_id = cpie.entity_id',
                []
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddBoolQueryWithTermAndPriceFiltersInside()
    {
        $query = $this->createBoolQuery(
            [
                $this->createFilterQuery($this->createTermFilter('must1')),
                $this->createFilterQuery($this->createRangeFilter('price')),
            ],
            [
                $this->createFilterQuery($this->createTermFilter('should1')),
            ],
            [
                $this->createFilterQuery($this->createTermFilter('mustNot1')),
            ]
        );
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->at(0))
            ->method('joinLeft')
            ->with(
                ['cpie' => 'prefix_catalog_product_index_eav'],
                'search_index.entity_id = cpie.entity_id',
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(1))
            ->method('joinLeft')
            ->with(
                ['price_index' => 'prefix_catalog_product_index_price'],
                'search_index.entity_id = price_index.entity_id AND price_index.website_id = ' . self::WEBSITE_ID,
                []
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddBoolFilterWithTermFiltersInside()
    {
        $query = $this->createFilterQuery(
            $this->createBoolFilter(
                [
                    $this->createTermFilter('must1'),
                ],
                [
                    $this->createTermFilter('should1'),
                ],
                [
                    $this->createTermFilter('mustNot1'),
                ]
            )
        );
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['cpie' => 'prefix_catalog_product_index_eav'],
                'search_index.entity_id = cpie.entity_id',
                []
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddBoolFilterWithBoolFiltersInside()
    {
        $query = $this->createFilterQuery(
            $this->createBoolFilter(
                [
                    $this->createBoolFilter([$this->createTermFilter('must1')], [], []),
                ],
                [
                    $this->createBoolFilter([$this->createTermFilter('should1')], [], []),
                ],
                [
                    $this->createBoolFilter([$this->createTermFilter('mustNot1')], [], []),
                ]
            )
        );
        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['cpie' => 'prefix_catalog_product_index_eav'],
                'search_index.entity_id = cpie.entity_id',
                []
            )
            ->willReturnSelf();
        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    /**
     * @param $filter
     * @return \Magento\Framework\Search\Request\Query\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFilterQuery($filter)
    {
        $query = $this->getMockBuilder('\Magento\Framework\Search\Request\Query\Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $query->method('getType')
            ->willReturn(QueryInterface::TYPE_FILTER);
        $query->method('getReference')
            ->willReturn($filter);
        return $query;
    }

    /**
     * @param array $must
     * @param array $should
     * @param array $mustNot
     * @return \Magento\Framework\Search\Request\Query\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     * @internal param $filter
     */
    private function createBoolQuery(array $must, array $should, array $mustNot)
    {
        $query = $this->getMockBuilder('\Magento\Framework\Search\Request\Query\BoolExpression')
            ->disableOriginalConstructor()
            ->getMock();
        $query->method('getType')
            ->willReturn(QueryInterface::TYPE_BOOL);
        $query->method('getMust')
            ->willReturn($must);
        $query->method('getShould')
            ->willReturn($should);
        $query->method('getMustNot')
            ->willReturn($mustNot);
        return $query;
    }

    /**
     * @param array $must
     * @param array $should
     * @param array $mustNot
     * @return \Magento\Framework\Search\Request\Filter\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     * @internal param $filter
     */
    private function createBoolFilter(array $must, array $should, array $mustNot)
    {
        $query = $this->getMockBuilder('\Magento\Framework\Search\Request\Filter\BoolExpression')
            ->disableOriginalConstructor()
            ->getMock();
        $query->method('getType')
            ->willReturn(FilterInterface::TYPE_BOOL);
        $query->method('getMust')
            ->willReturn($must);
        $query->method('getShould')
            ->willReturn($should);
        $query->method('getMustNot')
            ->willReturn($mustNot);
        return $query;
    }

    /**
     * @param string $field
     * @return \Magento\Framework\Search\Request\Filter\Range|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRangeFilter($field)
    {
        $filter = $this->createFilterMock(
            '\Magento\Framework\Search\Request\Filter\Range',
            FilterInterface::TYPE_RANGE,
            $field
        );
        return $filter;
    }

    /**
     * @param string $field
     * @return \Magento\Framework\Search\Request\Filter\Term|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTermFilter($field)
    {
        $filter = $this->createFilterMock(
            '\Magento\Framework\Search\Request\Filter\Term',
            FilterInterface::TYPE_TERM,
            $field
        );
        return $filter;
    }

    /**
     * @param string $class
     * @param string $type
     * @param string $field
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createFilterMock($class, $type, $field)
    {
        $filter = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->method('getType')
            ->willReturn($type);
        $filter->method('getField')
            ->willReturn($field);
        return $filter;
    }

    /**
     * @param string $code
     * @param string $backendType
     * @param string $backendTable
     */
    private function createAttributeMock($code, $backendType = null, $backendTable = null)
    {
        $attribute = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Eav\Attribute')
            ->setMethods(['getBackendType', 'getBackendTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->method('getBackendType')
            ->willReturn($backendType);
        $attribute->method('getBackendTable')
            ->willReturn($backendTable);
        $this->attributeCollection->method('getItemByColumnValue')
            ->with('attribute_code', $code)
            ->willReturn($attribute);
    }
}
