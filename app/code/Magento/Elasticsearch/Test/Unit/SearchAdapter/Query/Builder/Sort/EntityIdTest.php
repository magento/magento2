<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\EntityId;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\TestCase;

class EntityIdTest extends TestCase
{
    /**
     * @var EntityId
     */
    private $entityId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->entityId = new EntityId();
    }

    /**
     * @return void
     */
    public function testBuild(): void
    {
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $result = $this->entityId->build($attributeMock, 'desc', $requestMock);
        self::assertArrayHasKey('_script', $result);
        self::assertArrayHasKey('script', $result['_script']);
        self::assertEquals('desc', $result['_script']['order']);
    }
}
