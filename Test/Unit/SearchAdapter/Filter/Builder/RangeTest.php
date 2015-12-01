<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;

/**
 * @see \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range
 */
class RangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Range
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Wildcard|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterInterface;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeInterface;

    protected function setUp()
    {
        $this->fieldMapper = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\FieldMapperInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeInterface = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMock();

        $this->filterInterface = $this->getMockBuilder('Magento\Framework\Search\Request\Filter\Range')
            ->disableOriginalConstructor()
            ->setMethods([
                'getField',
                'getFrom',
                'getTo',
            ])
            ->getMock();

        $this->model = new Range(
            $this->fieldMapper,
            $this->storeManager,
            $this->customerSession
        );
    }

    /**
     *  Test buildFilter method
     */
    public function testBuildFilter()
    {
        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('field');

        $this->filterInterface->expects($this->any())
            ->method('getField')
            ->willReturn('field');

        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn(1);

        $this->storeManager->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->filterInterface->expects($this->any())
            ->method('getFrom')
            ->willReturn('field');

        $this->filterInterface->expects($this->any())
            ->method('getTo')
            ->willReturn('field');

        $this->model->buildFilter($this->filterInterface);
    }

    /**
     *  Test buildFilter method with field name 'price'
     */
    public function testPriceBuildFilter()
    {
        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('price');

        $this->filterInterface->expects($this->any())
            ->method('getField')
            ->willReturn('field');

        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeInterface);

        $this->storeInterface->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->filterInterface->expects($this->any())
            ->method('getFrom')
            ->willReturn('field');

        $this->filterInterface->expects($this->any())
            ->method('getTo')
            ->willReturn('field');

        $this->model->buildFilter($this->filterInterface);
    }
}
