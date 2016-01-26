<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Advanced;

use Magento\CatalogSearch\Test\Unit\Model\ResourceModel\BaseCollectionTest;
use Magento\Framework\Api\Filter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CollectionTest extends BaseCollectionTest
{
    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
     */
    private $advancedCollection;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $temporaryStorageFactory;

    /**
     * @var \Magento\Search\Api\SearchInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $search;

    /**
     * setUp method for CollectionTest
     */
    protected function setUp()
    {
        $helper = new ObjectManagerHelper($this);

        $storeManager = $this->getStoreManager();
        $universalFactory = $this->getUniversalFactory();
        $this->criteriaBuilder = $this->getCriteriaBuilder();
        $this->filterBuilder = $this->getMock('Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->temporaryStorageFactory = $this->getMock(
            'Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory',
            [],
            [],
            '',
            false
        );
        $this->search = $this->getMock('Magento\Search\Api\SearchInterface', [], [], '', false);

        $this->advancedCollection = $helper->getObject(
            'Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection',
            [
                'storeManager' => $storeManager,
                'universalFactory' => $universalFactory,
                'searchCriteriaBuilder' => $this->criteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'temporaryStorageFactory' => $this->temporaryStorageFactory,
                'search' => $this->search,
            ]
        );
    }

    public function testLoadWithFilterNoFilters()
    {
        $this->advancedCollection->loadWithFilter();
    }

    public function testLoadWithFilterWithFilters()
    {
        $firstFilter = new Filter();
        $firstFilter->setField('attr_code_1');
        $firstFilter->setValue('attr_value_1');

        $secondFilter = new Filter();
        $secondFilter->setField('attr_code_2');
        $secondFilter->setValue('attr_value_2');

        $this->filterBuilder->expects($this->exactly(2))->method('setField');
        $this->filterBuilder->expects($this->exactly(2))->method('setValue');
        $this->filterBuilder->expects($this->at(2))->method('create')->willReturn($firstFilter);
        $this->filterBuilder->expects($this->at(5))->method('create')->willReturn($secondFilter);

        $criteria = $this->getMock('Magento\Framework\Api\Search\SearchCriteria', [], [], '', false);
        $this->criteriaBuilder->expects($this->once())->method('create')->willReturn($criteria);
        $criteria->expects($this->once())
            ->method('setRequestName')
            ->with('advanced_search_container');

        $tempTable = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false);
        $temporaryStorage = $this->getMock(
            'Magento\Framework\Search\Adapter\Mysql\TemporaryStorage',
            [],
            [],
            '',
            false
        );
        $temporaryStorage->expects($this->once())->method('storeApiDocuments')->willReturn($tempTable);
        $this->temporaryStorageFactory->expects($this->once())->method('create')->willReturn($temporaryStorage);

        $searchResult = $this->getMock('Magento\Framework\Api\Search\SearchResultInterface', [], [], '', false);
        $this->search->expects($this->once())->method('search')->willReturn($searchResult);

        $this->advancedCollection->addFieldsToFilter(
            [['attr_code_1' => 'attr_value_1'], ['attr_code_2' => 'attr_value_2']]
        )->loadWithFilter();
        $this->advancedCollection->getData();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCriteriaBuilder()
    {
        $criteriaBuilder = $this->getMockBuilder('Magento\Framework\Api\Search\SearchCriteriaBuilder')
            ->setMethods(['addFilter', 'create', 'setRequestName'])
            ->disableOriginalConstructor()
            ->getMock();
        return $criteriaBuilder;
    }
}
