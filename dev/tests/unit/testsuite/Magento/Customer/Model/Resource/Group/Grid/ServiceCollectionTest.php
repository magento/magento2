<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model\Resource\Group\Grid;

use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Unit test for \Magento\Customer\Model\Resource\Group\Grid\ServiceCollection
 */
class ServiceCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\Service\V1\Data\FilterBuilder */
    protected $filterBuilder;

    /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var \Magento\Framework\Service\V1\Data\SortOrderBuilder */
    protected $sortOrderBuilder;

    /** @var \Magento\Customer\Service\V1\Data\SearchResults */
    protected $searchResults;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Service\V1\CustomerGroupServiceInterface */
    protected $groupServiceMock;

    /** @var ServiceCollection */
    protected $serviceCollection;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->filterBuilder = $this->objectManager->getObject('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $filterGroupBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder');
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchBuilder */
        $this->searchCriteriaBuilder = $this->objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['filterGroupBuilder' => $filterGroupBuilder]
        );
        $this->sortOrderBuilder = $this->objectManager->getObject(
            '\Magento\Framework\Service\V1\Data\SortOrderBuilder'
        );
        $this->groupServiceMock = $this->getMockBuilder('\Magento\Customer\Service\V1\CustomerGroupServiceInterface')
            ->getMock();
        $this->searchResults = $this->objectManager->getObject('Magento\Customer\Service\V1\Data\SearchResultsBuilder')
            ->create();

        $this->serviceCollection = $this->objectManager
            ->getObject(
                'Magento\Customer\Model\Resource\Group\Grid\ServiceCollection',
                [
                    'filterBuilder' => $this->filterBuilder,
                    'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                    'groupService' => $this->groupServiceMock,
                ]
            );
    }

    public function testGetSearchCriteriaImplicitEq()
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField('name')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        /** @var SearchCriteria $expectedSearchCriteria */
        $expectedSearchCriteria = $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(0)
            ->addSortOrder($sortOrder)
            ->addFilter([$this->filterBuilder->setField('name')->setConditionType('eq')
                    ->setValue('Magento')->create()])
            ->create();

        // Verifies that the search criteria Data Object created by the serviceCollection matches expected
        $this->groupServiceMock->expects($this->once())
            ->method('searchGroups')
            ->with($this->equalTo($expectedSearchCriteria))
            ->will($this->returnValue($this->searchResults));

        // Now call service collection to load the data.  This causes it to create the search criteria Data Object
        $this->serviceCollection->addFieldToFilter('name', 'Magento');
        $this->serviceCollection->setOrder('name', ServiceCollection::SORT_ORDER_ASC);
        $this->serviceCollection->loadData();
    }

    public function testGetSearchCriteriaOneField()
    {
        $field = 'age';
        $conditionType = 'gt';
        $value = '35';
        $sortOrder = $this->sortOrderBuilder
            ->setField('name')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        /** @var SearchCriteria $expectedSearchCriteria */
        $filter = $this->filterBuilder->setField($field)->setConditionType($conditionType)->setValue($value)->create();
        $expectedSearchCriteria = $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(0)
            ->addSortOrder($sortOrder)
            ->addFilter([$filter])
            ->create();

        // Verifies that the search criteria Data Object created by the serviceCollection matches expected
        $this->groupServiceMock->expects($this->once())
            ->method('searchGroups')
            ->with($this->equalTo($expectedSearchCriteria))
            ->will($this->returnValue($this->searchResults));

        // Now call service collection to load the data.  This causes it to create the search criteria Data Object
        $this->serviceCollection->addFieldToFilter($field, [$conditionType => $value]);
        $this->serviceCollection->setOrder('name', ServiceCollection::SORT_ORDER_ASC);
        $this->serviceCollection->loadData();
    }

    public function testGetSearchCriteriaOr()
    {
        // Test ((A == 1) or (B == 1 ))
        $fieldA = 'A';
        $fieldB = 'B';
        $value = 1;

        $sortOrder = $this->sortOrderBuilder
            ->setField('name')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        /** @var SearchCriteria $expectedSearchCriteria */
        $expectedSearchCriteria = $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(0)
            ->addSortOrder($sortOrder)
            ->addFilter(
                [
                    $this->filterBuilder->setField($fieldA)->setConditionType('eq')->setValue($value)->create(),
                    $this->filterBuilder->setField($fieldB)->setConditionType('eq')->setValue($value)->create(),
                ]
            )
            ->create();

        // Verifies that the search criteria Data Object created by the serviceCollection matches expected
        $this->groupServiceMock->expects($this->once())
            ->method('searchGroups')
            ->with($this->equalTo($expectedSearchCriteria))
            ->will($this->returnValue($this->searchResults));

        // Now call service collection to load the data.  This causes it to create the search criteria Data Object
        $this->serviceCollection->addFieldToFilter([$fieldA, $fieldB], [$value, $value]);
        $this->serviceCollection->setOrder('name', ServiceCollection::SORT_ORDER_ASC);
        $this->serviceCollection->loadData();
    }

    public function testGetSearchCriteriaAnd()
    {
        // Test ((A > 1) and (B > 1))
        $fieldA = 'A';
        $fieldB = 'B';
        $value = 1;

        $sortOrder = $this->sortOrderBuilder
            ->setField('name')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        /** @var SearchCriteria $expectedSearchCriteria */
        $expectedSearchCriteria = $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(0)
            ->addSortOrder($sortOrder)
            ->addFilter(
                [
                    $this->filterBuilder->setField($fieldA)->setConditionType('gt')
                        ->setValue($value)->create()
                ]
            )
            ->addFilter(
                [
                    $this->filterBuilder->setField($fieldB)->setConditionType('gt')
                        ->setValue($value)->create()
                ]
            )
            ->create();

        // Verifies that the search criteria Data Object created by the serviceCollection matches expected
        $this->groupServiceMock->expects($this->once())
            ->method('searchGroups')
            ->with($this->equalTo($expectedSearchCriteria))
            ->will($this->returnValue($this->searchResults));

        // Now call service collection to load the data.  This causes it to create the search criteria Data Object
        $this->serviceCollection->addFieldToFilter($fieldA, ['gt' => $value]);
        $this->serviceCollection->addFieldToFilter($fieldB, ['gt' => $value]);
        $this->serviceCollection->setOrder('name', ServiceCollection::SORT_ORDER_ASC);
        $this->serviceCollection->loadData();
    }

    /**
     * @param string[] $fields
     * @param array $conditions
     *
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage When passing in a field array there must be a matching condition array
     * @dataProvider addFieldToFilterInconsistentArraysDataProvider
     */
    public function testAddFieldToFilterInconsistentArrays($fields, $conditions)
    {
        $this->serviceCollection->addFieldToFilter($fields, $conditions);
    }

    public function addFieldToFilterInconsistentArraysDataProvider()
    {
        return [
            'missingCondition' => [
                ['fieldA', 'missingCondition'],
                [['eq' => 'A']]
            ],
            'missingField' => [
                ['fieldA'],
                [['eq' => 'A'], ['eq' => 'B']]
            ],
        ];
    }
}
