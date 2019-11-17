<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for SearchResultApplier.
 */
class SearchResultApplierTest extends TestCase
{
    /**
     * @var SearchResultApplier|MockObject
     */
    private $object;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var Select
     */
    private $select;

    /**
     * @var SearchResultInterface|MockObject
     */
    private $searchResult;

    /**
     * @var DocumentInterface|MockObject
     */
    private $document;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $currentPage;

    protected function setUp(): void
    {
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection
            ->method('getSelect')
            ->willReturn($this->select);

        $this->searchResult = $this->getMockBuilder(SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->document = $this->getMockBuilder(DocumentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->document
            ->method('getId')
            ->willReturn(123);

        $this->searchResult
            ->method('getItems')
            ->willReturn([$this->document]);

        $this->size = 10;
        $this->currentPage = 1;

        $this->object = new SearchResultApplier(
            $this->collection,
            $this->searchResult,
            $this->size,
            $this->currentPage
        );
    }

    public function testApply(): void
    {
        $this->collection->expects($this->once())
            ->method('setPageSize')
            ->with(null)
            ->willReturn($this->collection);

        $this->collection->expects($this->once())
            ->method('setCurPage')
            ->with(null)
            ->willReturn($this->collection);

        $this->object->apply();
    }
}
