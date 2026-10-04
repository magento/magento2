<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query;

use Magento\Framework\GraphQl\Query\QueryDataFormatter;
use Magento\Framework\GraphQl\Query\QueryResponseFormatterInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class QueryDataFormatterTest extends TestCase
{
    /**
     * @return void
     */
    public function testNotUsingCorrectInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Formatter must implement ' . QueryResponseFormatterInterface::class
        );

        new QueryDataFormatter([new \stdClass()]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testFormatResponse(): void
    {
        $formatterMock = $this->createMock(QueryResponseFormatterInterface::class);
        $formatterMock->expects($this->once())
            ->method('formatResponse')
            ->willReturn(['formatted' => true]);

        $queryDataFormatter = new QueryDataFormatter([$formatterMock]);
        $result = $queryDataFormatter->formatResponse(['data' => 'test']);

        $this->assertEquals(['formatted' => true], $result);
    }
}
