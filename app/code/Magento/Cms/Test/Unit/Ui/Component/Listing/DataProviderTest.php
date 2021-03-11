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

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Authorization|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting|\PHPUnit\Framework\MockObject\MockObject
     */
    private $reportingMock;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestInterfaceMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
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
            ->getMockForAbstractClass();

        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManagerMock */
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
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
                            ],
                            'componentType' => \Magento\Ui\Component\Container::NAME
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
