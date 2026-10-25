<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Test\Unit;

use Exception;
use Magento\Framework\Logger\Monolog;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class MonologTest extends TestCase
{
    public function testAddRecord()
    {
        $logger = new Monolog(__METHOD__);
        $handler = new TestHandler();

        $logger->pushHandler($handler);

        $logger->addRecord(Logger::ERROR, 'test');
        list($record) = $handler->getRecords();

        $this->assertSame('test', $record['message']);
    }
}
