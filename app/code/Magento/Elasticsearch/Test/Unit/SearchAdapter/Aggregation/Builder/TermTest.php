<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation\Builder;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Term;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\Aggregation\TermBucket;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TermTest extends TestCase
{
    /**
     * @var Term
     */
    private $model;

    /**
     * @var BucketInterface|MockObject
     */
    protected $requestBuckedInterface;

    /**
     * @var DataProviderInterface|MockObject
     */
    protected $dataProviderContainer;

    /**
     * @var TermBucket|MockObject
     */
    protected $bucket;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->requestBuckedInterface = $this->getMockBuilder(BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataProviderContainer = $this->getMockBuilder(
            DataProviderInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = $this->getMockBuilder(TermBucket::class)
            ->onlyMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $objectManagerHelper->getObject(
            Term::class,
            []
        );
    }

    /**
     * Test build() method
     */
    public function testBuild()
    {
        $dimensions = [
            'scope' => [
                'name' => 'scope',
                'value' => 1,
            ],
        ];

        $queryResult = [
            'took' => 1,
            'timed_out' => false,
            '_shards' => [],
            'hits' => [
                'total' => 1,
                'max_score' => 1,
                'hits' => [],
            ],
            'aggregations' => [
                'category_bucket' => [
                    'buckets' => [
                        [
                            'key' => '23',
                            'doc_count' => 12,
                        ],
                    ],
                ],
            ],
        ];

        $this->bucket->expects($this->once())
            ->method('getName')
            ->willReturn('category_bucket');

        $this->model->build(
            $this->bucket,
            $dimensions,
            $queryResult,
            $this->dataProviderContainer
        );
    }
}
