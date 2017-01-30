<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Response;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class QueryResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Document[]
     */
    private $documents = [];

    /**
     * @var \Magento\Framework\Search\Response\Aggregation
     */
    private $aggregations = [];

    /**
     * @var \Magento\Framework\Search\Response\QueryResponse | \PHPUnit_Framework_MockObject_MockObject
     */
    private $queryResponse;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        for ($count = 0; $count < 5; $count++) {
            $document = $this->getMockBuilder('Magento\Framework\Search\Document')
                ->disableOriginalConstructor()
                ->getMock();

            $document->expects($this->any())->method('getId')->will($this->returnValue($count));
            $this->documents[] = $document;
        }

        $this->aggregations = $this->getMockBuilder('Magento\Framework\Search\Response\Aggregation')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryResponse = $helper->getObject(
            'Magento\Framework\Search\Response\QueryResponse',
            [
                'documents' => $this->documents,
                'aggregations' => $this->aggregations,
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
        $this->assertEquals(count($this->queryResponse), 5);
    }

    public function testGetAggregations()
    {
        $aggregations = $this->queryResponse->getAggregations();
        $this->assertInstanceOf('Magento\Framework\Search\Response\Aggregation', $aggregations);
    }
}
