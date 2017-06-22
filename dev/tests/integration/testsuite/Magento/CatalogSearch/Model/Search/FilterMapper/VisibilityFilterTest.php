<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\FilterMapper\VisibilityFilter;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\FrontendResource;
use Magento\CatalogSearch\Model\Search\FilterMapper\StockStatusFilter;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;

class VisibilityFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var ResourceConnection */
    private $resource;

    /** @var ConditionManager */
    private $conditionManager;

    /** @var FrontendResource */
    private $indexerEavFrontendResource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var EavConfig|\PHPUnit_Framework_MockObject_MockObject */
    private $eavConfigMock;

    /** @var VisibilityFilter */
    private $visibilityFilter;

    /** @var int */
    private $answerToLifeTheUniverseAndEverything = 42;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->create(ResourceConnection::class);
        $this->conditionManager = $this->objectManager->create(ConditionManager::class);
        $this->indexerEavFrontendResource = $this->objectManager->create(FrontendResource::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);

        $this->eavConfigMock = $this->getMockBuilder(EavConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->visibilityFilter = $this->objectManager->create(
            VisibilityFilter::class,
            [
                'eavConfig' => $this->eavConfigMock
            ]
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid filter type: Luke I am your father!
     */
    public function testApplyWithWrongType()
    {
        $select = $this->resource->getConnection()->select();
        $filter = $this->mockFilter();

        $this->visibilityFilter->apply($select, $filter, 'Luke I am your father!');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Wrong id for visibility attribute
     */
    public function testApplyWithWrongAttributeCode()
    {
        $select = $this->resource->getConnection()->select();
        $filter = $this->mockFilter();

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $this->visibilityFilter->apply($select, $filter, VisibilityFilter::FILTER_BY_WHERE);
    }

    public function testApplyFilterAsWhere()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $filter = $this->mockFilter();
        $attribute = $this->mockAttribute();

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn($attribute);

        $resultSelect = $this->visibilityFilter->apply($select, $filter, VisibilityFilter::FILTER_BY_WHERE);
        $expectedSelect = $this->getExpectedSelectForWhereFilter();

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    public function testApplyFilterAsJoin()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $filter = $this->mockFilter();
        $attribute = $this->mockAttribute();

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn($attribute);

        $resultSelect = $this->visibilityFilter->apply($select, $filter, VisibilityFilter::FILTER_BY_JOIN);
        $expectedSelect = $this->getExpectedSelectForJoinFilter();

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    private function getExpectedSelectForWhereFilter()
    {
        $filter = $this->mockFilter();
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        )->where(
            $this->conditionManager->combineQueries(
                [
                    $this->conditionManager->generateCondition(
                        'some_index.attribute_id',
                        '=',
                        $this->answerToLifeTheUniverseAndEverything
                    ),
                    $this->conditionManager->generateCondition(
                        'some_index.value',
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),
                    $this->conditionManager->generateCondition(
                        'some_index.store_id',
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],
                Select::SQL_AND
            )
        );

        return $select;
    }

    private function getExpectedSelectForJoinFilter()
    {
        $filter = $this->mockFilter();
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        )->joinInner(
            ['visibility_filter' => $this->indexerEavFrontendResource->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    'some_index.entity_id = visibility_filter.entity_id',
                    $this->conditionManager->generateCondition(
                        'visibility_filter.attribute_id',
                        '=',
                        $this->answerToLifeTheUniverseAndEverything
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.value',
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.store_id',
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );

        return $select;
    }

    private function mockFilter()
    {
        return $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name2', [42, 450], 'visibility'])
            ->setMethods(null)
            ->getMock();
    }

    private function mockAttribute()
    {
        $attribute  = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $attribute
            ->method('getId')
            ->willReturn($this->answerToLifeTheUniverseAndEverything);

        return $attribute;
    }
}
