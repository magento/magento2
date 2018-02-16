<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;

/**
 * Test for \Magento\CatalogSearch\Model\Search\TableMapper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TableMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\CatalogSearch\Model\Search\TableMapper
     */
    private $target;

    /**
     * @var AliasResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasResolver;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->never())->method('quoteInto');

        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource->expects($this->never())->method('getTableName');
        $this->resource->expects($this->never())->method('getConnection');

        $this->website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->website->expects($this->never())->method('getId');

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->never())->method('getWebsite');
        $this->storeManager->expects($this->never())->method('getStore');

        $this->attributeCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeCollection);

        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->setMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aliasResolver = $this->getMockBuilder(AliasResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->aliasResolver->expects($this->any())
            ->method('getAlias')
            ->willReturnCallback(function (FilterInterface $filter) {
                return $filter->getField() . '_alias';
            });
        $this->target = $objectManager->getObject(
            \Magento\CatalogSearch\Model\Search\TableMapper::class,
            [
                'resource' => $this->resource,
                'storeManager' => $this->storeManager,
                'attributeCollectionFactory' => $attributeCollectionFactory,
                'eavConfig' => $this->eavConfig,
                'aliasResolver' => $this->aliasResolver,
            ]
        );

        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\Search\RequestInterface::class)
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

        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    public function testAddStaticAttributeFilter()
    {
        $priceFilter = $this->createRangeFilter('static');
        $query = $this->createFilterQuery($priceFilter);

        $this->request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

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

        $select = $this->target->addTables($this->select, $this->request);
        $this->assertEquals($this->select, $select, 'Returned results isn\'t equal to passed select');
    }

    /**
     * @param $filter
     *
     * @return \Magento\Framework\Search\Request\Query\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFilterQuery($filter)
    {
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Filter::class)
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
     *
     * @return \Magento\Framework\Search\Request\Query\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     *
     * @internal param $filter
     */
    private function createBoolQuery(array $must, array $should, array $mustNot)
    {
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\BoolExpression::class)
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
     *
     * @return \Magento\Framework\Search\Request\Filter\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     *
     * @internal param $filter
     */
    private function createBoolFilter(array $must, array $should, array $mustNot)
    {
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\BoolExpression::class)
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
     *
     * @return \Magento\Framework\Search\Request\Filter\Range|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRangeFilter($field)
    {
        $filter = $this->createFilterMock(
            \Magento\Framework\Search\Request\Filter\Range::class,
            FilterInterface::TYPE_RANGE,
            $field
        );
        return $filter;
    }

    /**
     * @param string $field
     *
     * @return \Magento\Framework\Search\Request\Filter\Term|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTermFilter($field)
    {
        $filter = $this->createFilterMock(
            \Magento\Framework\Search\Request\Filter\Term::class,
            FilterInterface::TYPE_TERM,
            $field
        );
        return $filter;
    }

    /**
     * @param string $class
     * @param string $type
     * @param string $field
     *
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
}
