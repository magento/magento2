<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\TaxClass\Type;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAssignedToObjects()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $searchResultsMock  = $this->getMockBuilder(\Magento\Framework\Api\SearchResults::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(['randomValue']));

        /** @var \Magento\Framework\Api\FilterBuilder $filterBuilder */
        $filterBuilder = $objectManagerHelper
            ->getObject(\Magento\Framework\Api\FilterBuilder::class);
        /** @var \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder */
        $filterGroupBuilder = $objectManagerHelper
            ->getObject(\Magento\Framework\Api\Search\FilterGroupBuilder::class);
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $objectManagerHelper->getObject(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            [
                'filterGroupBuilder' => $filterGroupBuilder
            ]
        );
        $expectedSearchCriteria = $searchCriteriaBuilder
            ->addFilters([$filterBuilder->setField('tax_class_id')->setValue(5)->create()])
            ->create();

        $customerGroupServiceMock = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroupServiceMock->expects($this->once())
            ->method('getList')
            ->with($expectedSearchCriteria)
            ->willReturn($searchResultsMock);

        /** @var $model \Magento\Tax\Model\TaxClass\Type\Customer */
        $model = $objectManagerHelper->getObject(
            \Magento\Tax\Model\TaxClass\Type\Customer::class,
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
