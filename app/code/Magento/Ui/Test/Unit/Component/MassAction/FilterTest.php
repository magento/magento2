<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\MassAction;

use Magento\Framework\Data\Collection;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactoryMock;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /** @var Filter */
    private $filter;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderMock;

    /**
     * @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractDbMock;

    /**
     * @var SearchResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultMock;

    /**
     * @var UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentMock;

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
        $contextMock = $this->getMock(ContextInterface::class);
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
            ->willReturn($contextMock);
        $contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->dataProviderMock->expects($this->any())
            ->method('setLimit');
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
     * Run test for getCollection method
     *
     * @param int[]|bool $selectedIds
     * @param int[]|bool $excludedIds
     * @param int $filterExpected
     * @param string $conditionExpected
     * @param string $collectionClass
     * @dataProvider applySelectionOnTargetProviderDataProvider
     */
    public function testGetCollection($selectedIds, $excludedIds, $filterExpected, $conditionExpected, $collectionClass)
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
        $this->searchResultMock = $this->getMockBuilder($collectionClass)
            ->disableOriginalConstructor()
            ->setMethods(['getItems', 'getAllIds'])
            ->getMockForAbstractClass();
        $this->searchResultMock->expects($this->any())
            ->method('getItems')
            ->willReturn([]);
        $this->dataProviderMock->expects($this->any())
            ->method('getSearchResult')
            ->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn([]);
        $this->assertEquals($this->abstractDbMock, $this->filter->getCollection($this->abstractDbMock));
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
            [[1, 2, 3], 'false' , 0, 'in', SearchResultInterface::class],
            [[1, 2, 3], [1, 2, 3] , 1, 'nin', SearchResultInterface::class],
            ['false', [1, 2, 3] , 1, 'nin', SearchResultInterface::class],
            ['false', 'false' , 0, '', SearchResultInterface::class],
            [[1, 2, 3], 'false' , 0, 'in', Collection::class],
            [[1, 2, 3], [1, 2, 3] , 1, 'nin', Collection::class],
            ['false', [1, 2, 3] , 1, 'nin', Collection::class],
            ['false', 'false' , 0, '', Collection::class]
        ];
    }

    /**
     * @throws \Exception
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testApplySelectionOnTargetProviderException()
    {
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
     * This tests the method prepareComponent()
     */
    public function testPrepareComponent()
    {
        $this->filter->prepareComponent($this->uiComponentMock);
    }

    /**
     * This tests the method getComponentRefererUrl()
     */
    public function testGetComponentRefererUrlIsNotNull()
    {
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
