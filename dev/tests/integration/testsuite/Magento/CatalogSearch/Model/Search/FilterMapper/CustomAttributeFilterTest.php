<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\Search\Request\Filter\Term;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\DB\Select;

class CustomAttributeFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var ResourceConnection */
    private $resource;

    /** @var  */
    private $customAttributeFilter;

    /** @var EavConfig|\PHPUnit\Framework\MockObject\MockObject */
    private $eavConfigMock;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ConditionManager */
    private $conditionManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->create(ResourceConnection::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->conditionManager = $this->objectManager->create(ConditionManager::class);

        $this->eavConfigMock = $this->getMockBuilder(EavConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customAttributeFilter = $this->objectManager->create(
            CustomAttributeFilter::class,
            [
                'eavConfig' => $this->eavConfigMock
            ]
        );
    }

    public function testApplyWithoutFilters()
    {
        $select = $this->resource->getConnection()->select();
        $filters = [];

        $resultSelect = $this->customAttributeFilter->apply($select, ...$filters);

        $this->assertEquals(
            (string) $select,
            (string) $resultSelect,
            'Select queries must be the same in case when we have no filters to apply.'
        );
    }

    public function testApplyWithWrongAttributeFilter()
    {
        $this->expectExceptionMessage("Invalid attribute id for field: field1");
        $this->expectException(\InvalidArgumentException::class);
        $select = $this->resource->getConnection()->select();
        $filters = $this->mockFilters();
        $firstFilter = reset($filters);

        $this->eavConfigMock
            ->method('getAttribute')
            ->with(Product::ENTITY, $firstFilter->getField())
            ->willReturn(null);

        $this->customAttributeFilter->apply($select, ...$filters);
    }

    public function testApplyByOneFilter()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $filters = $this->mockFilters();
        $firstFilter = reset($filters);

        $attributes = $this->mockAttributes();
        $firstAttribute = reset($attributes);

        $this->eavConfigMock
            ->method('getAttribute')
            ->with(Product::ENTITY, $firstFilter->getField())
            ->willReturn($firstAttribute);

        $resultSelect = $this->customAttributeFilter->apply($select, ...[$firstFilter]);

        $expectedSelect = $this->getSqlForOneAttributeSearch();

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same in case when we have one filter to apply.'
        );
    }

    public function testApplyByTwoFilters()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $filters = $this->mockFilters();
        $attributes = $this->mockAttributes();

        $this->eavConfigMock
            ->method('getAttribute')
            ->withConsecutive(
                [Product::ENTITY, $filters[0]->getField()],
                [Product::ENTITY, $filters[1]->getField()]
            )->will(
                $this->onConsecutiveCalls(...$attributes)
            );

        $resultSelect = $this->customAttributeFilter->apply($select, ...$filters);

        $expectedSelect = $this->getSqlForTwoAttributeSearch();

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same in case when we have two filters to apply.'
        );
    }

    private function getSqlForOneAttributeSearch()
    {
        $filters = $this->mockFilters();
        $firstFilter = reset($filters);
        $attributes = $this->mockAttributes();
        $firstAttribute = reset($attributes);

        $joinConditions = [
            '`some_index`.`entity_id` = `field1_filter`.`entity_id`',
            sprintf('`field1_filter`.`attribute_id` = %s', $firstAttribute->getId()),
            sprintf('`field1_filter`.`store_id` = %s', (int) $this->storeManager->getStore()->getId())
        ];

        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        )->joinInner(
            ['field1_filter' => $this->resource->getTableName('catalog_product_index_eav')],
            $this->conditionManager->combineQueries($joinConditions, Select::SQL_AND),
            []
        )->where(sprintf('`some_index`.`attribute_id` = %s', $firstAttribute->getId()))
        ->where(sprintf("`some_index`.`value` = '%s'", $firstFilter->getValue()));

        return $select;
    }

    private function getSqlForTwoAttributeSearch()
    {
        $attributes = $this->mockAttributes();
        $firstAttribute = array_shift($attributes);
        $secondAttribute = array_shift($attributes);

        $joinConditions1 = [
            '`some_index`.`entity_id` = `field1_filter`.`entity_id`',
            sprintf('`field1_filter`.`attribute_id` = %s', $firstAttribute->getId()),
            sprintf('`field1_filter`.`store_id` = %s', (int) $this->storeManager->getStore()->getId())
        ];

        $joinConditions2 = [
            '`some_index`.`entity_id` = `field2_filter`.`entity_id`',
            sprintf('`field2_filter`.`attribute_id` = %s', $secondAttribute->getId()),
            sprintf('`field2_filter`.`store_id` = %s', (int) $this->storeManager->getStore()->getId())
        ];

        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        )->joinInner(
            ['field1_filter' => $this->resource->getTableName('catalog_product_index_eav')],
            $this->conditionManager->combineQueries($joinConditions1, Select::SQL_AND),
            []
        )->joinInner(
            ['field2_filter' => $this->resource->getTableName('catalog_product_index_eav')],
            $this->conditionManager->combineQueries($joinConditions2, Select::SQL_AND),
            []
        );

        return $select;
    }

    private function mockFilters()
    {
        $filters = [];

        $filters[] = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name2', 'value2', 'field1'])
            ->setMethods(null)
            ->getMock();

        $filters[] = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name3', 'value3', 'field2'])
            ->setMethods(null)
            ->getMock();

        return $filters;
    }

    private function mockAttributes()
    {
        $attribute1 = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $attribute1
            ->method('getId')
            ->willReturn(42);

        $attribute2 = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $attribute2
            ->method('getId')
            ->willReturn(450);

        return [$attribute1, $attribute2];
    }
}
