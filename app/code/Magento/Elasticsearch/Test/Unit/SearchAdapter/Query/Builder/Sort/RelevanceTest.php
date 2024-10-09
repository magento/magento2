<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\Relevance;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\TestCase;

class RelevanceTest extends TestCase
{
    /**
     * @var Relevance
     */
    private $relevance;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->relevance = new Relevance();
    }

    /**
     * @return void
     */
    public function testBuild(): void
    {
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $result = $this->relevance->build($attributeMock, 'desc', $requestMock);
        self::assertEquals(['_score' => ['order' => 'desc']], $result);
    }
}
