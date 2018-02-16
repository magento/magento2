<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Validator as ExpressionValidator;
use Magento\Cron\Model\Schedule;

/**
 * Class \Magento\Cron\Test\Unit\Model\ScheduleTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ScheduleTest extends AbstractSchedule
{
    protected $resourceJobMock;

    protected function setUp()
    {
        parent::setUp();

        $this->resourceJobMock = $this->getMockBuilder(\Magento\Cron\Model\ResourceModel\Schedule::class)
            ->disableOriginalConstructor()->setMethods([
                'trySetJobUniqueStatusAtomic',
                '__wakeup',
                'getIdFieldName'
            ])->getMockForAbstractClass();

        $this->resourceJobMock->expects($this->any())->method('getIdFieldName')->will($this->returnValue('id'));
    }

    /**
     * @param string $cronExpr
     * @return Schedule
     */
    protected function getScheduleModel($cronExpr = '')
    {
        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->getHelper()->getObject(\Magento\Cron\Model\Schedule::class, [
                'expressionFactory' => $this->getExpressionFactoryObject($cronExpr),
                'partFactory' => $this->getExpressionPartFactoryObject($cronExpr),
            ]);
        return $model;
    }

    /**
     * @param string $cronExpression
     * @dataProvider validCronExprDataProvider
     */
    public function testSetCronExpr($cronExpression)
    {
        // 1. Create mocks
        /** @var ExpressionValidator $expressionValidator */
        $model = $this->getScheduleModel($cronExpression);
        // 2. Run tested method
        $model->setCronExpr($cronExpression);
    }

    /**
     * @param string $cronExpression
     * @expectedException \Magento\Framework\Exception\CronException
     * @dataProvider invalidCronExprDataProvider
     */
    public function testSetCronExprException($cronExpression)
    {
        // 1. Create mocks
        /** @var ExpressionValidator $expressionValidator */
        $model = $this->getScheduleModel($cronExpression);
        // 2. Run tested method
        $model->setCronExpr($cronExpression);
    }

    /**
     * @param int $scheduledAt
     * @param string $cronExpr
     * @param $expected
     * @dataProvider tryScheduleDataProvider
     */
    public function testTrySchedule($scheduledAt, $cronExpr, $expected)
    {
        // 1. Create mocks
        /** @var ExpressionValidator $expressionValidator */
        $model = $this->getScheduleModel($cronExpr);

        // 2. Set fixtures
        $model->setScheduledAt($scheduledAt);

        // 3. Run tested method
        $result = $model->trySchedule();

        // 4. Compare actual result with expected result
        $this->assertEquals($expected, $result);
    }

    public function testTryScheduleWithConversionToAdminStoreTime()
    {
        $cronExpr = '* * * * *';

        // 1. Create mocks
        $timezoneConverter = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $timezoneConverter->expects($this->once())
            ->method('date')
            ->with($this->getScheduledtAt())
            ->willReturn(new \DateTime($this->getScheduledtAt()));

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->getHelper()->getObject(\Magento\Cron\Model\Schedule::class, [
                'timezoneConverter' => $timezoneConverter,
                'expressionFactory' => $this->getExpressionFactoryObject($cronExpr),
                'partFactory' => $this->getExpressionPartFactoryObject($cronExpr),
            ]);

        // 2. Set fixtures
        $model->setScheduledAt($this->getScheduledtAt());

        // 3. Run tested method
        $result = $model->trySchedule();

        // 4. Compare actual result with expected result
        $this->assertTrue($result);
    }

    /**
     * @return array
     */
    public function tryScheduleDataProvider()
    {
        $date = '2011-12-13 14:15:16';
        return [
            [strtotime($date), '', false],
            [strtotime($date), '* * * * *', true],
            [strtotime($date), '* * * * *', true],
            [strtotime($date), '15 * * * *', true],
            [strtotime($date), '* 14 * * *', true],
            [strtotime($date), '* * 13 * *', true],
            [strtotime($date), '* * * 12 *', true],
            [strtotime($date), '*/15 * * * *', true],
            [strtotime($date), '*/4 * * * *', false],
            [strtotime($date), '15/15 * * * *', true],
            [strtotime($date), '30/15 * * * *', false],
            [strtotime($date), '* 30,*/7 * * *', false],
            [strtotime($date), '* * 15,*/13 * *', true],
            [strtotime($date), '* * * */6 *', true],
            [strtotime('Monday'), '* * * * 1', true],
        ];
    }

    /**
     * @param string $cronExpressionPart
     * @param int $dateTimePart
     * @param bool $expectedResult
     * @dataProvider matchCronExpressionDataProvider
     */
    public function testMatchCronExpression($cronExpressionPart, $dateTimePart, $expectedResult)
    {
        // 1. Create mocks
        /** @var ExpressionValidator $expressionValidator */
        $model = $this->getScheduleModel($cronExpressionPart);
        // 2. Run tested method
        $result = $model->matchCronExpression($cronExpressionPart, $dateTimePart);

        // 3. Compare actual result with expected result
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function matchCronExpressionDataProvider()
    {
        return [
            ['*', 0, true],
            ['*', 1, true],
            ['*', 59, true],

            ['0,1,20', 0, true],
            ['0,1,20', 1, true],
            ['0,1,20', 20, true],
            ['0,1,22', 2, false],
            ['0,1,*', 2, true],

            ['0-20', 0, true],
            ['0-20', 1, true],
            ['0-20', 20, true],
            ['0-20', 21, false],

            ['*/2', 0, true],
            ['*/2', 2, true],
            ['*/2', 4, true],
            ['*/2', 3, false],
            ['*/20', 40, true],

            ['0-20/5', 0, true],
            ['0-20/5', 5, true],
            ['0-20/5', 10, true],
            ['0-20/5', 21, false],
            ['0-20/5', 25, false],

            ['1/5', 5, false],
            ['5/5', 5, true],
            ['10/5', 5, false],
            ['10/5', 10, true],
        ];
    }

    /**
     * @param string $cronExpressionPart
     * @expectedException \Magento\Framework\Exception\CronException
     * @dataProvider matchCronExpressionExceptionDataProvider
     */
    public function testMatchCronExpressionException($cronExpressionPart)
    {
        $dateTimePart = 10;

        // 1 Create mocks
        $model = $this->getScheduleModel($cronExpressionPart);

        // 2. Run tested method
        $model->matchCronExpression($cronExpressionPart, $dateTimePart);
    }

    /**
     * @return array
     */
    public function matchCronExpressionExceptionDataProvider()
    {
        return [
            ['1/2/3'],    //Invalid cron expression, expecting 'match/modulus': 1/2/3
            ['1/'],       //Invalid cron expression, expecting numeric modulus: 1/
            ['-'],        //Invalid cron expression
            ['1-2-3'],    //Invalid cron expression, expecting 'from-to' structure: 1-2-3
            ['2-1'],      //Invalid cron expression, expecting from <= to in 'from-to' structure: 2-1
        ];
    }

    public function testTryLockJobSuccess()
    {
        $scheduleId = 1;

        $this->resourceJobMock->expects($this->once())
            ->method('trySetJobUniqueStatusAtomic')
            ->with($scheduleId, Schedule::STATUS_RUNNING, Schedule::STATUS_PENDING)
            ->will($this->returnValue(true));

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->getHelper()->getObject(\Magento\Cron\Model\Schedule::class, [
                'resource' => $this->resourceJobMock
            ]);
        $model->setId($scheduleId);
        $this->assertEquals(0, $model->getStatus());

        $model->tryLockJob();

        $this->assertEquals(Schedule::STATUS_RUNNING, $model->getStatus());
    }

    public function testTryLockJobFailure()
    {
        $scheduleId = 1;

        $this->resourceJobMock->expects($this->once())
            ->method('trySetJobUniqueStatusAtomic')
            ->with($scheduleId, Schedule::STATUS_RUNNING, Schedule::STATUS_PENDING)
            ->will($this->returnValue(false));

        /** @var \Magento\Cron\Model\Schedule $model */
        $model = $this->getHelper()->getObject(\Magento\Cron\Model\Schedule::class, [
                'resource' => $this->resourceJobMock
            ]);
        $model->setId($scheduleId);
        $this->assertEquals(0, $model->getStatus());

        $model->tryLockJob();

        $this->assertEquals(0, $model->getStatus());
    }
}
