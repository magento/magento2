<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Ui\Component\DataProvider;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    const TEST_REQUEST_NAME = 'test_request_name';

    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var Reporting|MockObject
     */
    protected $reporting;

    /**
     * @var SearchCriteriaInterface|MockObject
     */
    protected $searchCriteria;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilder;

    /**
     * @var AttributeRepository|MockObject
     */
    protected $attributeRepository;

    protected function setUp(): void
    {
        $this->reporting = $this->getMockBuilder(
            Reporting::class
        )->disableOriginalConstructor()
            ->getMock();

        $searchCriteriaBuilder = $this->mockSearchCriteria();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeRepository = $this->getMockBuilder(
            AttributeRepository::class
        )->disableOriginalConstructor()
            ->getMock();

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

        $attributeMock = $this->getMockBuilder(AttributeInterface::class)
            ->getMockForAbstractClass();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->once())
            ->method('getValue')
            ->willReturn('opt1_value');

        $searchDocumentMock = $this->getMockBuilder(DocumentInterface::class)
            ->getMockForAbstractClass();
        $searchDocumentMock->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([$attributeMock]);

        $searchResultMock = $this->getMockBuilder(SearchResultInterface::class)
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
     * @return MockObject
     */
    protected function mockSearchCriteria()
    {
        $this->searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();

        $searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteria);

        return $searchCriteriaBuilder;
    }
}
