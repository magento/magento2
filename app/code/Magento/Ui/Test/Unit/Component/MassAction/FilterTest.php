<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\MassAction;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Api\Filter as ApiFilter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class FilterTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactoryMock;

    /**
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /** @var \Magento\Ui\Component\MassAction\Filter */
    private $filter;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     *\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderMock;

    /**
     *\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractDbMock;

    /**
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultMock;

    /**
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentMock;

    /**
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->uiComponentFactoryMock = $this->getMock(UiComponentFactory::class, [], [], '', false);
        $this->filterBuilderMock = $this->getMock(FilterBuilder::class, [], [], '', false);
        $this->requestMock = $this->getMock(RequestInterface::class);
        $this->dataProviderMock = $this->getMock(DataProviderInterface::class);
        $this->uiComponentMock = $this->getMock(UiComponentInterface::class);
        $this->abstractDbMock = $this->getMock(AbstractDb::class, [], [], '', false);
        $this->contextMock = $this->getMock(ContextInterface::class);
        $this->searchResultMock = $this->getMock(SearchResultInterface::class);
        $uiComponentMockTwo = $this->getMock(UiComponentInterface::class);
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
     * @dataProvider applySelectionOnTargetProviderDataProvider
     */
    public function testApplySelectionOnTargetProvider($selectedIds, $excludedIds, $filterExpected, $conditionExpected)
    {
        $this->setUpApplySelection($selectedIds, $excludedIds, $filterExpected, $conditionExpected);
        $this->filter->applySelectionOnTargetProvider();
    }

    /**
     * Data provider for testApplySelectionOnTargetProvider
     */
    public function applySelectionOnTargetProviderDataProvider()
    {
        return [
            [[1, 2, 3], 'false' , 0, 'in'],
            [[1, 2, 3], [1, 2, 3] , 1, 'nin'],
            ['false', [1, 2, 3] , 1, 'nin'],
            ['false', 'false' , 0, '']
        ];
    }

    /**
     * @throws \Exception
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testApplySelectionOnTargetProviderException()
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
            ->willReturn([]);

        $filterMock = $this->getMock(ApiFilter::class, [], [], '', false);
        $this->filterBuilderMock->expects($this->any())
            ->method('setConditionType')
            ->willReturn($this->filterBuilderMock);
        $this->filterBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($filterMock);
        $this->filterBuilderMock->expects($this->any())
            ->method('setField')
            ->willReturn($this->filterBuilderMock);
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with(Filter::SELECTED_PARAM)
            ->willReturn([1]);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with(Filter::EXCLUDED_PARAM)
            ->willReturn([]);
        $this->dataProviderMock->expects($this->any())
            ->method('addFilter')
            ->with($filterMock)
            ->willThrowException(new \Exception('exception'));
        $this->filter->applySelectionOnTargetProvider();
    }

    /**
     * Run test for getCollection method with SearchResultInterface
     *
     * @param int[]|bool $selectedIds
     * @param int[]|bool $excludedIds
     * @param int $filterExpected
     * @param string $conditionExpected
     * @dataProvider applySelectionOnTargetProviderDataProvider
     */
    public function testGetCollection($selectedIds, $excludedIds, $filterExpected, $conditionExpected)
    {
        $this->setUpApplySelection($selectedIds, $excludedIds, $filterExpected, $conditionExpected);
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('namespace')
            ->willReturn('');
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with(Filter::SELECTED_PARAM)
            ->willReturn($selectedIds);
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with(Filter::EXCLUDED_PARAM)
            ->willReturn($excludedIds);
        $this->assertEquals($this->abstractDbMock, $this->filter->getCollection($this->abstractDbMock));
    }

    /**
     * Run test for getCollection method with collection
     *
     * @param int[]|bool $selectedIds
     * @param int[]|bool $excludedIds
     * @param int $filterExpected
     * @param string $conditionExpected
     * @dataProvider applySelectionOnTargetProviderDataProvider
     */
    public function testGetCollectionWithCollection($selectedIds, $excludedIds, $filterExpected, $conditionExpected)
    {
        $this->dataProviderMock = $this->getMock(
            \Magento\Ui\DataProvider\AbstractDataProvider::class,
            [],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->dataProviderMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn([1, 2, 3]);

        $this->setUpApplySelection($selectedIds, $excludedIds, $filterExpected, $conditionExpected);
        
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['namespace', null, ''],
                [Filter::SELECTED_PARAM, null, $selectedIds],
                [Filter::EXCLUDED_PARAM, null, $excludedIds],
            ]);

        $this->assertEquals($this->abstractDbMock, $this->filter->getCollection($this->abstractDbMock));
    }

    /**
     * This tests the method prepareComponent()
     */
    public function testPrepareComponent()
    {
        $this->filter->prepareComponent($this->uiComponentMock);
    }

    /**
     * This tests the method getComponent()
     */
    public function testGetComponent()
    {
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('namespace')
            ->willReturn('');
        $this->assertEquals($this->uiComponentMock, $this->filter->getComponent());
    }

    /**
     * This tests the method getComponentRefererUrl()
     */
    public function testGetComponentRefererUrlIsNotNull()
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
     * This tests the method getComponentRefererUrl()
     */
    public function testGetComponentRefererUrlIsNull()
    {
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->assertNull($this->filter->getComponentRefererUrl());
    }

    /**
     * Apply mocks for current parameters from datasource
     *
     * @param int[]|bool $selectedIds
     * @param int[]|bool $excludedIds
     * @param int $filterExpected
     * @param string $conditionExpected
     */
    private function setUpApplySelection($selectedIds, $excludedIds, $filterExpected, $conditionExpected)
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
            ->willReturn([new \Magento\Framework\DataObject(['id' => 1])]);
        $filterMock = $this->getMock(ApiFilter::class, [], [], '', false);
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with(Filter::SELECTED_PARAM)
            ->willReturn($selectedIds);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with(Filter::EXCLUDED_PARAM)
            ->willReturn($excludedIds);
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
