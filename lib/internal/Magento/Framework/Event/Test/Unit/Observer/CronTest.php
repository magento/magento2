<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Test\Unit\Observer;

use \Magento\Framework\Event\Observer\Cron;

/**
 * Class CronTest
 */
class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cron
     */
    protected $cron;

    protected function setUp()
    {
        $this->cron = new Cron();
    }

    protected function tearDown()
    {
        $this->cron = null;
    }

    /**
     * @dataProvider numericValueProvider
     * @param string|int $value
     * @param int|bool $expectedResult
     */
    public function testGetNumeric($value, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->cron->getNumeric($value));
    }

    public function numericValueProvider()
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

    /**
     * @dataProvider matchCronExpressionProvider
     * @param string $expression
     * @param int $number
     * @param bool $expectedResult
     */
    public function testMatchCronExpression($expression, $number, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->cron->matchCronExpression($expression, $number));
    }

    public function matchCronExpressionProvider()
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

    /**
     * @dataProvider isValidForProvider
     * @param int $time
     * @param string $expression
     * @param bool $expectedResult
     */
    public function testIsValidFor($time, $expression, $expectedResult)
    {
        $eventMock = $this->getMock('Magento\Framework\Event', [], [], '', false);

        $this->cron->setCronExpr($expression);
        $this->cron->setNow($time);

        $this->assertEquals($expectedResult, $this->cron->isValidFor($eventMock));
    }

    public function isValidForProvider()
    {
        return [
            [mktime(0, 0, 12, 7, 1, 2000), '* * * * *', true],
            [mktime(0, 0, 12, 7, 1, 2000), '* * * * * *', false],
            [mktime(12, 0, 0, 7, 1, 2000), '0 12 * * *', true],
            [mktime(11, 0, 0, 7, 1, 2000), '0 12 * * *', false]
        ];
    }
}
