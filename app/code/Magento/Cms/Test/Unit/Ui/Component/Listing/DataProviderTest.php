<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Ui\Component\Listing;

use Magento\Cms\Ui\Component\DataProvider;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Authorization;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Authorization|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reportingMock;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestInterfaceMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var \Magento\Cms\Ui\Component\DataProvider
     */
    private $dataProvider;

    /**
     * @var string
     */
    private $name = 'cms_page_listing_data_source';

    /**
     * @var string
     */
    private $primaryFieldName = 'page';

    /**
     * @var string
     */
    private $requestFieldName = 'id';

    public function setUp()
    {
        $this->authorizationMock = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportingMock = $this->getMockBuilder(Reporting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterfaceMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManagerMock */
        $objectManagerMock = $this->getMock(ObjectManagerInterface::class);
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

    /**
     * @covers \Magento\Cms\Ui\Component\DataProvider::prepareMetadata
     */
    public function testPrepareMetadata()
    {
        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Cms::save')
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
