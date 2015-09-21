<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Ui\Component\DataProvider;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Ui\Component\Listing\AttributeRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeRepository;

    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reporting;

    /** @var \Magento\Customer\Ui\Component\DataProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataProvide;

    public function setUp()
    {
        $this->reporting = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\DataProvider\Reporting')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder('Magento\Framework\Api\Search\SearchCriteriaBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $filterBuilder = $this->getMockBuilder('Magento\Framework\Api\FilterBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeRepository = $this->getMockBuilder('Magento\Customer\Ui\Component\Listing\AttributeRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataProvide = new DataProvider(
            '',
            '',
            '',
            $this->reporting,
            $this->searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $this->attributeRepository
        );
    }

    public function testGetData()
    {
        $document = $this->getMockBuilder('Magento\Framework\Api\Search\DocumentInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockBuilder('Magento\Framework\Api\AttributeInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria = $this->getMockBuilder('Magento\Framework\Api\Search\SearchCriteria')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResult = $this->getMockBuilder('Magento\Framework\Api\Search\SearchResultInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->reporting->expects($this->once())
            ->method('search')
            ->with($searchCriteria)
            ->willReturn($searchResult);
        $searchResult->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(1);
        $searchResult->expects($this->once())
            ->method('getItems')
            ->willReturn([$document]);
        $document->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([$attribute]);
        $attribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('attribute-code');
        $attribute->expects($this->once())
            ->method('getValue')
            ->willReturn('12,13,14');
        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn([
                'attribute-code' => [
                    'frontend_input' => 'input',
                    'visible' => true
                ]
            ]);
        $this->assertEquals(
            [
                'totalRecords' => 1,
                'items' => [
                    [
                        'attribute-code' => [12, 13, 14]
                    ]
                ]
            ],
            $this->dataProvide->getData()
        );
    }
}
