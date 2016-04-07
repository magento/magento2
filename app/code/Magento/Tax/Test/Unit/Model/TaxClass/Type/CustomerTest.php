<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\TaxClass\Type;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAssignedToObjects()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $searchResultsMock  = $this->getMockBuilder('Magento\Framework\Api\SearchResults')
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(['randomValue']));

        /** @var \Magento\Framework\Api\FilterBuilder $filterBuilder */
        $filterBuilder = $objectManagerHelper
            ->getObject('Magento\Framework\Api\FilterBuilder');
        /** @var \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder */
        $filterGroupBuilder = $objectManagerHelper
            ->getObject('Magento\Framework\Api\Search\FilterGroupBuilder');
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $objectManagerHelper->getObject(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            [
                'filterGroupBuilder' => $filterGroupBuilder
            ]
        );
        $expectedSearchCriteria = $searchCriteriaBuilder
            ->addFilters([$filterBuilder->setField('tax_class_id')->setValue(5)->create()])
            ->create();

        $customerGroupServiceMock = $this->getMockBuilder('Magento\Customer\Api\GroupRepositoryInterface')
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroupServiceMock->expects($this->once())
            ->method('getList')
            ->with($expectedSearchCriteria)
            ->willReturn($searchResultsMock);

        /** @var $model \Magento\Tax\Model\TaxClass\Type\Customer */
        $model = $objectManagerHelper->getObject(
            'Magento\Tax\Model\TaxClass\Type\Customer',
            [
                'customerGroupRepository' => $customerGroupServiceMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilder,
                'filterBuilder' => $filterBuilder,
                'data' => ['id' => 5]
            ]
        );

        $this->assertTrue($model->isAssignedToObjects());
    }
}
