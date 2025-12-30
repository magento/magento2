<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\Widget\Html;

use Magento\Catalog\Block\Product\Widget\Html\Pager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Unit test coverage file
 *
 * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager
 */
class PagerTest extends TestCase
{
    /**
     * @var Pager
     */
    private Pager $pager;
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;
    /**
     * @var AbstractCollection|MockObject
     */
    private $collectionMock;
    /**
     * @var Url|MockObject
     */
    private $urlBuilderMock;

    /**
     * Set up test dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->urlBuilderMock = $this->createMock(Url::class);
        $selectMock = $this->createMock(Select::class);
        $this->collectionMock = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect', 'getSize'])
            ->getMock();

        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $this->collectionMock->method('getSelect')->willReturn($selectMock);

        $this->pager = $objectManager->getObject(
            Pager::class,
            [
                'context' => $contextMock,
            ]
        );
    }

    /**
     * Unit test for getCollectionSize()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getCollectionSize()
     * @dataProvider collectionSizeDataProvider
     * @param int $collectionSize
     * @param int $totalLimit
     * @param int $expectedResult
     * @return void
     */
    public function testGetCollectionSize(
        int $collectionSize,
        int $totalLimit,
        int $expectedResult
    ): void {
        $this->collectionSizeHelper($totalLimit, $collectionSize);
        $this->assertSame($expectedResult, $this->pager->getCollectionSize());
    }

    /**
     * Unit test for getTotalNum()
     *
     * Reuses collectionSizeDataProvider to cover scenarios where total limit affects total number.
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getTotalNum()
     * @dataProvider collectionSizeDataProvider
     * @param int $collectionSize
     * @param int $totalLimit
     * @param int $expectedResult
     * @return void
     */
    public function testGetTotalNum(int $collectionSize, int $totalLimit, int $expectedResult): void
    {
        $this->collectionSizeHelper($totalLimit, $collectionSize);
        $this->assertSame($expectedResult, $this->pager->getTotalNum());
    }

    /**
     * Helper method to set up collection size mock and pager total limit.
     *
     * @param int $totalLimit
     * @param int $collectionSize
     * @return void
     */
    private function collectionSizeHelper(int $totalLimit, int $collectionSize): void
    {
        $this->collectionMock->expects($this->once())->method('getSize')->willReturn($collectionSize);

        $this->pager->setTotalLimit($totalLimit);
        $this->pager->setCollection($this->collectionMock);
    }

    /**
     * Data provider for testGetCollectionSize()
     *
     * @return array
     */
    public static function collectionSizeDataProvider(): array
    {
        return [
            'no_total_limit_cache' => [5, 0, 5],
            'total_limit_smaller' => [10, 6, 6],
            'total_limit_larger' => [4, 6, 4],
        ];
    }

    /**
     * Unit test for getLastNum()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getLastNum()
     * @return void
     */
    public function testGetLastNum(): void
    {
        $selectMock = $this->createMock(Select::class);
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect', 'setPageSize', 'setCurPage', 'count', 'getSize'])
            ->getMock();

        $collection->method('setPageSize')->willReturnSelf();
        $collection->method('setCurPage')->willReturnSelf();
        $collection->method('getSelect')->willReturn($selectMock);
        $collection->method('count')->willReturn(5);
        $collection->method('getSize')->willReturn(20);
        $this->requestMock->method('getParam')->willReturn(2);

        $this->pager->setLimit(10);
        $this->pager->setCollection($collection);

        $this->assertSame(15, $this->pager->getLastNum());
    }

    /**
     * Unit test for getLastPageNum()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getLastPageNum()
     * @dataProvider lastPageNumDataProvider
     * @param int $collectionSize
     * @param int $limit
     * @param int $expectedLastPage
     * @return void
     */
    public function testGetLastPageNum(int $collectionSize, int $limit, int $expectedLastPage): void
    {
        $this->collectionMock->method('getSize')->willReturn($collectionSize);
        $this->pager->setLimit($limit);
        $this->pager->setCollection($this->collectionMock);

        $this->assertEquals($expectedLastPage, $this->pager->getLastPageNum());
    }

    /**
     * Data provider for testGetLastPageNum()
     *
     * @return array
     */
    public static function lastPageNumDataProvider(): array
    {
        return [
            'zero_collection_returns_one' => [0, 10, 1],
            'exact_pages' => [20, 10, 2],
            'partial_last_page' => [25, 10, 3],
            'single_page' => [5, 10, 1],
        ];
    }

    /**
     * Unit test for isFirstPage()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::isFirstPage()
     * @return void
     */
    public function testIsFirstPage(): void
    {
        $this->pager->setCollection($this->collectionMock);
        $this->assertTrue($this->pager->isFirstPage());
    }

