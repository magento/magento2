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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Search\Model\PopularSearchTerms
 */
class PopularSearchTermsTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var PopularSearchTerms
     */
    private $popularSearchTerms;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Collection|MockObject
     */
    private $queryCollectionMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->queryCollectionMock = $this->createMock(Collection::class);
        $this->popularSearchTerms = new PopularSearchTerms($this->scopeConfigMock, $this->queryCollectionMock);
    }

    /**
     * Test isCacheableDataProvider method
     *
     * @return void
     */
    public function testIsCacheable()
    {
        $term = 'test1';
        $storeId = 1;
        $pageSize = 35;

        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->with(
                PopularSearchTerms::XML_PATH_MAX_COUNT_CACHEABLE_SEARCH_TERMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($pageSize);
        $this->queryCollectionMock->expects($this->exactly(2))
            ->method('isTopSearchResult')
            ->with($term, $storeId, $pageSize)
            ->willReturn(true, false);

        $this->assertTrue($this->popularSearchTerms->isCacheable($term, $storeId));
        $this->assertFalse($this->popularSearchTerms->isCacheable($term, $storeId));
    }
}
