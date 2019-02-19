<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Http\Payload\Filter;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\Filter\RemoveFieldsFilter;
use PHPUnit\Framework\TestCase;

class RemoveFieldsFilterTest extends TestCase
{
    public function testFilterRemovesFields()
    {
        $filter = new RemoveFieldsFilter(['foo', 'bar']);

        $actual = $filter->filter([
            'some' => 123,
            'data' => 321,
            'foo' => 'to',
            'filter' => ['blah'],
            'bar' => 'fields from'
        ]);

        $expected = [
            'some' => 123,
            'data' => 321,
            'filter' => ['blah'],
        ];

        $this->assertEquals($expected, $actual);
    }
}
