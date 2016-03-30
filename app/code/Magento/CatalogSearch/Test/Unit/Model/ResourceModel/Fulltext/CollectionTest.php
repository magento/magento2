<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Fulltext;

use Magento\CatalogSearch\Test\Unit\Model\ResourceModel\BaseCollectionTest;

class CollectionTest extends BaseCollectionTest
{
    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $model;

    /**
     * @var \Magento\Framework\Api\Filter
     */
    private $filter;

    /**
     * setUp method for CollectionTest
     */
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $storeManager = $this->getStoreManager();
        $universalFactory = $this->getUniversalFactory();
        $scopeConfig = $this->getScopeConfig();
        $criteriaBuilder = $this->getCriteriaBuilder();
        $filterBuilder = $this->getFilterBuilder();

        $this->prepareObjectManager([
            [
                'Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation',
                $this->getMock('Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation')
            ],
        ]);

        $this->model = $helper->getObject(
            'Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection',
            [
                'storeManager' => $storeManager,
                'universalFactory' => $universalFactory,
                'scopeConfig' => $scopeConfig,
            ]
        );

        $search = $this->getMockForAbstractClass('\Magento\Search\Api\SearchInterface');
        $this->model->setSearchCriteriaBuilder($criteriaBuilder);
        $this->model->setSearch($search);
        $this->model->setFilterBuilder($filterBuilder);

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 333
     * @expectedExceptionMessage setRequestName
     */
    public function testGetFacetedData()
    {
        $this->model->getFacetedData('field');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getScopeConfig()
    {
        $scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn(1);

        return $scopeConfig;
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
        $this->filter = new \Magento\Framework\Api\Filter();
        $this->filter->setField('price_dynamic_algorithm');
        $this->filter->setValue(1);
        $criteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with($this->filter);
        $criteria = $this->getMock('Magento\Framework\Api\Search\SearchCriteria', [], [], '', false);
        $criteriaBuilder->expects($this->once())->method('create')->willReturn($criteria);
        $criteria->expects($this->once())
            ->method('setRequestName')
            ->withConsecutive(['catalog_view_container'])
            ->willThrowException(new \Exception('setRequestName', 333));

        return $criteriaBuilder;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFilterBuilder()
    {
        $filterBuilder = $this->getMock('Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $filterBuilder->expects($this->once())->method('setField')->with('price_dynamic_algorithm');
        $filterBuilder->expects($this->once())->method('setValue')->with(1);
        $filterBuilder->expects($this->once())->method('create')->willReturn($this->filter);
        return $filterBuilder;
    }

    /**
     * @param $map
     */
    private function prepareObjectManager($map)
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())->method('getInstance')->willReturnSelf();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($map));
        $reflectionClass = new \ReflectionClass('Magento\Framework\App\ObjectManager');
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock);
    }
}
