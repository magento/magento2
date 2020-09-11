<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Response;

use Magento\Framework\Api\Search\Document;
use Magento\Framework\Search\Response\Aggregation;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryResponseTest extends TestCase
{
    /**
     * @var Document[]
     */
    private $documents = [];

    /**
     * @var Aggregation
     */
    private $aggregations = [];

    /**
     * @var QueryResponse|MockObject
     */
    private $queryResponse;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        for ($count = 0; $count < 5; $count++) {
            $document = $this->getMockBuilder(Document::class)
                ->disableOriginalConstructor()
                ->getMock();

            $document->expects($this->any())->method('getId')->willReturn($count);
            $this->documents[] = $document;
        }

        $this->aggregations = $this->getMockBuilder(Aggregation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryResponse = $helper->getObject(
            QueryResponse::class,
            [
                'documents' => $this->documents,
                'aggregations' => $this->aggregations,
                'total' => 1
            ]
        );
    }

    public function testGetIterator()
    {
        $count = 0;
        foreach ($this->queryResponse as $document) {
            $this->assertEquals($document->getId(), $count);
            $count++;
        }
    }

    public function testCount()
    {
        $this->assertCount(5, $this->queryResponse);
    }

    public function testGetAggregations()
    {
        $aggregations = $this->queryResponse->getAggregations();
        $this->assertInstanceOf(Aggregation::class, $aggregations);
    }
}
