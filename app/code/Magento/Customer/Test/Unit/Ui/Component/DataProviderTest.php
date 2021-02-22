<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Ui\Component\DataProvider;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    const TEST_REQUEST_NAME = 'test_request_name';

    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var Reporting | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $reporting;

    /**
     * @var SearchCriteriaInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Framework\App\RequestInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var FilterBuilder | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterBuilder;

    /**
     * @var AttributeRepository | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeRepository;

    protected function setUp(): void
    {
        $this->reporting = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting::class
        )->disableOriginalConstructor()->getMock();

        $searchCriteriaBuilder = $this->mockSearchCriteria();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();

        $this->filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeRepository = $this->getMockBuilder(
            \Magento\Customer\Ui\Component\Listing\AttributeRepository::class
        )->disableOriginalConstructor()->getMock();

        $this->model = new DataProvider(
            self::TEST_REQUEST_NAME,
            '',
            '',
            $this->reporting,
            $searchCriteriaBuilder,
            $this->request,
            $this->filterBuilder,
            $this->attributeRepository
        );
    }

    public function testGetData()
    {
        $attributeCode = 'attribute_code';
        $attributeValue = [
            AttributeMetadataInterface::OPTIONS => [
                [
                    'label' => 'opt1_label',
                    'value' => 'opt1_value',
                ],
            ],
        ];

        $expected = [
            [
                'attribute_code' => ['opt1_value'],
            ],
        ];

        $attributeMock = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->getMockForAbstractClass();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->once())
            ->method('getValue')
            ->willReturn('opt1_value');

        $searchDocumentMock = $this->getMockBuilder(\Magento\Framework\Api\Search\DocumentInterface::class)
            ->getMockForAbstractClass();
        $searchDocumentMock->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([$attributeMock]);

        $searchResultMock = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultInterface::class)
            ->getMockForAbstractClass();
        $searchResultMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(1);
        $searchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$searchDocumentMock]);

        $this->searchCriteria->expects($this->once())
            ->method('setRequestName')
            ->with(self::TEST_REQUEST_NAME)
            ->willReturnSelf();

        $this->reporting->expects($this->once())
            ->method('search')
            ->with($this->searchCriteria)
            ->willReturn($searchResultMock);

        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn([$attributeCode => $attributeValue]);

        $result = $this->model->getData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('totalRecords', $result);
        $this->assertEquals(1, $result['totalRecords']);
        $this->assertArrayHasKey('items', $result);
        $this->assertIsArray($result['items']);
        $this->assertEquals($result['items'], $expected);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockSearchCriteria()
    {
        $this->searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaInterface::class)
            ->getMockForAbstractClass();

        $searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteria);

        return $searchCriteriaBuilder;
    }
}
