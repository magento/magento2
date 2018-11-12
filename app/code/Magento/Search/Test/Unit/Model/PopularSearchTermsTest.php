<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\PopularSearchTerms;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Store\Model\ScopeInterface;

/**
 * @covers \Magento\Search\Model\PopularSearchTerms
 */
class PopularSearchTermsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var PopularSearchTerms
     */
    private $popularSearchTerms;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryCollectionMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->queryCollectionMock = $this->createMock(Collection::class);
        $this->popularSearchTerms = new PopularSearchTerms($this->scopeConfigMock, $this->queryCollectionMock);
    }

    /**
     * Test isCacheableDataProvider method
     *
     * @dataProvider isCacheableDataProvider
     *
     * @param string $term
     * @param array $terms
     * @param $expected $terms
     *
     * @return void
     */
    public function testIsCacheable($term, $terms, $expected)
    {
        $storeId = 7;
        $pageSize = 25;

        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with(
                PopularSearchTerms::XML_PATH_MAX_COUNT_CACHEABLE_SEARCH_TERMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($pageSize);
        $this->queryCollectionMock->expects($this->once())->method('setPopularQueryFilter')->with($storeId)
            ->willReturnSelf();
        $this->queryCollectionMock->expects($this->once())->method('setPageSize')->with($pageSize)
            ->willReturnSelf();
        $this->queryCollectionMock->expects($this->once())->method('load')->willReturnSelf();
        $this->queryCollectionMock->expects($this->once())->method('getColumnValues')->with('query_text')
            ->willReturn($terms);

        $actual = $this->popularSearchTerms->isCacheable($term, $storeId);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function isCacheableDataProvider()
    {
        return [
            ['test01', [], false],
            ['test02', ['test01', 'test02'], true],
            ['test03', ['test01', 'test02'], false],
            ['test04', ['test04'], true],
        ];
    }
}
