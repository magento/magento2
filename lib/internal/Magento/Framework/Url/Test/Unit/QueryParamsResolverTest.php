<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\QueryParamsResolver;
use PHPUnit\Framework\TestCase;

class QueryParamsResolverTest extends TestCase
{
    /** @var QueryParamsResolver */
    protected $object;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(QueryParamsResolver::class);
    }

    public function testGetQuery()
    {
        $this->object->addQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals('foo=bar&true=false', $this->object->getQuery());
    }

    public function testGetQueryEscaped()
    {
        $this->object->addQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals('foo=bar&amp;true=false', $this->object->getQuery(true));
    }

    public function testSetQuery()
    {
        $this->object->setQuery('foo=bar&true=false');
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }

    public function testSetQueryIdempotent()
    {
        $this->object->setQuery(null);
        $this->assertEquals([], $this->object->getQueryParams());
    }

    public function testSetQueryParam()
    {
        $this->object->setQueryParam('foo', 'bar');
        $this->object->setQueryParam('true', 'false');
        $this->object->setQueryParam('foo', 'bar');
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }

    public function testSetQueryParams()
    {
        $this->object->setQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }

    public function testAddQueryParamsIdempotent()
    {
        $this->object->setData('query_params', ['foo' => 'bar', 'true' => 'false']);
        $this->object->addQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }
}
