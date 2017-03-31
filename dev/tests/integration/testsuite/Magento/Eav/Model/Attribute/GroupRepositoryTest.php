<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute;

use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\TestFramework\Helper\Bootstrap;

class GroupRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeGroupRepositoryInterface
     */
    private $repository;

    protected function setUp()
    {
        $this->repository = Bootstrap::getObjectManager()->create(AttributeGroupRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_group_for_search.php
     */
    public function testGetList()
    {
        /** @var Set $attributeSet */
        $attributeSet = Bootstrap::getObjectManager()->create(Set::class)
            ->load('attribute_set_1_for_search', 'attribute_set_name');

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        $filter1 = $filterBuilder->setField('attribute_set_id')
            ->setValue($attributeSet->getId())
            ->create();
        $filter2 = $filterBuilder->setField('default_id')
            ->setValue(0)
            ->setConditionType('eq')
            ->create();
        $filter3 = $filterBuilder->setField('sort_order')
            ->setValue(10)
            ->setConditionType('gteq')
            ->create();
        $filter4 = $filterBuilder->setField('sort_order')
            ->setValue(30)
            ->setConditionType('lteq')
            ->create();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);

        $searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $searchCriteriaBuilder->addFilters([$filter3, $filter4]);

        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField('attribute_group_code')
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteriaBuilder->setPageSize(1);
        $searchCriteriaBuilder->setCurrentPage(1);

        $searchCriteria = $searchCriteriaBuilder->create();

        $searchResult = $this->repository->getList($searchCriteria);

        $this->assertEquals(2, $searchResult->getTotalCount());

        $items = array_values($searchResult->getItems());
        $this->assertEquals(1, count($items));
        $this->assertEquals('attribute_group_3_for_search', $items[0]['attribute_group_code']);
    }
}