    /**
     * Unit test for isFirstPage() when current page is not the first
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::isFirstPage()
     * @return void
     */
    public function testIsFirstPageReturnsFalseWhenCurrentPageIsTwo(): void
    {
        $this->setProtectedProperty('_currentPage', 2);
        $this->assertFalse($this->pager->isFirstPage());
    }

    /**
     * Unit test for isLastPage()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::isLastPage()
     * @dataProvider pageSizeDataProvider
     * @param int $pageSize
     * @param bool $expectedResult
     * @return void
     */
    public function testIsLastPage(int $pageSize, bool $expectedResult): void
    {
        $this->collectionMock->method('getSize')->willReturn($pageSize);
        $this->pager->setCollection($this->collectionMock);
        $this->assertSame($expectedResult, $this->pager->isLastPage());
    }

    /**
     * Data provider for testIsLastPage()
     *
     * @return array
     */
    public static function pageSizeDataProvider(): array
    {
        return [
            'empty_collection_is_last_page' => [0, true],
            'small_collection_is_last_page' => [10, true],
            'large_collection_not_last_page' => [20, false]
        ];
    }

    /**
     * Unit test for getPages()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getPages()
     * @return void
     */
    public function testGetPagesReturnsSinglePageForSmallCollection(): void
    {
        $this->pager->setCollection($this->collectionMock);
        $result = $this->pager->getPages();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Unit test for getPages()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getPages()
     * @return void
     */
    public function testGetPagesReturnsMultiplePagesForLargeCollection(): void
    {
        $this->collectionMock->method('getSize')->willReturn(60);

        $this->pager->setCollection($this->collectionMock);
        $result = $this->pager->getPages();

        $this->assertIsArray($result);
        $this->assertCount(5, $result);
    }

    /**
     * Verify _initFrame method with different current page and last page number.
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::_initFrame()
     * @dataProvider initFramesDataProvider
     * @param int $curPage
     * @param int $lastPageNo
     * @param int $frameStart
     * @param int $frameEnd
     * @return void
     */
    public function testInitFrame(int $curPage, int $lastPageNo, int $frameStart, int $frameEnd): void
    {
        $pager = $this->getMockBuilder(Pager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLastPageNum', 'getCurrentPage'])
            ->getMock();

        $pager->expects($this->any())->method('getCurrentPage')->willReturn($curPage);
        $pager->expects($this->any())->method('getLastPageNum')->willReturn($lastPageNo);

        $reflection = new ReflectionClass($pager);
        $initFrameMethod = $reflection->getMethod('_initFrame');
        $initFrameMethod->setAccessible(true);
        $initFrameMethod->invoke($pager);

        $this->assertEquals($frameStart, $pager->getFrameStart());
        $this->assertEquals($frameEnd, $pager->getFrameEnd());
        $this->assertTrue($pager->isFrameInitialized());
    }

    /**
     * Data provider for testInitFrame()
     *
     * @return array
     */
    public static function initFramesDataProvider(): array
    {
        return [
            'current-page-empty' => [0, 5, 1, 5],
            'current-page-in-middle' => [5, 10, 3, 7],
            'current-page-near-start' => [2, 10, 1, 5],
            'current-page-near-end' => [9, 10, 6, 10],
        ];
    }

    /**
     * Unit test for getCurrentPage() using data provider
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getCurrentPage()
     * @dataProvider getCurrentPageDataProvider
     * @param array $reqParams
     * @param int $collectionSize
     * @param int $limit
     * @param int $expectedFirst
     * @param int|null $expectedSecond
     * @return void
     */
    public function testGetCurrentPageWithDataProvider(
        int  $reqParams,
        int  $collectionSize,
        int  $limit,
        int  $expectedFirst,
        ?int $expectedSecond
    ): void {
        $this->requestMock->method('getParam')->willReturn($reqParams);
        $this->collectionMock->method('getSize')->willReturn($collectionSize);
        $this->pager->setLimit($limit);
        $this->pager->setCollection($this->collectionMock);

        $firstResult = $this->pager->getCurrentPage();
        $this->assertEquals($expectedFirst, $firstResult);

        if ($expectedSecond !== null) {
            $secondResult = $this->pager->getCurrentPage();
            $this->assertSame($expectedSecond, $secondResult);
        }
    }

    /**
     * Data provider for testGetCurrentPageWithDataProvider()
     *
     * @return array
     */
    public static function getCurrentPageDataProvider(): array
    {
        return [
            'param_invalid_defaults' => [0, 20, 10, 1, null],
            'param_valid_within_range' => [2, 100, 10, 2, null],
            'param_greater_clamped_to_last' => [5, 20, 10, 2, null]
        ];
    }

    /**
     * Unit test for getLimit() when limit is already set
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getLimit()
     * @return void
     */
    public function testGetLimitReturnsSetLimitWhenLimitIsAlreadySet(): void
    {
        // When limit is explicitly set on the pager, getLimit() should return it
        $this->pager->setLimit(25);
        $this->assertSame(25, $this->pager->getLimit());
    }

    /**
     * Unit test for getLimit() when request param corresponds to a valid available limit
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getLimit()
     * @return void
     */
    public function testGetLimitReturnsRequestParamIfInAvailableLimits(): void
    {
        // When request param corresponds to a valid available limit, it should be returned
        $available = $this->pager->getAvailableLimit();
        $this->assertNotEmpty($available, 'Available limits should not be empty for this test');

        $keys = array_keys($available);
        $chosenKey = $keys[count($keys) - 1];

        $this->requestMock->method('getParam')->willReturn($chosenKey);

        $this->assertSame($chosenKey, $this->pager->getLimit());
    }

    /**
     * Unit test for getLimit() when request param is invalid
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getLimit()
     * @return void
     */
    public function testGetLimitReturnsDefaultWhenRequestParamInvalid(): void
    {
        // When request param is invalid or missing, getLimit() should return the first available limit
        $available = $this->pager->getAvailableLimit();
        $this->assertNotEmpty($available, 'Available limits should not be empty for this test');

        $this->requestMock->method('getParam')->willReturn('non-existing-key');

        $expectedDefault = current(array_keys($available));
        $this->assertSame($expectedDefault, $this->pager->getLimit());
    }

    /**
     * Unit test for getFirstNum() using data provider
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getFirstNum()
     * @dataProvider getFirstNumDataProvider
     * @param int $limit
     * @param int $collectionSize
     * @param int $requestPage
     * @param int $expectedFirstNum
     * @return void
     */
    public function testGetFirstNumWithDataProvider(
        int $limit,
        int $collectionSize,
        int $requestPage,
        int $expectedFirstNum
    ): void {
        $this->requestMock->method('getParam')->willReturn($requestPage);
        $this->collectionMock->method('getSize')->willReturn($collectionSize);

        $this->pager->setLimit($limit);
        $this->pager->setCollection($this->collectionMock);

        $this->assertEquals($expectedFirstNum, $this->pager->getFirstNum());
    }

    /**
     * Data provider for testGetFirstNumWithDataProvider()
     *
     * Each case: [limit, collectionSize, requestPage, expectedFirstNum]
     *
     * @return array
     */
    public static function getFirstNumDataProvider(): array
    {
        return [
            // Normal case: limit 10, page 3 => first = 10*(3-1)+1 = 21
            'normal_middle_page' => [10, 100, 3, 21],
            // Requested page greater than last page:limit 10,collectionSize 15 -> lastPage=2, first = 10*(2-1)+1 = 11
            'clamp_to_last_page' => [10, 15, 5, 11],
            // Zero collection size: should treat last page as 1 -> first = 1
            'zero_collection' => [10, 0, 2, 1],
            // limit 1: page 5 => first = 1*(5-1)+1 = 5
            'limit_one' => [1, 10, 5, 5],
        ];
    }

    /**
     * Unit test for getPreviousPageUrl() with data provider
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getPreviousPageUrl()
     * @dataProvider previousPageUrlDataProvider
     * @param int $currentPage
     * @param int|null $expectedQueryValue
     * @return void
     */
    public function testGetPreviousPageUrlWithDataProvider(int $currentPage, ?int $expectedQueryValue): void
    {
        $expectedUrl = 'http://example.com/prev';

        $this->setProtectedProperty('_currentPage', $currentPage);
        $this->mockUrlBuilder($expectedQueryValue, $expectedUrl);

        $this->assertSame($expectedUrl, $this->pager->getPreviousPageUrl());
    }

    /**
     * Data provider for testGetPreviousPageUrlWithDataProvider
     *
     * @return array
     */
    public static function previousPageUrlDataProvider(): array
    {
        return [
            'on_first_page_zero_prev' => [1, null],
            'on_second_page_prev_null' => [2, null],
            'on_third_page_prev_two' => [3, 2],
            'on_fifth_page_prev_four' => [5, 4],
        ];
    }

    /**
     * Data-driven test for getNextPageUrl()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getNextPageUrl()
     * @dataProvider nextPageUrlDataProvider
     * @param int $currentPage
     * @param int $expectedQueryValue
     * @return void
     */
    public function testGetNextPageUrlWithDataProvider(int $currentPage, int $expectedQueryValue): void
    {
        $expectedUrl = 'http://example.com/next';

        $this->setProtectedProperty('_currentPage', $currentPage);
        $this->mockUrlBuilder($expectedQueryValue, $expectedUrl);

        $this->assertSame($expectedUrl, $this->pager->getNextPageUrl());
    }

    /**
     * Data provider for testGetNextPageUrlWithDataProvider
     *
     * @return array
     */
    public static function nextPageUrlDataProvider(): array
    {
        return [
            'current_1_next_2' => [1, 2],
            'current_2_next_3' => [2, 3],
            'current_3_next_4' => [3, 4],
            'current_5_next_6' => [5, 6],
        ];
    }

    /**
     * Data-driven test for getLastPageUrl()
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::getLastPageUrl()
     * @dataProvider lastPageUrlDataProvider
     * @param int $lastPage
     * @param int|null $expectedQueryValue
     * @return void
     */
    public function testGetLastPageUrlWithDataProvider(int $lastPage, ?int $expectedQueryValue): void
    {
        $expectedUrl = 'http://example.com/last';

        $this->setProtectedProperty('_lastPage', $lastPage);
        $this->mockUrlBuilder($expectedQueryValue, $expectedUrl);

        $this->assertSame($expectedUrl, $this->pager->getLastPageUrl());
    }

    /**
     * Data provider for testGetLastPageUrlWithDataProvider
     *
     * @return array
     */
    public static function lastPageUrlDataProvider(): array
    {
        return [
            'last_page_one' => [1, null],
            'last_page_two' => [2, 2],
            'last_page_five' => [5, 5],
        ];
    }

    /**
     * Unit test for setCollection() verifies limit() is called with correct offset and limit
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::setCollection()
     * @dataProvider setCollectionLimitDataProvider
     * @param int $limit
     * @param int $collectionSize
     * @param int $requestPage
     * @param int $expectedOffset
     * @param int $expectedLimit
     * @return void
     */
    public function testSetCollectionAppliesCorrectLimitAndOffset(
        int $limit,
        int $collectionSize,
        int $requestPage,
        int $expectedOffset,
        int $expectedLimit
    ): void {
        $selectMock = $this->createMock(Select::class);
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect', 'setPageSize', 'setCurPage', 'getSize'])
            ->getMock();

        $collection->method('setPageSize')->willReturnSelf();
        $collection->method('setCurPage')->willReturnSelf();
        $collection->method('getSelect')->willReturn($selectMock);
        $collection->method('getSize')->willReturn($collectionSize);
        $this->requestMock->method('getParam')->willReturn($requestPage);

        $selectMock->expects($this->once())
            ->method('limit')
            ->with($expectedLimit, $expectedOffset);

        $this->pager->setLimit($limit);

        $result = $this->pager->setCollection($collection);
        $this->assertSame($this->pager, $result);
    }

    /**
     * Data provider for testSetCollectionAppliesCorrectLimitAndOffset()
     *
     * @return array
     */
    public static function setCollectionLimitDataProvider(): array
    {
        return [
            'first_page_normal' => [10, 100, 1, 0, 10],
            'second_page_normal' => [10, 100, 2, 10, 10],
            'last_page_partial_items' => [10, 25, 3, 20, 5],
            'edge_case_offset_plus_limit_exceeds_total' => [10, 15, 2, 10, 5],
            'requested_page_exceeds_last_page' => [10, 25, 10, 20, 5],
        ];
    }

    /**
     * Unit test for setCollection() when totalLimit restricts collection size
     *
     * @covers \Magento\Catalog\Block\Product\Widget\Html\Pager::setCollection()
     * @return void
     */
    public function testSetCollectionWithTotalLimit(): void
    {
        $selectMock = $this->createMock(Select::class);
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect', 'setPageSize', 'setCurPage', 'getSize'])
            ->getMock();

        $collection->method('setPageSize')->willReturnSelf();
        $collection->method('setCurPage')->willReturnSelf();
        $collection->method('getSelect')->willReturn($selectMock);
        $collection->method('getSize')->willReturn(100);

        $this->requestMock->method('getParam')->willReturn(3);

        $selectMock->expects($this->once())
            ->method('limit')
            ->with(5, 20);

        $this->pager->setTotalLimit(25);
        $this->pager->setLimit(10);

        $result = $this->pager->setCollection($collection);
        $this->assertSame($this->pager, $result);
    }

    /**
     * Helper method to set protected property on pager instance
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    private function setProtectedProperty(string $property, mixed $value): void
    {
        $reflection = new ReflectionProperty($this->pager, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($this->pager, $value);
    }

    /**
     * Helper method to mock url builder behavior for pagination urls
     *
     * @param int|null $expectedQueryValue
     * @param string $expectedReturnUrl
     * @return void
     */
    private function mockUrlBuilder(?int $expectedQueryValue, string $expectedReturnUrl): void
    {
        $pageVar = $this->pager->getPageVarName();

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->anything(),
                $this->callback(function ($args) use ($pageVar, $expectedQueryValue) {
                    if (!isset($args['_query']) || !array_key_exists($pageVar, $args['_query'])) {
                        return false;
                    }
                    return $args['_query'][$pageVar] === $expectedQueryValue;
                })
            )
            ->willReturn($expectedReturnUrl);
    }
}
