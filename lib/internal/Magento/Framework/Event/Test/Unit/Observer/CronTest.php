<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer\Cron;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CronTest extends TestCase
{
    /**
     * @var Cron
     */
    protected $cron;

    protected function setUp(): void
    {
        $this->cron = new Cron();
    }

    protected function tearDown(): void
    {
        $this->cron = null;
    }

    /**     * @param string|int $value
     * @param int|bool $expectedResult
     */
    #[DataProvider('numericValueProvider')]
    public function testGetNumeric($value, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->cron->getNumeric($value));
    }

    /**
     * @return array
     */
    public static function numericValueProvider()
    {
        return [
            ['jan', 1],
            ['feb', 2],
            ['mar', 3],
            ['apr', 4],
            ['may', 5],
            ['jun', 6],
            ['jul', 7],
            ['aug', 8],
            ['sep', 9],
            ['oct', 10],
            ['nov', 11],
            ['dec', 12],
            ['sun', 0],
            ['mon', 1],
            ['tue', 2],
            ['wed', 3],
            ['thu', 4],
            ['fri', 5],
            ['sat', 6],
            ['negative', false],
            ['SATupper-case & suffix', 6],
            [154, 154],
            [3.14, 3.14],
            ['12', '12']
        ];
    }

    /**     * @param string $expression
     * @param int $number
     * @param bool $expectedResult
     */
    #[DataProvider('matchCronExpressionProvider')]
    public function testMatchCronExpression($expression, $number, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->cron->matchCronExpression($expression, $number));
    }

    /**
     * @return array
     */
    public static function matchCronExpressionProvider()
    {
        return [
            ['mon-fri', 2, true],
            ['mon-fri', 0, false],
            ['january-june', 3, true],
            ['january-june', 11, false],
            [1, 1, true],
            ['*', 1214, true],
            [13, 11, false],
        ];
    }

    /**     * @param int $time
     * @param string $expression
     * @param bool $expectedResult
     */
    #[DataProvider('isValidForProvider')]
    public function testIsValidFor($time, $expression, $expectedResult)
    {
        $eventMock = $this->createMock(Event::class);

        $this->cron->setCronExpr($expression);
        $this->cron->setNow($time);

        $this->assertEquals($expectedResult, $this->cron->isValidFor($eventMock));
    }

    /**
     * @return array
     */
    public static function isValidForProvider()
    {
        return [
            [mktime(0, 0, 12, 7, 1, 2000), '* * * * *', true],
            [mktime(0, 0, 12, 7, 1, 2000), '* * * * * *', false],
            [mktime(12, 0, 0, 7, 1, 2000), '0 12 * * *', true],
            [mktime(11, 0, 0, 7, 1, 2000), '0 12 * * *', false]
        ];
    }
}
