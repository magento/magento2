<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\MassAction;

use Magento\Framework\Api\Filter as ApiFilter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as ResourceAbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Ui component massaction filter tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $uiComponentFactoryMock;

    /**
     * @var MockObject
     */
    private $filterBuilderMock;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $dataProviderMock;

    /**
     * @var MockObject
     */
    private $abstractDbMock;

    /**
     * @var MockObject
     */
    private $searchResultMock;

    /**
     * @var MockObject
     */
    private $uiComponentMock;

    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $resourceAbstractDbMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->addMethods(['value'])
            ->onlyMethods(['setConditionType', 'create', 'setField'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->dataProviderMock = $this->getMockForAbstractClass(DataProviderInterface::class);
        $this->uiComponentMock = $this->getMockForAbstractClass(UiComponentInterface::class);
        $this->abstractDbMock = $this->createPartialMock(
            AbstractDb::class,
            ['getResource', 'addFieldToFilter']
        );
        $this->resourceAbstractDbMock = $this->createMock(ResourceAbstractDb::class);
        $this->contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $this->searchResultMock = $this->getMockForAbstractClass(SearchResultInterface::class);
        $uiComponentMockTwo = $this->getMockForAbstractClass(UiComponentInterface::class);
        $this->filter = $this->objectManager->getObject(
            Filter::class,
            [
                'factory' => $this->uiComponentFactoryMock,
                'request' => $this->requestMock,
                'filterBuilder' => $this->filterBuilderMock
            ]
        );
        $this->uiComponentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->uiComponentMock);
        $this->uiComponentMock->expects($this->any())
            ->method('getChildComponents')
            ->willReturn([$uiComponentMockTwo]);
        $uiComponentMockTwo->expects($this->any())
            ->method('getChildComponents')
            ->willReturn([]);
        $this->uiComponentMock->expects($this->any())
            ->method('getContext')
            ->willReturn($this->contextMock);
    }

    /**
     * Run test for applySelectionOnTargetProvider method
     *
     * @param int[]|bool $selectedIds
     * @param int[]|bool $excludedIds
     * @param int $filterExpected
     * @param string $conditionExpected
     *
     * @return void
     * @throws LocalizedException
     * @dataProvider applySelectionOnTargetProviderDataProvider
     */
    public function testApplySelectionOnTargetProvider(
        $selectedIds,
        $excludedIds,
        $filterExpected,
        $conditionExpected
    ): void {
        $this->setUpApplySelection($filterExpected, $conditionExpected);
        $this->requestMock
            ->method('getParam')
            ->withConsecutive([Filter::SELECTED_PARAM], [Filter::EXCLUDED_PARAM])
            ->willReturnOnConsecutiveCalls($selectedIds, $excludedIds);
        $this->filter->applySelectionOnTargetProvider();
    }

    /**
     * Data provider for testApplySelectionOnTargetProvider.
     *
     * @return array
     */
    public function applySelectionOnTargetProviderDataProvider(): array
    {
        return [
            [[1, 2, 3], 'false', 0, 'in'],
            [[1, 2, 3], [1, 2, 3], 1, 'nin'],
            ['false', [1, 2, 3], 1, 'nin'],
            ['false', 'false', 0, '']
        ];
    }

    /**
     * @return void
     */
    public function testApplySelectionOnTargetProviderException(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->dataProviderMock->expects($this->any())
            ->method('setLimit');
        $this->dataProviderMock->expects($this->any())
            ->method('getSearchResult')
            ->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->any())
            ->method('getItems')
            ->willReturn([]);

        $filterMock = $this->createMock(ApiFilter::class);
        $this->filterBuilderMock->expects($this->any())
            ->method('setConditionType')
            ->willReturn($this->filterBuilderMock);
        $this->filterBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($filterMock);
        $this->filterBuilderMock->expects($this->any())
            ->method('setField')
            ->willReturn($this->filterBuilderMock);
        $this->requestMock
            ->method('getParam')
            ->withConsecutive([Filter::SELECTED_PARAM], [Filter::EXCLUDED_PARAM])
            ->willReturnOnConsecutiveCalls([1], []);
        $this->dataProviderMock->expects($this->any())
            ->method('addFilter')
            ->with($filterMock)
            ->willThrowException(new \Exception('exception'));
        $this->filter->applySelectionOnTargetProvider();
    }

    /**
     * Run test for getCollection method with SearchResultInterface.
     *
     * @param int[]|bool $selectedIds
     * @param int[]|bool $excludedIds
     * @param int $filterExpected
     * @param string $conditionExpected
     *
     * @return void
     * @dataProvider applySelectionOnTargetProviderDataProvider
     */
    public function testGetCollection($selectedIds, $excludedIds, $filterExpected, $conditionExpected): void
    {
        $this->setUpApplySelection($filterExpected, $conditionExpected);
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(
                [Filter::SELECTED_PARAM],
                [Filter::EXCLUDED_PARAM],
                [Filter::SELECTED_PARAM],
                [Filter::EXCLUDED_PARAM],
                ['namespace']
            )
            ->willReturnOnConsecutiveCalls($selectedIds, $excludedIds, $selectedIds, $excludedIds, '');
        $this->abstractDbMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->resourceAbstractDbMock);
        $this->abstractDbMock->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->assertEquals($this->abstractDbMock, $this->filter->getCollection($this->abstractDbMock));
    }

    /**
     * Run test for getCollection method with collection
     *
     * @param int[]|bool $selectedIds
     * @param int[]|bool $excludedIds
     * @param int $filterExpected
     * @param string $conditionExpected
     *
     * @return void
     * @dataProvider applySelectionOnTargetProviderDataProvider
     */
    public function testGetCollectionWithCollection(
        $selectedIds,
        $excludedIds,
        $filterExpected,
        $conditionExpected
    ): void {
        $this->dataProviderMock = $this->createMock(AbstractDataProvider::class);
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->dataProviderMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn([1, 2, 3]);

        $this->setUpApplySelection($filterExpected, $conditionExpected);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['namespace', null, ''],
                    [Filter::SELECTED_PARAM, null, $selectedIds],
                    [Filter::EXCLUDED_PARAM, null, $excludedIds],
                ]
            );

        $this->abstractDbMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->resourceAbstractDbMock);
        $this->abstractDbMock->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->assertEquals($this->abstractDbMock, $this->filter->getCollection($this->abstractDbMock));
    }

    /**
     * This tests the method prepareComponent().
     *
     * @return void
     */
    public function testPrepareComponent(): void
    {
        $result = $this->filter->prepareComponent($this->uiComponentMock);
        $this->assertNull($result);
    }

    /**
     * This tests the method getComponent().
     *
     * @return void
     */
    public function testGetComponent(): void
    {
        $this->requestMock
            ->method('getParam')
            ->with('namespace')
            ->willReturn('');
        $this->assertEquals($this->uiComponentMock, $this->filter->getComponent());
    }

    /**
     * This tests the method getComponentRefererUrl().
     *
     * @return void
     */
    public function testGetComponentRefererUrlIsNotNull(): void
    {
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $returnArray = [
            'referer_url' => 'referer_url'
        ];
        $this->dataProviderMock->expects($this->once())
            ->method('getConfigData')
            ->willReturn($returnArray);
        $this->assertEquals('referer_url', $this->filter->getComponentRefererUrl());
    }

    /**
     * This tests the method getComponentRefererUrl().
     *
     * @return void
     */
    public function testGetComponentRefererUrlIsNull(): void
    {
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->assertNull($this->filter->getComponentRefererUrl());
    }

    /**
     * Apply mocks for current parameters from datasource.
     *
     * @param int $filterExpected
     * @param string $conditionExpected
     *
     * @return void
     */
    private function setUpApplySelection($filterExpected, $conditionExpected): void
    {
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->dataProviderMock->expects($this->any())
            ->method('setLimit');
        $this->dataProviderMock->expects($this->any())
            ->method('getSearchResult')
            ->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->any())
            ->method('getItems')
            ->willReturn([new DataObject(['id' => 1])]);
        $filterMock = $this->createMock(ApiFilter::class);
        $this->dataProviderMock->expects($this->exactly($filterExpected))
            ->method('addFilter')
            ->with($filterMock);

        $this->filterBuilderMock->expects($this->exactly($filterExpected))
            ->method('setConditionType')
            ->with($conditionExpected)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())
            ->method('value')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($filterMock);
        $this->filterBuilderMock->expects($this->any())
            ->method('setConditionType')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())
            ->method('value')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($filterMock);
    }
}
