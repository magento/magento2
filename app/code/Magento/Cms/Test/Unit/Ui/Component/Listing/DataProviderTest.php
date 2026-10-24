<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Ui\Component\Listing;

use Magento\Cms\Ui\Component\DataProvider;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Ui\Component\Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Cms\Api\Data\PageInterface;

class DataProviderTest extends TestCase
{
    /**
     * @var AuthorizationInterface|MockObject
     */
    private AuthorizationInterface|MockObject $authorizationMock;

    /**
     * @var Reporting|MockObject
     */
    private Reporting|MockObject $reportingMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private SearchCriteriaBuilder|MockObject $searchCriteriaBuilderMock;

    /**
     * @var RequestInterface|MockObject
     */
    private RequestInterface|MockObject $requestInterfaceMock;

    /**
     * @var FilterBuilder|MockObject
     */
    private FilterBuilder|MockObject $filterBuilderMock;

    /**
     * @var DataProvider
     */
    private DataProvider $dataProvider;

    /**
     * @var string
     */
    private string $name = 'cms_page_listing_data_source';

    /**
     * @var string
     */
    private string $primaryFieldName = 'page';

    /**
     * @var string
     */
    private string $requestFieldName = 'id';

    /**
     * @var array
     */
    private array $pageLayoutColumns = [
        PageInterface::PAGE_LAYOUT,
        PageInterface::CUSTOM_THEME,
        PageInterface::CUSTOM_THEME_FROM,
        PageInterface::CUSTOM_THEME_TO,
        PageInterface::CUSTOM_ROOT_TEMPLATE
    ];

    protected function setUp(): void
    {
        $this->authorizationMock = $this->createMock(AuthorizationInterface::class);

        $this->reportingMock = $this->createMock(Reporting::class);

        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->requestInterfaceMock = $this->createMock(RequestInterface::class);

        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);

        /** @var ObjectManagerInterface|MockObject $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->method('get')
            ->with(AuthorizationInterface::class)
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
    public function testPrepareMetadata(): void
    {
        $this->authorizationMock->expects($this->exactly(2))
            ->method('isAllowed')
            ->willReturnMap(
                [
                    ['Magento_Cms::save', null, false],
                    ['Magento_Cms::save_design', null, false],
                ]
            );

        $metadata = [
            'cms_page_columns' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'editorConfig' => [
                                'enabled' => false
                            ],
                            'componentType' => Container::NAME
                        ]
                    ]
                ]
            ]
        ];

        foreach ($this->pageLayoutColumns as $column) {
            $metadata['cms_page_columns']['children'][$column] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'editor' => [
                                'editorType' => false
                            ],
                            'componentType' => Container::NAME
                        ]
                    ]
                ]
            ];
        }

        $this->assertEquals(
            $metadata,
            $this->dataProvider->prepareMetadata()
        );
    }

    /**
     * @covers \Magento\Cms\Ui\Component\DataProvider::prepareMetadata
     */
    public function testPrepareMetadataForCmsBlockListing(): void
    {
        $name = 'cms_block_listing_data_source';

        $this->dataProvider = new DataProvider(
            $name,
            $this->primaryFieldName,
            $this->requestFieldName,
            $this->reportingMock,
            $this->searchCriteriaBuilderMock,
            $this->requestInterfaceMock,
            $this->filterBuilderMock
        );

        $this->assertEquals([], $this->dataProvider->prepareMetadata());
    }
}
