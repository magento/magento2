<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Ui\Component\Listing;


use Magento\Cms\Ui\Component\DataProvider;
use Magento\Framework\App\ObjectManager;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    private $authorizationMock;
    private $reportingMock;
    private $searchCriteriaBuilderMock;
    private $requestInterfaceMock;
    private $filterBuilderMock;
    private $name = 'cms_page_listing_data_source';
    private $primaryFieldName = 'page';
    private $requestFieldName = 'id';

    public function setUp()
    {
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\Authorization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportingMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\DataProvider\Reporting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterfaceMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($this->authorizationMock);
        ObjectManager::setInstance($objectManagerMock);

        $this->dataProvider = new DataProvider(
            $this->name,
            $this->primaryFieldName,
            $this->requestFieldName,
            $this->reportingMock,
            $this->searchCriteriaBuilderMock,
            $this->requestInterfaceMock,
            $this->filterBuilderMock
        );
    }

    public function testPrepareMetadata()
    {
        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with(DataProvider::ADMIN_RESOURCE)
            ->willReturn(false);

        $metadata = [
            'cms_page_columns' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'editorConfig' => [
                                'enabled' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals(
            $metadata,
            $this->dataProvider->prepareMetadata()
        );

    }
}
